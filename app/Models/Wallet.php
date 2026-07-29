<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'owner_id', 'balance', 'available_balance', 
        'pending_balance', 'credit_limit', 'debt_warning_sent_at',
        'debt_warning_level', 'currency', 'status'
    ];

    protected $casts = [
        'balance' => 'decimal:0',
        'available_balance' => 'decimal:0',
        'pending_balance' => 'decimal:0',
        'credit_limit' => 'decimal:0',
        'debt_warning_sent_at' => 'datetime',
        'debt_warning_level' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function topupTransactions(): HasMany
    {
        return $this->hasMany(TopupTransaction::class);
    }
}
