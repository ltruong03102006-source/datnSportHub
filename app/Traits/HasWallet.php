<?php

namespace App\Traits;

use App\Models\Wallet;

trait HasWallet
{
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
}
