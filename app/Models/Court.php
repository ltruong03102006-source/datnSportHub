<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Court extends Model
{
    use HasFactory;

    protected $table = 'courts';

    protected $fillable = ['venue_id', 'name', 'status', 'is_bookable_online'];

    protected $casts = [
        'is_bookable_online' => 'boolean',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope: Lấy chỉ các sân đang hoạt động
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Lấy các sân bị ẩn
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Kiểm tra sân có thể đặt trực tuyến không
     */
    public function canBeBooked(): bool
    {
        return $this->status === 'active' && $this->is_bookable_online;
    }

    /**
     * Kiểm tra sân có lịch đặt trong tương lai không
     */
    public function hasFutureBookings(): bool
    {
        return $this->bookings()
            ->where('date', '>=', now()->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->exists();
    }
}
