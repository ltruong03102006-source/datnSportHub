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
            ->with([
                'prices' => fn($query) => $query->where('day_of_week', $dayOfWeek)
            ])
            ->get();

        $bookedSlots = $court->bookings()
            ->where('slot_date', $date->toDateString())
            ->whereNot('status', 'cancelled')
            ->select('start_time', 'end_time')
            ->get()
            ->keyBy(fn($booking) => $booking->start_time . '-' . $booking->end_time);

        return $slots->map(fn($slot) => [
            'slot_id' => $slot->id,
            'court_id' => $court->id,
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'duration_minutes' => $slot->duration_minutes,
            'price' => $slot->prices?->first()?->price ?? 0,
            'price_type' => $slot->prices?->first()?->price_type ?? 'normal',
            'is_available' => !$bookedSlots->has($slot->start_time . '-' . $slot->end_time),
        ]);
    }
}
