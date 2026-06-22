<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    protected $fillable = [
        'venue_id',
        'hours_before',
        'fee_percent',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}