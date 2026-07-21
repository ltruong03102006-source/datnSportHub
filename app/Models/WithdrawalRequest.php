<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'wallet_id', 
        'amount',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'status',
        'admin_note',
        'processed_at'
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