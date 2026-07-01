<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return max(0, (int) $this->total_sessions - (int) $this->used_sessions);
    }

    public function progressLabel(): string
    {
        return "{$this->used_sessions}/{$this->total_sessions} buổi";
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
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