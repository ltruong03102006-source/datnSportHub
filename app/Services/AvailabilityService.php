<?php

namespace App\Services;

use App\Models\Court;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getAvailability(Court $court, Carbon $date): Collection
    {
        $dayOfWeek = $date->dayOfWeek;

        $slots = $court->timeSlots()
            ->with(['prices' => fn($query) => $query->where('day_of_week', $dayOfWeek)])
            ->get();

        $bookedSlots = $court->bookings()
            ->whereDate('slot_date', $date->format('Y-m-d'))
            // Chỉ booking đã xác nhận mới khóa ca.
            // Booking pending chỉ là yêu cầu, khách khác vẫn được gửi yêu cầu cùng ca.
            ->where('status', 'confirmed')
            ->get();

        return $slots->map(function($slot) use ($bookedSlots, $date) {
            $isBooked = $bookedSlots->contains(function ($booking) use ($slot) {
                return $booking->start_time < $slot->end_time
                    && $booking->end_time > $slot->start_time;
            });
            $isPast = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->start_time)->isPast();

            return [
                'slot_id'          => $slot->id,
                'court_id'         => $slot->court_id,
                'start_time'       => substr($slot->start_time, 0, 5),
                'end_time'         => substr($slot->end_time, 0, 5),
                'duration_minutes' => $slot->duration_minutes,
                'price'            => $slot->prices?->first()?->price ?? 0,
                'price_type'       => $slot->prices?->first()?->price_type ?? 'normal',
                'is_available'     => !$isBooked && !$isPast,
                'is_past'          => $isPast,
                'is_booked'        => $isBooked, // Biến này bắt buộc phải có
            ];
        });
    }
}
