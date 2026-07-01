<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VenuePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'type',
        'duration',
        'max_sessions_per_week',
        'discount_percent',
        'max_subscribers',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
        'max_sessions_per_week' => 'integer',
        'discount_percent' => 'decimal:2',
        'max_subscribers' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookingPackages(): HasMany
    {
        return $this->hasMany(BookingPackage::class, 'package_id');
    }

    public function activeBookingPackages(): HasMany
    {
        return $this->bookingPackages()
            ->whereIn('status', [
                'pending_payment',
                'active',
                'paused',
            ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isWeeklyPackage(): bool
    {
        return $this->type === 'week';
    }

    public function isMonthlyPackage(): bool
    {
        return $this->type === 'month';
    }

    public function allowsDailyPlay(): bool
    {
        return (int) $this->max_sessions_per_week === 7;
    }

    public function durationLabel(): string
    {
        return $this->type === 'week'
            ? $this->duration . ' tuần'
            : $this->duration . ' tháng';
    }

    public function discountLabel(): string
    {
        return rtrim(rtrim(number_format((float) $this->discount_percent, 2), '0'), '.') . '%';
    }

    public function hasSubscriberLimit(): bool
    {
        return ! is_null($this->max_subscribers);
    }

    public function remainingSlots(): ?int
    {
        if (! $this->hasSubscriberLimit()) {
            return null;
        }

        $used = $this->activeBookingPackages()->count();

        return max(0, (int) $this->max_subscribers - $used);
    }

    public function isFull(): bool
    {
        if (! $this->hasSubscriberLimit()) {
            return false;
        }

        return $this->remainingSlots() <= 0;
    }
}