<?php

namespace App\Traits;

use App\Models\Wallet;

trait HasWallet
{
    /**
     * Lấy mối quan hệ wallet
     */
    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class, 'owner_id');
    }

    /**
     * Lấy ví của User, nếu chưa có thì tự động tạo mới (Lazy Loading)
     */
    public function getOrCreateWallet(): Wallet
    {
        return Wallet::firstOrCreate(
            ['owner_id' => $this->id],
            [
                'balance' => 0,
                'available_balance' => 0,
                'pending_balance' => 0,
                'status' => 'active',
                'currency' => 'VND'
            ]
        );
    }

    /**
     * Accessor lấy số dư ví của User
     */
    public function getWalletBalanceAttribute(): float
    {
        return (float) ($this->getOrCreateWallet()->balance ?? 0);
    }
}
