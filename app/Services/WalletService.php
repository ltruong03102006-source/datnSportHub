<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Xử lý biến động số dư ví (Cộng hoặc Trừ tiền) an toàn tuyệt đối với Database Row Lock.
     */
    public function processTransaction(
        Wallet $wallet,
        TransactionType $type,
        float $amount,
        string $description,
        ?int $bookingId = null,
        ?int $withdrawalRequestId = null,
        ?array $metadata = null,
        ?string $reference = null
    ): WalletTransaction {
        // Đảm bảo amount truyền vào luôn là số dương. Việc cộng hay trừ do Type quyết định.
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Số tiền giao dịch phải lớn hơn 0.');
        }

        return DB::transaction(function () use ($wallet, $type, $amount, $description, $bookingId, $withdrawalRequestId, $metadata, $reference) {
            if ($bookingId) {
                $existing = WalletTransaction::query()
                    ->where('booking_id', $bookingId)
                    ->where('type', $type->value)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            if ($withdrawalRequestId) {
                $existing = WalletTransaction::query()
                    ->where('withdrawal_request_id', $withdrawalRequestId)
                    ->where('type', $type->value)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            // 1. LOCK ROW: Khóa dòng dữ liệu ví này lại, các request khác chạm vào ví này phải xếp hàng chờ
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $balanceBefore = $lockedWallet->balance;
            
            // 2. Xác định là giao dịch TĂNG hay GIẢM tiền
            $isAddition = in_array($type, [
                TransactionType::BOOKING_INCOME, 
                TransactionType::BOOKING_ONLINE_CREDIT,
                TransactionType::TOPUP, 
                TransactionType::TOPUP_CREDIT,
                TransactionType::REFUND
            ]);

            if ($isAddition) {
                $lockedWallet->balance += $amount;
            } else {
                $lockedWallet->balance -= $amount;
            }

            // 3. Cập nhật số dư mới
            $lockedWallet->save();

            // 4. Lưu lại lịch sử giao dịch (Audit Trail)
            $transaction = WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'booking_id' => $bookingId,
                'withdrawal_request_id' => $withdrawalRequestId,
                'reference' => $reference ?: $this->generateReferenceNumber(),
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $lockedWallet->balance,
                'description' => $description,
                'metadata' => $metadata,
            ]);

            if (! $isAddition && class_exists(DebtService::class)) {
                app(DebtService::class)->suspendOwnerIfDebtLimitExceeded((int) $lockedWallet->owner_id);
            }

            if ($isAddition && class_exists(DebtService::class)) {
                app(DebtService::class)->reactivateOwnerVenuesIfDebtRepaid((int) $lockedWallet->owner_id);
            }

            if (class_exists(DebtService::class)) {
                app(DebtService::class)->syncDebtWarningStatus((int) $lockedWallet->owner_id);
            }

            return $transaction;
        });
    }

    /**
     * Hàm sinh mã giao dịch duy nhất. VD: TXN-20260722-ABC12
     */
    private function generateReferenceNumber(): string
    {
        $prefix = 'TXN';
        $date = date('Ymd'); // Ví dụ: 20260722
        
        do {
            $random = strtoupper(Str::random(5));
            $reference = "{$prefix}-{$date}-{$random}";
        } while (WalletTransaction::where('reference', $reference)->exists());
        
        return $reference;
    }
}
