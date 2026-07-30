<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'owner_id',
        'wallet_id',
        'code',
        'amount',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bank_account_no',
        'bank_account_name',
        'owner_note',
        'status',
        'admin_note',
        'proof_image'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => WithdrawalStatus::class,
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
