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

                // BẮT ĐẦU: TÍNH TIỀN DỊCH VỤ & CHUẨN BỊ MẢNG LƯU DATABASE
                $serviceListToSave = []; 
                $servicesTotal = 0;
                
                if ($request->has('services') && is_array($request->services)) {
                    $totalMinutes = 0;
                    foreach ($slots as $s) {
                        $totalMinutes += \Carbon\Carbon::parse($s['start_time'])->diffInMinutes(\Carbon\Carbon::parse($s['end_time']));
                    }
                    $totalHours = $totalMinutes / 60;

                    foreach ($request->services as $svc) {
                        $qty = max(1, (int) ($svc['quantity'] ?? 1));
                        $unitPrice = (float) ($svc['price'] ?? 0);
                        $type = $svc['type'] ?? 'retail';

                        $serviceLineTotal = $unitPrice * $qty;
                        if ($type === 'rental') {
                            $serviceLineTotal = $unitPrice * $qty * $totalHours;
                        }

                        $servicesTotal += $serviceLineTotal;

                        // Chuẩn hóa dữ liệu pivot: price = đơn giá, quantity = số lượng.
                        // View/Invoice sẽ tính line total = quantity * price.
                        $serviceListToSave[] = [
                            'service_id' => $svc['id'],
                            'quantity'   => $qty,
                            'price'      => $unitPrice,
                        ];
                    }
                }
                
                $originalPrice += $servicesTotal;
                // KẾT THÚC: TÍNH TIỀN DỊCH VỤ

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

                $paymentMethod = $request->input('payment_method', 'COD');
                $finalPrice = $originalPrice - $discount;
                $user = Auth::user();
                $customerWallet = null;

                if ($paymentMethod === 'wallet') {
                    if (!$user) {
                        throw new HttpException(401, 'Vui lòng đăng nhập để thực hiện thanh toán.');
                    }
                    $customerWallet = $user->getOrCreateWallet();
                    if ((float)$customerWallet->balance < $finalPrice) {
                        throw new HttpException(400, 'Số dư ví không đủ để thanh toán (' . number_format($customerWallet->balance) . 'đ). Vui lòng chọn phương thức khác hoặc nạp thêm tiền.');
                    }

                    // 1. Trừ tiền ví khách hàng
                    app(\App\Services\WalletService::class)->processTransaction(
                        wallet: $customerWallet,
                        type: \App\Enums\TransactionType::PAYMENT,
                        amount: $finalPrice,
                        description: "Thanh toán đơn đặt sân",
                    );
                }

                $booking = new Booking();
                $booking->court_id = $request->court_id;
                $booking->time_slot_id = $items->first()['time_slot_id'];
                $booking->user_id = Auth::id();
                $booking->slot_date = $request->slot_date;
                $booking->start_time = $items->first()['start_time'];
                $booking->end_time = $items->last()['end_time'];
                $booking->total_price = $finalPrice; // Lưu ý: $finalPrice này đã bao gồm $servicesTotal ở trên rồi
                $booking->payment_method = $paymentMethod;
                $booking->status = $paymentMethod === 'wallet' ? 'confirmed' : 'pending';
                $booking->payment_status = $paymentMethod === 'wallet' ? 'paid' : 'unpaid';
                $booking->note = $request->note;
                $booking->timestamps = false;
                $booking->created_at = $now;
                $booking->updated_at = $now;
                $booking->save();

                // BẮT ĐẦU: ÉP LƯU TRỰC TIẾP DỊCH VỤ XUỐNG DATABASE BẰNG QUERY BUILDER
                if (!empty($serviceListToSave)) {
                    $insertData = [];
                    foreach ($serviceListToSave as $item) {
                        $insertData[] = [
                            'booking_id' => $booking->id,
                            'service_id' => $item['service_id'],
                           'quantity'   => $item['quantity'], // Số lượng dịch vụ
                           'price'      => $item['price'],    // Đơn giá / giá theo đơn vị
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    \Illuminate\Support\Facades\DB::table('booking_services')->insert($insertData);
                }
                // KẾT THÚC: LƯU DỊCH VỤ
                // KẾT THÚC: LƯU DỊCH VỤ

                if ($paymentMethod === 'wallet') {
                    // ... Logic trừ ví của bạn ...
                    // 2. Nạp tiền vào Ví Nền Tảng (Platform Wallet)
                    app(\App\Services\PlatformWalletService::class)->credit(
                        amount: $finalPrice,
                        type: 'booking_payment',
                        description: "Thanh toán đặt sân bằng ví khách hàng #{$booking->id}",
                        referenceType: 'booking',
                        referenceId: $booking->id,
                        reference: 'BOOKING-' . $booking->id
                    );

                    // 3. Tạo bản ghi giao dịch
                    \App\Models\Transaction::updateOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'user_id' => $booking->user_id,
                            'transaction_code' => 'TXN-W-' . $booking->id . '-' . now()->format('YmdHis'),
                            'amount' => $booking->total_price,
                            'payment_method' => 'wallet',
                            'payment_gateway' => 'WALLET',
                            'payment_status' => 'success',
                            'transaction_time' => now(),
                            'note' => 'Khách hàng thanh toán qua số dư ví.',
                        ]
                    );
                }

                if ($voucher) {
                    $booking->vouchers()->attach($voucher->id, ['discount_amount' => $discount]);
                    app(\App\Services\VoucherService::class)->incrementUsage($voucher);
                }

                $booking->items()->createMany($items->map(function ($item) {
                    unset($item['created_at'], $item['updated_at']);
                    return $item;
                })->all());

                $booking->recordStatusChange(Auth::id(), '', 'pending', 'Người dùng tạo booking', $now);

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
