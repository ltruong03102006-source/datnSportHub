<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BookingRescheduleRequestSlot extends Model { protected $fillable=['booking_reschedule_request_id','booking_id','old_slot_date','old_start_time','old_end_time','new_slot_date','new_time_slot_id']; protected $casts=['old_slot_date'=>'date','new_slot_date'=>'date']; public function booking(): BelongsTo{return $this->belongsTo(Booking::class);} public function newTimeSlot(): BelongsTo{return $this->belongsTo(TimeSlot::class,'new_time_slot_id');} }
