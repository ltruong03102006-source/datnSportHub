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

                $booking = new Booking();
                $booking->court_id = $request->court_id;
                $booking->time_slot_id = $items->first()['time_slot_id'];
                $booking->user_id = Auth::id();
                $booking->slot_date = $request->slot_date;
                $booking->start_time = $items->first()['start_time'];
                $booking->end_time = $items->last()['end_time'];
                $booking->total_price = $originalPrice - $discount;
                $booking->status = 'pending';
                $booking->payment_status = 'unpaid';
                $booking->note = $request->note;
                $booking->timestamps = false;
                $booking->created_at = $now;
                $booking->updated_at = $now;
                $booking->save();

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
