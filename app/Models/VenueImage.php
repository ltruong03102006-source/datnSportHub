<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueImage extends Model
{
    protected $fillable = ['venue_id', 'image_path'];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
