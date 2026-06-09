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

        foreach ($slots as $index => $slot) {
            if ($index > 0) {
                $previous = $slots[$index - 1];

                if ($previous['end_time'] > $slot['start_time']) {
                    throw new HttpException(409, 'Các ca không được xếp chồng lên nhau');
                }
            }
        }

        $dayOfWeek = Carbon::parse($request->slot_date)->dayOfWeek;

        $bookings = DB::transaction(function () use ($request, $slots, $dayOfWeek) {
            $created = collect();

            foreach ($slots as $slot) {
                $conflict = Booking::where('court_id', $request->court_id)
                    ->where('slot_date', $request->slot_date)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->where(function ($q) use ($slot) {
                        $q->where('start_time', '<', $slot['end_time'])
                          ->where('end_time', '>', $slot['start_time']);
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    throw new HttpException(409, 'Khung giờ này đã được đặt');
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

                $booking = Booking::create([
                    'court_id' => $request->court_id,
                    'user_id' => Auth::id(),
                    'slot_date' => $request->slot_date,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'total_price' => $price,
                    'status' => 'pending',
                    'note' => $request->note,
                ]);

                BookingLog::create([
                    'booking_id' => $booking->id,
                    'changed_by' => Auth::id(),
                    'old_status' => '',
                    'new_status' => 'pending',
                    'note' => 'Người dùng tạo booking',
                ]);

                $created->push($booking);
            }

            return $created;
        });

        $bookings = $bookings->map(function ($booking) {
            dispatch(new SendBookingConfirmation($booking));
            return $booking->load('court.venue');
        });

        return response()->json([
            'message' => 'Đặt sân thành công',
            'data' => $bookings,
        ], 201);
    }
}
