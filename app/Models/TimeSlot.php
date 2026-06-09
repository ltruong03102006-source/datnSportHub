<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['court_id', 'start_time', 'end_time', 'duration_minutes'])]
class TimeSlot extends Model
{
    use HasFactory;

    protected $table = 'time_slots';

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(SlotPrice::class);
    }
}
