<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueTransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'from_owner_id',
        'to_owner_id',
        'status',
        'admin_note',
    ];

    /**
     * Lấy thông tin Cơ sở sân đang được chuyển nhượng
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Lấy thông tin Chủ sân cũ (Người gửi yêu cầu)
     */
    public function fromOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_owner_id');
    }

    /**
     * Lấy thông tin Chủ sân mới (Người nhận chuyển nhượng)
     */
    public function toOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_owner_id');
    }
}