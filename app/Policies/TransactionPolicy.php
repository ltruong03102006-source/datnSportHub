<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Cho phép người dùng đã đăng nhập xem danh sách giao dịch của chính họ.
     */
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Chỉ cho phép admin xem toàn bộ hoặc chủ giao dịch xem giao dịch của mình.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        if (strtolower($user->role ?? '') === 'admin') {
            return true;
        }

        return $user->id === $transaction->user_id;
    }
}
