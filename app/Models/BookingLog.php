<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLog extends Model
{
    use HasFactory;

    protected $table = 'booking_logs';

    protected $fillable = [
        'booking_id',
        'changed_by',
        'old_status',
        'new_status',
        'note',
    ];

    /**
     * The model does not use default timestamps (no created_at/updated_at columns)
     * because the existing table doesn't have `updated_at`.
     */
    public $timestamps = false;
}
