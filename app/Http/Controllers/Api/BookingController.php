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

        // VÁ LỖI TẠI ĐÂY: Chốt đúng 1 mốc thời gian duy nhất cho tất cả các ca trong đơn này
        $now = Carbon::now();

        $bookings = DB::transaction(function () use ($request, $slots, $dayOfWeek, $now) {
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

                // VÁ LỖI: Khởi tạo model và tắt tự động timestamp để ép giờ
                $booking = new Booking();
                $booking->court_id = $request->court_id;
                $booking->user_id = Auth::id();
                $booking->slot_date = $request->slot_date;
                $booking->start_time = $slot['start_time'];
                $booking->end_time = $slot['end_time'];
                $booking->total_price = $price;
                $booking->status = 'pending';
                $booking->note = $request->note;
                
                $booking->timestamps = false; // Tắt tự động nhảy giờ của Laravel
                $booking->created_at = $now;  // Gắn mốc thời gian dùng chung
                $booking->updated_at = $now;
                $booking->save();

                // Lưu log cũng dùng chung mốc thời gian đó
                $log = new BookingLog();
                $log->booking_id = $booking->id;
                $log->changed_by = Auth::id();
                $log->old_status = '';
                $log->new_status = 'pending';
                $log->note = 'Người dùng tạo booking';
                $log->timestamps = false; // Tắt tự động nhảy giờ
                $log->created_at = $now;  // CHỈ LƯU created_at
                // (Đã xóa dòng $log->updated_at ở đây)
                $log->save();

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
