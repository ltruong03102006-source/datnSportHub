<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['time_slot_id', 'price', 'price_type', 'day_of_week'])]
class SlotPrice extends Model
{
    use HasFactory;

    protected $table = 'slot_prices';

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
