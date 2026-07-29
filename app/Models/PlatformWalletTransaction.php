<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformWalletTransaction extends Model
{
    public const TYPE_CUSTOMER_ONLINE_PAYMENT_IN = 'customer_online_payment_in';
    public const TYPE_OWNER_TOPUP_IN = 'owner_topup_in';
    public const TYPE_OWNER_WITHDRAWAL_OUT = 'owner_withdrawal_out';
    public const TYPE_CUSTOMER_REFUND_OUT = 'customer_refund_out';
    public const TYPE_MANUAL_CREDIT = 'manual_credit';
    public const TYPE_MANUAL_DEBIT = 'manual_debit';

    protected $fillable = [
        'platform_wallet_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
        'performed_by',
        'occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function platformWallet(): BelongsTo
    {
        return $this->belongsTo(PlatformWallet::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
