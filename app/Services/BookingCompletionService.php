<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingCompletionService
{
    public function __construct(
        private SettlementService $settlementService
    ) {}

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

    public function settleCompletedBookings(?int $ownerId = null, ?int $userId = null): int
    {
        if (! $this->canSettleBookings()) {
            return 0;
        }

        $query = Booking::query()
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->whereNull('settlement_status')
                    ->orWhereIn('settlement_status', ['pending', 'processing', 'failed'])
                    ->orWhere(function ($settledQuery) {
                        $settledQuery->where('settlement_status', 'settled')
                            ->where(function ($invalidSettledQuery) {
                                $invalidSettledQuery->whereNull('settled_at')
                                    ->orWhere(function ($amountQuery) {
                                        $amountQuery->whereNull('booking_package_id')
                                            ->where('platform_fee', '<=', 0)
                                            ->where('owner_earnings', '<=', 0);
                                    });
                            });
                    });
            })
            ->when($ownerId, fn ($query) => $query->whereHas(
                'court.venue',
                fn ($venueQuery) => $venueQuery->where('owner_id', $ownerId)
            ))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('id');

        $settled = 0;

        foreach ($query->get() as $booking) {
            try {
                $this->settlementService->settleBooking($booking);
                $settled++;
            } catch (\Throwable $e) {
                \Log::channel('settlement')->error("Lỗi đối soát Booking #{$booking->id}: " . $e->getMessage());

                $booking->update(['settlement_status' => \App\Enums\SettlementStatus::FAILED]);
            }
        }

        return $settled;
    }

    private function completeGroup(Collection $group, Carbon $now): int
    {
        $ids = $group->pluck('id')->all();
        $canSettleBookings = $this->canSettleBookings();

        return DB::transaction(function () use ($ids, $now, $canSettleBookings): int {
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

                if ($canSettleBookings) {
                    try {
                        $this->settlementService->settleBooking($booking->fresh());
                    } catch (\Throwable $e) {
                        \Log::channel('settlement')->error("Lỗi đối soát Booking #{$booking->id}: " . $e->getMessage());

                        $booking->update(['settlement_status' => \App\Enums\SettlementStatus::FAILED]);
                    }
                }
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

    private function canSettleBookings(): bool
    {
        if (! Schema::hasTable('bookings')) {
            return false;
        }

        foreach (['settlement_status', 'settled_at', 'platform_fee', 'owner_earnings'] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                return false;
            }
        }

        if (! Schema::hasTable('wallets') || ! Schema::hasTable('wallet_transactions')) {
            return false;
        }

        foreach (['wallet_id', 'booking_id', 'reference', 'balance_before', 'metadata'] as $column) {
            if (! Schema::hasColumn('wallet_transactions', $column)) {
                return false;
            }
        }

        return true;
    }

    public function cancelExpiredPendingBookings(?int $ownerId = null, ?int $userId = null): int
    {
        $holdTimeMinutes = \App\Models\Setting::get('booking_hold_time', 15);
        $now = now('Asia/Ho_Chi_Minh');
        
        $expiredBookings = Booking::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<=', $now->subMinutes($holdTimeMinutes))
            ->when($ownerId, fn ($query) => $query->whereHas(
                'court.venue',
                fn ($venueQuery) => $venueQuery->where('owner_id', $ownerId)
            ))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->get();
            
        $cancelledCount = 0;
        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Hệ thống tự động hủy do quá hạn thanh toán.',
            ]);
            \App\Models\BookingLog::create([
                'booking_id' => $booking->id,
                'changed_by' => $booking->user_id, // Ghi nhận là user bị hủy do quá hạn
                'old_status' => 'pending',
                'new_status' => 'cancelled',
                'note' => "Quá hạn thanh toán ({$holdTimeMinutes} phút). Slot đã được giải phóng.",
            ]);
            $cancelledCount++;
        }
        
        return $cancelledCount;
    }
}
