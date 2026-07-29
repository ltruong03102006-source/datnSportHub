<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    use HasFactory;

    protected $table = 'time_slots';

    protected $fillable = [
        'court_id',
        'start_time',
        'end_time',
        'duration_minutes',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Scope: Lấy khung giờ của các sân con chỉ định (dùng để tính công suất sân)
     */
    public function scopeForCourts(Builder $query, $courtIds): Builder
    {
        return $query->whereIn('court_id', $courtIds);
    }

    /**
     * Quan hệ giá theo từng thứ trong tuần.
     * 0 = Chủ nhật, 1 = Thứ 2, ..., 6 = Thứ 7.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(SlotPrice::class, 'time_slot_id');
    }

    /**
     * Alias để dùng cho code đặt gói.
     */
    public function slotPrices(): HasMany
    {
        return $this->hasMany(SlotPrice::class, 'time_slot_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'time_slot_id');
    }

    public function bookingPackageSessions(): HasMany
    {
        return $this->hasMany(BookingPackageSession::class, 'time_slot_id');
    }
}