<?php

namespace App\Services;

use App\Enums\SettlementStatus;
use App\Enums\TransactionType;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettlementService
{
    public function __construct(
        protected CommissionService $commissionService,
        protected WalletService $walletService
    ) {}

    public function process(Booking $booking): Booking
    {
        return $this->settleBooking($booking);
    }

    public function settleBooking(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $lockedBooking = Booking::query()
                ->with(['court.venue.owner'])
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->settlement_status === SettlementStatus::SETTLED) {
                return $lockedBooking;
            }

            if ($lockedBooking->status !== 'completed') {
                throw new RuntimeException("Booking #{$lockedBooking->id} chưa hoàn tất nên chưa thể đối soát.");
            }

            $venue = $lockedBooking->court?->venue;
            $owner = $venue?->owner;

            if (! $venue || ! $owner) {
                throw new RuntimeException("Không tìm thấy chủ sân cho booking #{$lockedBooking->id}.");
            }

            $grossAmount = $this->getGrossAmount($lockedBooking);

            if ($grossAmount <= 0) {
                throw new RuntimeException("Số tiền booking #{$lockedBooking->id} không hợp lệ.");
            }

            $commissionRate = $this->commissionService->getApplicableRate($venue);
            $commissionAmount = $this->commissionService->calculatePlatformFee($grossAmount, $commissionRate);
            $ownerAmount = $this->commissionService->calculateOwnerEarnings($grossAmount, $commissionAmount);
            $wallet = $owner->getOrCreateWallet();
            $reference = 'BOOKING-' . $lockedBooking->id;

            $lockedBooking->update(['settlement_status' => SettlementStatus::PROCESSING]);

            if ($this->isPlatformOnlinePayment($lockedBooking)) {
                if (! in_array((string) $lockedBooking->payment_status, ['paid', 'completed', 'success'], true)) {
                    throw new RuntimeException("Booking online #{$lockedBooking->id} chưa thanh toán thành công.");
                }

                if ($ownerAmount > 0) {
                    $this->walletService->processTransaction(
                        wallet: $wallet,
                        type: TransactionType::BOOKING_ONLINE_CREDIT,
                        amount: $ownerAmount,
                        description: "Nhận tiền booking online #{$lockedBooking->id}",
                        bookingId: $lockedBooking->id,
                        metadata: [
                            'payment_method' => $lockedBooking->payment_method,
                            'gross_amount' => $grossAmount,
                            'commission_amount' => $commissionAmount,
                            'owner_amount' => $ownerAmount,
                        ],
                        reference: $reference
                    );
                }
            } elseif ($this->isOwnerDirectPayment($lockedBooking)) {
                if ($commissionAmount > 0) {
                    $this->walletService->processTransaction(
                        wallet: $wallet,
                        type: TransactionType::COMMISSION_COD_DEBIT,
                        amount: $commissionAmount,
                        description: "Trừ hoa hồng booking COD #{$lockedBooking->id}",
                        bookingId: $lockedBooking->id,
                        metadata: [
                            'payment_method' => $lockedBooking->payment_method,
                            'gross_amount' => $grossAmount,
                            'commission_amount' => $commissionAmount,
                            'owner_amount' => $ownerAmount,
                        ],
                        reference: $reference
                    );
                }
            } else {
                throw new RuntimeException("Phương thức thanh toán booking #{$lockedBooking->id} không hỗ trợ đối soát.");
            }

            $lockedBooking->update([
                'commission_rate' => $commissionRate,
                'platform_fee' => $commissionAmount,
                'owner_earnings' => $ownerAmount,
                'settlement_status' => SettlementStatus::SETTLED,
                'settled_at' => $lockedBooking->settled_at ?: now(),
            ]);

            return $lockedBooking->refresh();
        });
    }

    private function getGrossAmount(Booking $booking): float
    {
        if (isset($booking->gross_amount) && (float) $booking->gross_amount > 0) {
            return (float) $booking->gross_amount;
        }

        if (isset($booking->total_price) && (float) $booking->total_price > 0) {
            return (float) $booking->total_price;
        }

        if (isset($booking->total_amount) && (float) $booking->total_amount > 0) {
            return (float) $booking->total_amount;
        }

        return 0.0;
    }

    private function isPlatformOnlinePayment(Booking $booking): bool
    {
        return in_array(strtolower((string) $booking->payment_method), [
            'vnpay',
            'online',
            'bank_transfer',
            'platform_transfer',
        ], true);
    }

    private function isOwnerDirectPayment(Booking $booking): bool
    {
        return in_array(strtolower((string) $booking->payment_method), [
            'cod',
            'cash',
            'offline',
        ], true);
    }
}
