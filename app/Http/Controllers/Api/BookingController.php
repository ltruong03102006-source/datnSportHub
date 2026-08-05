<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingLog;
use App\Models\Court;
use App\Models\Setting;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BookingController extends Controller
{
    public function store(BookingRequest $request)
    {
        $court = Court::with('venue')->find($request->court_id);

        if (! $court || $court->venue?->status !== 'active' || $court->status !== 'active' || ! $court->is_bookable_online) {
            return response()->json(['message' => 'Cơ sở hoặc Sân hiện không ở trạng thái hoạt động (chưa ký hợp đồng với Admin)'], 403);
        }

        $slots = collect($request->slots ?? [[
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]])->map(function ($slot) {
            return [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ];
        })->sortBy('start_time')->values();

        $dayOfWeek = Carbon::parse($request->slot_date)->dayOfWeek;
        $now = Carbon::now();
        $holdTimeMinutes = max(1, (int) Setting::get('booking_hold_time', 15));
        $holdCutoff = $now->copy()->subMinutes($holdTimeMinutes);

        try {
            foreach ($slots as $index => $slot) {
                if ($index > 0) {
                    $previous = $slots[$index - 1];

                    if ($previous['end_time'] > $slot['start_time']) {
                        throw new HttpException(409, 'Các ca không được xếp chồng lên nhau');
                    }
                }
            }

            $booking = DB::transaction(function () use ($request, $slots, $dayOfWeek, $now, $court, $holdCutoff) {
                $items = collect();

                // 1. TÍNH TỔNG SỐ GIỜ KHÁCH ĐẶT ĐỂ TÍNH TIỀN CHO THUÊ
                $totalMinutes = 0;
                foreach ($slots as $slot) {
                    $conflict = BookingItem::whereDate('slot_date', $request->slot_date)
                        ->where(function ($q) use ($slot) {
                            $q->where('start_time', '<', $slot['end_time'])
                                ->where('end_time', '>', $slot['start_time']);
                        })
                        ->whereIn('status', ['booked', 'reschedule_pending'])
                        ->whereHas('booking', function ($query) use ($request, $holdCutoff) {
                            $query->where('court_id', $request->court_id)
                                ->where(function ($statusQuery) use ($holdCutoff) {
                                    $statusQuery->whereIn('status', ['confirmed', 'completed'])
                                        ->orWhere(function ($pendingQuery) use ($holdCutoff) {
                                            $pendingQuery->where('status', 'pending')
                                                ->where('created_at', '>=', $holdCutoff);
                                        });
                                });
                        })
                        ->lockForUpdate()
                        ->exists();

                    $legacyConflict = Booking::where('court_id', $request->court_id)
                        ->whereDate('slot_date', $request->slot_date)
                        ->where(function ($statusQuery) use ($holdCutoff) {
                            $statusQuery->whereIn('status', ['confirmed', 'completed'])
                                ->orWhere(function ($pendingQuery) use ($holdCutoff) {
                                    $pendingQuery->where('status', 'pending')
                                        ->where('created_at', '>=', $holdCutoff);
                                });
                        })
                        ->whereDoesntHave('items')
                        ->where(function ($q) use ($slot) {
                            $q->where('start_time', '<', $slot['end_time'])
                                ->where('end_time', '>', $slot['start_time']);
                        })
                        ->lockForUpdate()
                        ->exists();

                    if ($conflict || $legacyConflict) {
                        throw new HttpException(409, 'This time slot has already been booked');
                    }
                }

                $isFirstBooking = true; // Cờ đánh dấu

                foreach ($slots as $slot) {
                    // ... (Đoạn check conflict giữ nguyên) ...

                    $timeSlot = TimeSlot::where('court_id', $request->court_id)
                        ->where('start_time', $slot['start_time'])
                        ->where('end_time', $slot['end_time'])
                        ->first();

                    $price = DB::table('slot_prices')
                        ->join('time_slots', 'slot_prices.time_slot_id', '=', 'time_slots.id')
                        ->where('time_slots.court_id', $request->court_id)
                        ->where('time_slots.start_time', $slot['start_time'])
                        ->where('time_slots.end_time', $slot['end_time'])
                        ->where(function ($q) use ($dayOfWeek) {
                            $q->where('slot_prices.day_of_week', $dayOfWeek)
                                ->orWhereNull('slot_prices.day_of_week');
                        })
                        ->orderByRaw('day_of_week IS NULL ASC')
                        ->value('price') ?? 0;

                    $items->push([
                        'time_slot_id' => $timeSlot?->id,
                        'slot_date' => $request->slot_date,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'price' => $price,
                        'status' => 'booked',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $originalPrice = $items->sum('price');
                $discount = 0.0;
                $voucher = null;
                if ($request->filled('voucher_code')) {
                    $voucher = \App\Models\Voucher::where('code', $request->voucher_code)->first();
                    if ($voucher) {
                        $voucherService = app(\App\Services\VoucherService::class);
                        $bookingSlots = $slots->toArray();
                        $eligibility = $voucherService->checkEligibility($voucher, $request->court_id, $request->slot_date, $bookingSlots, $originalPrice, Auth::id());
                        if (!$eligibility['eligible']) {
                            throw new HttpException(422, $eligibility['reason']);
                        }
                        $discount = $eligibility['discount'];
                    }
                }

                // TÍNH TỔNG TIỀN DỊCH VỤ ĐI KÈM (NẾU CÓ)
                $servicesCost = 0;
                $servicesData = [];
                if (!empty($request->services) && is_array($request->services)) {
                    foreach ($request->services as $srv) {
                        $serviceId = $srv['id'] ?? $srv['service_id'] ?? null;
                        $quantity = (int) ($srv['quantity'] ?? 1);
                        if ($serviceId && $quantity > 0) {
                            $serviceModel = \App\Models\Service::find($serviceId);
                            if ($serviceModel && $serviceModel->is_active) {
                                $servicePrice = (float) $serviceModel->price;
                                $servicesCost += $servicePrice * $quantity;
                                $servicesData[$serviceId] = [
                                    'quantity' => $quantity,
                                    'price' => $servicePrice,
                                ];
                                if ($serviceModel->stock !== null) {
                                    if ($serviceModel->stock < $quantity) {
                                        throw new HttpException(422, "Dịch vụ {$serviceModel->name} không đủ số lượng trong kho.");
                                    }
                                    $serviceModel->decrement('stock', $quantity);
                                }
                            }
                        }
                    }
                }

                $totalPrice = max(0, $originalPrice + $servicesCost - $discount);
                $paymentMethod = $request->input('payment_method', 'COD');
                /** @var \App\Models\User $user */
                $user = Auth::user();

                $wallet = method_exists($user, 'getOrCreateWallet') ? $user->getOrCreateWallet() : null;
                $currentWalletBalance = (float) ($wallet?->balance ?? $user->balance ?? 0);

                // Kiểm tra số dư ví nếu chọn phương thức thanh toán qua ví
                if ($paymentMethod === 'wallet') {
                    if ($currentWalletBalance < $totalPrice) {
                        throw new HttpException(400, 'Số dư ví không đủ để thanh toán (Số dư hiện có: ' . number_format($currentWalletBalance) . '₫, Cần thanh toán: ' . number_format($totalPrice) . '₫). Vui lòng nạp thêm tiền vào ví hoặc chọn phương thức khác.');
                    }
                }

                $booking = new Booking();
                $booking->court_id = $request->court_id;
                $booking->time_slot_id = $items->first()['time_slot_id'];
                $booking->user_id = $user->id;
                $booking->slot_date = $request->slot_date;
                $booking->start_time = $items->first()['start_time'];
                $booking->end_time = $items->last()['end_time'];
                $booking->total_price = $totalPrice;
                $booking->payment_method = $paymentMethod;

                if ($paymentMethod === 'wallet') {
                    $booking->status = 'confirmed';
                    $booking->payment_status = 'paid';
                } else {
                    $booking->status = 'pending';
                    $booking->payment_status = 'unpaid';
                }

                $booking->note = $request->note;
                $booking->timestamps = false;
                $booking->created_at = $now;
                $booking->updated_at = $now;
                $booking->save();

                // Xử lý trừ tiền ví & ghi nhận giao dịch
                if ($paymentMethod === 'wallet') {
                    if ($wallet) {
                        $wallet->balance -= $totalPrice;
                        $wallet->available_balance = max(0, (float) $wallet->available_balance - $totalPrice);
                        $wallet->save();
                        $newBalance = $wallet->balance;
                    } else {
                        $user->balance -= $totalPrice;
                        $newBalance = $user->balance;
                    }

                    // Đồng bộ lại bảng users.balance
                    $user->balance = $newBalance;
                    $user->save();

                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'wallet_id' => $wallet?->id,
                        'booking_id' => $booking->id,
                        'type' => 'payment',
                        'amount' => $totalPrice,
                        'balance_after' => $newBalance,
                        'description' => 'Thanh toán đơn đặt sân #' . $booking->id . ' qua số dư ví',
                    ]);

                    \App\Models\Transaction::create([
                        'booking_id' => $booking->id,
                        'user_id' => $user->id,
                        'transaction_code' => 'TXN-' . $booking->id . '-' . $now->format('YmdHis'),
                        'amount' => $totalPrice,
                        'payment_method' => 'wallet',
                        'payment_gateway' => 'WALLET',
                        'payment_status' => 'success',
                        'transaction_time' => $now,
                        'note' => 'Thanh toán bằng số dư ví.',
                    ]);

                    // Ghi nhận cộng tiền vào Ví Nền Tảng của Admin
                    app(\App\Services\PlatformWalletService::class)->credit(
                        amount: $totalPrice,
                        type: \App\Models\PlatformWalletTransaction::TYPE_CUSTOMER_ONLINE_PAYMENT_IN,
                        description: 'Khách thanh toán booking online: BOOKING-' . $booking->id,
                        referenceType: 'booking',
                        referenceId: $booking->id,
                        reference: 'BOOKING-' . $booking->id,
                        metadata: [
                            'payment_method' => 'wallet',
                        ]
                    );
                }

                if ($voucher) {
                    $booking->vouchers()->attach($voucher->id, ['discount_amount' => $discount]);
                    app(\App\Services\VoucherService::class)->incrementUsage($voucher);
                }

                if (!empty($servicesData)) {
                    $booking->services()->attach($servicesData);
                }

                $booking->items()->createMany($items->map(function ($item) {
                    unset($item['created_at'], $item['updated_at']);
                    return $item;
                })->all());

                $booking->recordStatusChange($user->id, '', $booking->status, $paymentMethod === 'wallet' ? 'Thanh toán thành công qua số dư ví' : 'Người dùng tạo booking', $now);

                return $booking;
            });
        } catch (HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        dispatch(new SendBookingConfirmation($booking));
        $booking->load(['court.venue', 'items']);

        // Notify customer and owner about new booking (best-effort)
        try {
            app(\App\Services\NotificationService::class)->notifyBookingPlaced($booking);
            $ownerId = $booking->court->venue->owner_id ?? null;
            if ($ownerId) {
                app(\App\Services\NotificationService::class)->notifyOwnerNewBooking($ownerId, $booking);
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        return response()->json([
            'message' => 'Booking confirmed successfully',
            'data' => [
                'id' => $booking->id,
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'items_count' => $booking->items->count(),
            ],
        ], 201);
    }
}
