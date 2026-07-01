<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchPost extends Model
{
    protected $fillable = [
        'user_id', 'sport_id', 'title', 'play_date', 
        'play_time', 'location', 'skill_level', 
        'needed_players', 'contact_info', 'description', 'status', 'total_players'
    ];

    protected $casts = [
        'play_date' => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function sport(): BelongsTo { return $this->belongsTo(Sport::class); }

    // Lấy tất cả người xin tham gia
    public function participants(): HasMany {
        return $this->hasMany(MatchParticipant::class);
    }

    // Lấy những người ĐÃ ĐƯỢC DUYỆT
    public function approvedParticipants(): HasMany {
        return $this->hasMany(MatchParticipant::class)->where('status', 'approved');
    }
}