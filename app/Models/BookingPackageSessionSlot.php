<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPackageSessionSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_package_session_id',
        'time_slot_id',
        'slot_order',
        'price',
    ];

    protected $casts = [
        'slot_order' => 'integer',
        'price' => 'decimal:2',
    ];

    public function bookingPackageSession(): BelongsTo
    {
        return $this->belongsTo(BookingPackageSession::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
