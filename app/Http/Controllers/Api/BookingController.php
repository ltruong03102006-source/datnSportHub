<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Court;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BookingController extends Controller
{
    public function store(BookingRequest $request)
    {
        $court = Court::find($request->court_id);

        if (! $court || $court->status !== 'active' || ! $court->is_bookable_online) {
            return response()->json(['message' => 'Sân không nhận đặt online'], 403);
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

        try {
            foreach ($slots as $index => $slot) {
                if ($index > 0) {
                    $previous = $slots[$index - 1];

                    if ($previous['end_time'] > $slot['start_time']) {
                        throw new HttpException(409, 'Các ca không được xếp chồng lên nhau');
                    }
                }
            }

            $bookings = DB::transaction(function () use ($request, $slots, $dayOfWeek, $now) {
                $created = collect();

                foreach ($slots as $slot) {
                    $conflict = Booking::where('court_id', $request->court_id)
                        ->where('slot_date', $request->slot_date)
                        ->whereIn('status', ['confirmed', 'completed'])
                        ->where(function ($q) use ($slot) {
                            $q->where('start_time', '<', $slot['end_time'])
                                ->where('end_time', '>', $slot['start_time']);
                        })
                        ->lockForUpdate()
                        ->exists();

                    if ($conflict) {
                        throw new HttpException(409, 'This time slot has already been booked');
                    }

                    $price = DB::table('slot_prices')
                        ->join('time_slots', 'slot_prices.time_slot_id', '=', 'time_slots.id')
                        ->where('time_slots.court_id', $request->court_id)
                        ->where('time_slots.start_time', $slot['start_time'])
                        ->where(function ($q) use ($dayOfWeek) {
                            $q->where('slot_prices.day_of_week', $dayOfWeek)
                                ->orWhereNull('slot_prices.day_of_week');
                        })
                        ->orderByRaw('day_of_week IS NULL ASC')
                        ->value('price') ?? 0;

                    $booking = new Booking();
                    $booking->court_id = $request->court_id;
                    $booking->user_id = Auth::id();
                    $booking->slot_date = $request->slot_date;
                    $booking->start_time = $slot['start_time'];
                    $booking->end_time = $slot['end_time'];
                    $booking->total_price = $price;
                    $booking->status = 'pending';
                    $booking->payment_status = 'unpaid';
                    $booking->note = $request->note;

                    $booking->timestamps = false;
                    $booking->created_at = $now;
                    $booking->updated_at = $now;
                    $booking->save();

                    $booking->recordStatusChange(Auth::id(), '', 'pending', 'Người dùng tạo booking', $now);

                    $created->push($booking);
                }

                return $created;
            });
        } catch (HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        $bookings = $bookings->map(function ($booking) {
            dispatch(new SendBookingConfirmation($booking));
            return $booking->load('court.venue');
        });

        // Notify customer and owner(s) about new booking(s) (best-effort)
        try {
            foreach ($bookings as $booking) {
                app(\App\Services\NotificationService::class)->notifyBookingPlaced($booking);
                $ownerId = $booking->court->venue->owner_id ?? null;
                if ($ownerId) {
                    app(\App\Services\NotificationService::class)->notifyOwnerNewBooking($ownerId, $booking);
                }
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        $data = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ];
        });

        return response()->json([
            'message' => 'Booking confirmed successfully',
            'data' => $bookings->count() === 1 ? $data->first() : $data,
        ], 201);
    }
}
