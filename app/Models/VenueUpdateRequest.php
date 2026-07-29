<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueUpdateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'requested_data',
        'status',
        'admin_note'
    ];

    // Tự động ép kiểu (cast) cột JSON thành Array
    protected $casts = [
        'requested_data' => 'array',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}