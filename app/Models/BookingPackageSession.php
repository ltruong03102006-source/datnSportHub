<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingPackageSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_package_id',
        'court_id',
        'time_slot_id',
        'weekday',
        'session_order',
        'price_per_session',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'session_order' => 'integer',
        'price_per_session' => 'decimal:2',
    ];

    public function bookingPackage(): BelongsTo
    {
        return $this->belongsTo(BookingPackage::class, 'booking_package_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BookingPackageSessionSlot::class, 'booking_package_session_id')
            ->orderBy('slot_order');
    }

    public function weekdayLabel(): string
    {
        return match ((int) $this->weekday) {
            0 => 'Chủ nhật',
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            default => 'Không xác định',
        };
    }
}
