<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Models\Setting;

class BookingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'venue_id',
        'package_id',

        'start_date',
        'end_date',

        'weekly_sessions',
        'total_sessions',
        'used_sessions',

        'total_amount',
        'discount_amount',
        'final_amount',

        'status',

        'paid_at',
        'paused_at',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',

        'weekly_sessions' => 'integer',
        'total_sessions' => 'integer',
        'used_sessions' => 'integer',

        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',

        'paid_at' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(VenuePackage::class, 'package_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(BookingPackageSession::class, 'booking_package_id')
            ->orderBy('session_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'booking_package_id')
            ->orderBy('slot_date')
            ->orderBy('start_time');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'booking_package_id');
    }

    public function pendingBookings(): HasMany
    {
        return $this->bookings()->where('status', 'pending');
    }

    public function confirmedBookings(): HasMany
    {
        return $this->bookings()->where('status', 'confirmed');
    }

    public function completedBookings(): HasMany
    {
        return $this->bookings()->where('status', 'completed');
    }

    public function cancelledBookings(): HasMany
    {
        return $this->bookings()->where('status', 'cancelled');
    }

    public function isPendingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function canBePaid(): bool
    {
        return $this->isPendingPayment();
    }

    public function canBePaused(): bool
    {
        return $this->isActive();
    }

    public function canBeResumed(): bool
    {
        return $this->isPaused();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            'pending_payment',
            'active',
            'paused',
        ], true);
    }

    public function remainingSessions(): int
    {
        return max(0, (int) $this->total_sessions - $this->usedSessionsCount());
    }

    public function usedSessionsCount(): int
    {
        $now = now('Asia/Ho_Chi_Minh');

        $isUsed = function (Booking $booking) use ($now): bool {
            if ($booking->status === 'completed') {
                return true;
            }

            if (in_array($booking->status, ['cancelled'], true)) {
                return false;
            }

            if (! $booking->slot_date || ! $booking->end_time) {
                return false;
            }

            return Carbon::parse($booking->slot_date->toDateString().' '.$booking->end_time, 'Asia/Ho_Chi_Minh')->lt($now);
        };

        if ($this->relationLoaded('bookings')) {
            if ($this->bookings->isNotEmpty()) {
                return $this->bookings->filter($isUsed)->count();
            }

            return $this->estimatedUsedSessionsCount($now);
        }

        $bookings = $this->bookings()->get();

        if ($bookings->isNotEmpty()) {
            return $bookings->filter($isUsed)->count();
        }

        return $this->estimatedUsedSessionsCount($now);
    }

    private function estimatedUsedSessionsCount(Carbon $now): int
    {
        if (! in_array($this->status, ['active', 'paused', 'completed'], true)) {
            return 0;
        }

        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        $this->loadMissing(['sessions.timeSlot', 'sessions.slots.timeSlot']);

        $startDate = Carbon::parse($this->start_date)->startOfDay();
        $endDate = Carbon::parse($this->end_date)->startOfDay();
        $used = 0;

        foreach ($this->sessions as $session) {
            $sessionSlots = $session->slots->isNotEmpty()
                ? $session->slots->sortBy('slot_order')->values()
                : collect([(object) ['timeSlot' => $session->timeSlot]]);

            $lastSlot = $sessionSlots->last()?->timeSlot;

            if (! $lastSlot?->end_time) {
                continue;
            }

            $cursor = $startDate->dayOfWeek === (int) $session->weekday
                ? $startDate->copy()
                : $startDate->copy()->next((int) $session->weekday);

            while ($cursor->lte($endDate)) {
                $sessionEnd = Carbon::parse($cursor->toDateString().' '.$lastSlot->end_time, 'Asia/Ho_Chi_Minh');

                if ($sessionEnd->lt($now)) {
                    $used++;
                }

                $cursor->addWeek();
            }
        }

        return min($used, (int) $this->total_sessions);
    }

    public function progressLabel(): string
    {
        return "{$this->usedSessionsCount()}/{$this->total_sessions} buổi";
    }

    public function paymentHoldExpiresAt(): ?Carbon
    {
        if (! $this->created_at) {
            return null;
        }

        $holdMinutes = max(1, (int) Setting::get('booking_hold_time', 15));

        return Carbon::parse($this->created_at)->addMinutes($holdMinutes);
    }

    public function paymentHoldExpired(): bool
    {
        $expiresAt = $this->paymentHoldExpiresAt();

        return $this->status === 'pending_payment'
            && $expiresAt
            && $expiresAt->lte(now());
    }

    public function displayStatus(): string
    {
        return $this->paymentHoldExpired() ? 'cancelled' : (string) $this->status;
    }

    public function statusLabel(): string
    {
        return match ($this->displayStatus()) {
            'pending_payment' => 'Chờ thanh toán',
            'active' => 'Đang hoạt động',
            'paused' => 'Tạm dừng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'expired' => 'Hết hạn',
            default => 'Không xác định',
        };
    }
}
