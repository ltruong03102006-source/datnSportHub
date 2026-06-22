<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingCompletionService
{
    public function completeExpiredBookings(?int $ownerId = null, ?int $userId = null): int
    {
        $now = now('Asia/Ho_Chi_Minh');

        $bookings = Booking::query()
            ->where('status', 'confirmed')
            ->when($ownerId, fn ($query) => $query->whereHas(
                'court.venue',
                fn ($venueQuery) => $venueQuery->where('owner_id', $ownerId)
            ))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->get();

        $completed = 0;

        foreach ($bookings->groupBy(fn (Booking $booking) => $this->groupKey($booking)) as $group) {
            if ($this->hasEnded($group, $now)) {
                $completed += $this->completeGroup($group, $now);
            }
        }

        return $completed;
    }

    private function completeGroup(Collection $group, Carbon $now): int
    {
        $ids = $group->pluck('id')->all();

        return DB::transaction(function () use ($ids, $now): int {
            $bookings = Booking::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            $confirmedBookings = $bookings->where('status', 'confirmed');

            if ($confirmedBookings->isEmpty()) {
                return 0;
            }

            foreach ($confirmedBookings as $booking) {
                $booking->update(['status' => 'completed']);
                $booking->recordStatusChange(
                    $booking->user_id,
                    'confirmed',
                    'completed',
                    'Scheduler completed expired booking',
                    $now
                );
            }

            $representativeBooking = $confirmedBookings->sortBy('id')->first();

            if ($representativeBooking->review_reminder_sent_at === null) {
                $representativeBooking->update(['review_reminder_sent_at' => $now]);
            }

            return $confirmedBookings->count();
        });
    }

    private function hasEnded(Collection $group, Carbon $now): bool
    {
        $lastBooking = $group->sortByDesc('end_time')->first();
        $endsAt = Carbon::parse(
            $lastBooking->slot_date->format('Y-m-d').' '.$lastBooking->end_time,
            'Asia/Ho_Chi_Minh'
        );

        return $now->greaterThanOrEqualTo($endsAt);
    }

    private function groupKey(Booking $booking): string
    {
        return implode('_', [
            $booking->user_id,
            $booking->court_id,
            $booking->slot_date->format('Y-m-d'),
            $booking->created_at?->format('Y-m-d H:i:s.u') ?? $booking->id,
        ]);
    }
}
