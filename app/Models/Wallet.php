<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'owner_id', 'balance', 'available_balance', 
        'pending_balance', 'credit_limit', 'currency', 'status'
    ];

    protected $casts = [
        'balance' => 'decimal:0',
        'available_balance' => 'decimal:0',
        'pending_balance' => 'decimal:0',
        'credit_limit' => 'decimal:0',
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
