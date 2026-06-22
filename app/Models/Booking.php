<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    // ĐÂY LÀ CHUẨN CỦA LARAVEL
    protected $fillable = [
        'court_id',
        'time_slot_id',
        'user_id',
        'slot_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'payment_status',
        'note',
        'cancel_reason',
        'cancellation_fee',
         'refund_amount', 
         'refund_status',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'slot_date' => 'date',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function timeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class); }
    public function rescheduleRequests(): HasMany { return $this->hasMany(BookingRescheduleRequest::class); }

    public function recordStatusChange(int $changedBy, string $oldStatus, string $newStatus, ?string $note = null, $createdAt = null): BookingLog
    {
        $log = new BookingLog();
        $log->booking_id = $this->id;
        $log->changed_by = $changedBy;
        $log->old_status = $oldStatus;
        $log->new_status = $newStatus;
        $log->note = $note;
        $log->timestamps = false;

        if ($createdAt !== null) {
            $log->created_at = $createdAt;
        }

        $log->save();

        return $log;
    }
    public function getCancellationPolicy(): array
    {
        $slotDate = $this->slot_date instanceof \Carbon\Carbon 
            ? $this->slot_date->format('Y-m-d') 
            : \Carbon\Carbon::parse($this->slot_date)->format('Y-m-d');
            
        $startsAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $slotDate . ' ' . $this->start_time, 'Asia/Ho_Chi_Minh');
        $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');
        
        // Tính số giờ còn lại trước khi đá (false để giữ số âm nếu đã quá giờ)
        $hoursDiff = $now->diffInHours($startsAt, false);

        if ($hoursDiff >= 24) {
            return ['fee_percent' => 0, 'refund_percent' => 100, 'hours' => $hoursDiff];
        } elseif ($hoursDiff >= 12) {
            return ['fee_percent' => 50, 'refund_percent' => 50, 'hours' => $hoursDiff];
        } else {
            return ['fee_percent' => 100, 'refund_percent' => 0, 'hours' => $hoursDiff];
        }
    }
}
