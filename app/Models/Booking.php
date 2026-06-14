<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    // ĐÂY LÀ CHUẨN CỦA LARAVEL
    protected $fillable = [
        'court_id',
        'user_id',
        'slot_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'payment_status',
        'note',
        'cancel_reason'
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
}