<?php

namespace App\Services;

use App\Models\BookingPackageSession;
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

        $packageSessions = $this->activePackageSessionsForDate($court, $date);

        return $slots->map(function($slot) use ($bookedSlots, $packageSessions, $date) {
            $isBooked = $bookedSlots->contains(function ($booking) use ($slot) {
                return $booking->start_time < $slot->end_time
                    && $booking->end_time > $slot->start_time;
            }) || $this->packageSessionsOverlap($packageSessions, $slot->start_time, $slot->end_time);

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

    public function hasActivePackageBooking(Court $court, Carbon|string $date, string $startTime, string $endTime): bool
    {
        $date = $date instanceof Carbon
            ? $date->copy()
            : Carbon::parse($date);

        return $this->packageSessionsOverlap(
            $this->activePackageSessionsForDate($court, $date),
            $startTime,
            $endTime
        );
    }

    private function activePackageSessionsForDate(Court $court, Carbon $date): Collection
    {
        return BookingPackageSession::query()
            ->with(['timeSlot', 'slots.timeSlot'])
            ->where('court_id', $court->id)
            ->where('weekday', $date->dayOfWeek)
            ->whereHas('bookingPackage', function ($query) use ($date) {
                $query->where('status', 'active')
                    ->whereDate('start_date', '<=', $date->toDateString())
                    ->whereDate('end_date', '>=', $date->toDateString());
            })
            ->get();
    }

    private function packageSessionsOverlap(Collection $packageSessions, string $startTime, string $endTime): bool
    {
        $startTime = $this->normalizeTime($startTime);
        $endTime = $this->normalizeTime($endTime);

        return $packageSessions->contains(function (BookingPackageSession $session) use ($startTime, $endTime) {
            $slotRows = $session->slots->isNotEmpty()
                ? $session->slots->sortBy('slot_order')->values()
                : collect([(object) ['timeSlot' => $session->timeSlot]]);

            $firstSlot = $slotRows->first()?->timeSlot;
            $lastSlot = $slotRows->last()?->timeSlot;

            if (! $firstSlot || ! $lastSlot) {
                return false;
            }

            $packageStart = $this->normalizeTime($firstSlot->start_time);
            $packageEnd = $this->normalizeTime($lastSlot->end_time);

            return $packageStart < $endTime && $packageEnd > $startTime;
        });
    }

    private function normalizeTime(string $time): string
    {
        return date('H:i:s', strtotime($time));
    }
}
