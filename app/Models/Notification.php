<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Notification extends Model
{
    use HasFactory;

    /** Bảng notifications dùng UUID làm khóa chính. */
    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $notification) {
            // Bảng notifications cũ được tạo theo chuẩn Laravel nên hai cột
            // notifiable_* là bắt buộc. Đồng bộ chúng với người nhận của
            // notification nội bộ để cả hai cấu trúc cùng hoạt động.
            if (Schema::hasColumn('notifications', 'notifiable_type')) {
                $notification->notifiable_type ??= User::class;
                $notification->notifiable_id ??= $notification->user_id;
            }

            if (Schema::hasColumn('notifications', 'data')) {
                $notification->data ??= json_encode([
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'link' => $notification->link,
                ], JSON_UNESCAPED_UNICODE);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
