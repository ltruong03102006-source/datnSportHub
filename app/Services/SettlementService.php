<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\TransactionType;
use App\Enums\SettlementStatus;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function __construct(
        protected CommissionService $commissionService,
        protected WalletService $walletService
    ) {}

    /**
     * Thực hiện đối soát cho 1 Booking
     */
    public function process(Booking $booking): void
    {
        // 1. Nếu đã đối soát rồi thì bỏ qua
        if ($booking->settlement_status === SettlementStatus::SETTLED) {
            return;
        }

        DB::transaction(function () use ($booking) {
            // Đổi trạng thái sang đang xử lý
            $booking->update(['settlement_status' => SettlementStatus::PROCESSING]);

            // 2. Lấy thông tin sân và ví chủ sân
            $venue = $booking->court->venue; 
            $owner = $venue->owner;
            $wallet = $owner->getOrCreateWallet();

            // 3. Tính toán hoa hồng
            $commissionRate = $this->commissionService->getApplicableRate($venue);
            $platformFee = $this->commissionService->calculatePlatformFee($booking->total_price, $commissionRate);
            $ownerEarnings = $this->commissionService->calculateOwnerEarnings($booking->total_price, $platformFee);

            // 4. Quyết định Dòng tiền dựa trên Phương thức thanh toán
            // Giả sử cột payment_method của bạn lưu 'online' (VNPay) hoặc 'cash' (Tiền mặt)
            $paymentMethod = $booking->payment_method ?? 'online'; 

            if ($paymentMethod === 'online') {
                // Admin đã cầm tiền -> Trả phần Owner Earnings cho chủ sân
                $this->walletService->processTransaction(
                    wallet: $wallet,
                    type: TransactionType::BOOKING_INCOME,
                    amount: $ownerEarnings,
                    description: "Thu nhập từ lịch đặt sân #{$booking->id}",
                    bookingId: $booking->id
                );
            } else {
                // Chủ sân đã thu 100% tiền mặt -> Trừ phí hoa hồng (Platform Fee) vào ví chủ sân
                if ($platformFee > 0) {
                    $this->walletService->processTransaction(
                        wallet: $wallet,
                        type: TransactionType::COMMISSION_FEE,
                        amount: $platformFee,
                        description: "Phí hoa hồng (Thu hộ) từ lịch đặt sân #{$booking->id}",
                        bookingId: $booking->id
                    );
                }
            }

            // 5. Cập nhật Booking hoàn tất đối soát
            $booking->update([
                'commission_rate' => $commissionRate,
                'platform_fee' => $platformFee,
                'owner_earnings' => $ownerEarnings,
                'settlement_status' => SettlementStatus::SETTLED,
                'settled_at' => now(),
            ]);
        });
    }
}