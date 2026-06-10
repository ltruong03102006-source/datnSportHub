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
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();

        $bookedKeys = $bookedSlots->map(function($booking) {
            return substr($booking->start_time, 0, 5) . '-' . substr($booking->end_time, 0, 5);
        })->toArray();

        return $slots->map(function($slot) use ($bookedKeys, $date) {
            $slotTimeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);
            $isBooked = in_array($slotTimeKey, $bookedKeys);
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
