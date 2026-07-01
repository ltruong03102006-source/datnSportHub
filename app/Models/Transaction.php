<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'transaction_code',
        'amount',
        'payment_method',
        'payment_gateway',
        'payment_status',
        'transaction_time',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_time' => 'datetime',
    ];

    /**
     * Mỗi giao dịch thuộc về một booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Mỗi giao dịch thuộc về một người dùng.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Trả về nhãn hiển thị theo trạng thái để dùng chung ở nhiều view.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'success' => 'Thành công',
            'failed' => 'Thất bại',
            'refunded' => 'Hoàn tiền',
            default => 'Đang chờ',
        };
    }

    /**
     * Trả về class badge Bootstrap tương ứng với từng trạng thái.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'success' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-secondary',
            default => 'bg-warning text-dark',
        };
    }
}
