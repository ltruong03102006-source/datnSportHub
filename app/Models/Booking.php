<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['court_id', 'user_id', 'slot_date', 'start_time', 'end_time', 'total_price', 'status', 'note'])]
class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

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
