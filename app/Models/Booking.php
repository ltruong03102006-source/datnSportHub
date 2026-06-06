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
        'note'
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
}