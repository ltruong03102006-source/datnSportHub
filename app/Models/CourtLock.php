<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtLock extends Model
{
    protected $fillable = ['court_id', 'lock_date', 'start_time', 'end_time', 'reason'];
    
    public function court() {
        return $this->belongsTo(Court::class);
    }
}