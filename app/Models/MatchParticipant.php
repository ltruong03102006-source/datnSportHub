<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_post_id', 
        'user_id', 
        'status'
    ];

    // Liên kết ngược về Bài đăng
    public function matchPost(): BelongsTo
    {
        return $this->belongsTo(MatchPost::class);
    }

    // Liên kết để lấy thông tin Người xin tham gia (Avatar, Tên, SĐT...)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}