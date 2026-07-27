<?php

namespace App\Services;

use App\Models\PlatformWallet;
use App\Models\PlatformWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlatformWalletService
{
    public const DEFAULT_WALLET_CODE = 'main';

    public function getDefaultWallet(): PlatformWallet
    {
        return PlatformWallet::firstOrCreate(
            ['code' => self::DEFAULT_WALLET_CODE],
            [
                'name' => 'SportHub Platform Wallet',
                'balance' => 0,
                'currency' => 'VND',
                'status' => 'active',
            ]
        );
    }

    public function credit(
        float|int|string $amount,
        string $type,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reference = null,
        ?int $performedBy = null,
        array $metadata = []
    ): PlatformWalletTransaction {
        $amount = abs((float) $amount);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền ghi nhận vào ví nền tảng phải lớn hơn 0.',
            ]);
        }

        return $this->record(
            signedAmount: $amount,
            type: $type,
            description: $description,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reference: $reference,
            performedBy: $performedBy,
            metadata: $metadata
        );
    }

    public function debit(
        float|int|string $amount,
        string $type,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reference = null,
        ?int $performedBy = null,
        array $metadata = [],
        bool $allowNegative = false
    ): PlatformWalletTransaction {
        $amount = abs((float) $amount);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền ghi nhận ra khỏi ví nền tảng phải lớn hơn 0.',
            ]);
        }

        return $this->record(
            signedAmount: -$amount,
            type: $type,
            description: $description,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reference: $reference,
            performedBy: $performedBy,
            metadata: $metadata,
            allowNegative: $allowNegative
        );
    }

    protected function record(
        float $signedAmount,
        string $type,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reference = null,
        ?int $performedBy = null,
        array $metadata = [],
        bool $allowNegative = false
    ): PlatformWalletTransaction {
        return DB::transaction(function () use (
            $signedAmount,
            $type,
            $description,
            $referenceType,
            $referenceId,
            $reference,
            $performedBy,
            $metadata,
            $allowNegative
        ) {
            if ($referenceType && $referenceId) {
                $existing = PlatformWalletTransaction::query()
                    ->where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->where('type', $type)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $wallet = $this->getDefaultWallet();

            $wallet = PlatformWallet::query()
                ->whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $signedAmount;

            if (! $allowNegative && $balanceAfter < 0) {
                throw ValidationException::withMessages([
                    'balance' => 'Số dư ví nền tảng không đủ để thực hiện giao dịch.',
                ]);
            }

            $wallet->update([
                'balance' => $balanceAfter,
            ]);

            return PlatformWalletTransaction::create([
                'platform_wallet_id' => $wallet->id,
                'type' => $type,
                'amount' => $signedAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'metadata' => $metadata ?: null,
                'performed_by' => $performedBy,
                'occurred_at' => now(),
            ]);
        });
    }
}
