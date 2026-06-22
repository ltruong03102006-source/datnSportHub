<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRescheduleRequest extends Model
{
    protected $fillable = ['booking_id','user_id','old_slot_date','old_start_time','old_end_time','old_time_slot_id','new_slot_date','new_time_slot_id','reason','status','owner_note','reviewed_by','reviewed_at'];
    protected $casts = ['old_slot_date' => 'date', 'new_slot_date' => 'date', 'reviewed_at' => 'datetime'];
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function newTimeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class, 'new_time_slot_id'); }
    public function slots(): HasMany { return $this->hasMany(BookingRescheduleRequestSlot::class); }
}
