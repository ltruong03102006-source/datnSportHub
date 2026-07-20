<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRescheduleRequest extends Model
{
    protected $fillable = [
        'request_code',
        'booking_id',
        'user_id',
        'booking_item_id',
        'old_slot_date',
        'old_start_time',
        'old_end_time',
        'old_time_slot_id',
        'new_slot_date',
        'new_time_slot_id',
        'new_start_time',
        'new_end_time',
        'reason',
        'status',
        'owner_note',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected $casts = [
        'old_slot_date' => 'date',
        'new_slot_date' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function bookingItem(): BelongsTo { return $this->belongsTo(BookingItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function oldTimeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class, 'old_time_slot_id'); }
    public function newTimeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class, 'new_time_slot_id'); }
    public function slots(): HasMany { return $this->hasMany(BookingRescheduleRequestSlot::class); }
}
