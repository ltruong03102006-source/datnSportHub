<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopupTransaction extends Model
{
    protected $fillable = [
        'owner_id',
        'wallet_id',
        'code',
        'amount',
        'payment_method',
        'vnpay_txn_ref',
        'vnpay_transaction_no',
        'vnpay_response_code',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
