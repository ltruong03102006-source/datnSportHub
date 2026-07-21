<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'wallet_id',            // Thay cho user_id
        'amount',
        'bank_name',
        'bank_account_no',      // Giữ nguyên tên biến cũ của bạn để tương thích UI
        'bank_account_name',    // Giữ nguyên tên biến cũ của bạn để tương thích UI
        'status',
        'admin_note',
        'processed_at'          // Mới
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'status' => WithdrawalStatus::class,
        'processed_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}