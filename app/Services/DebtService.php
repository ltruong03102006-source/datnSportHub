<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Venue;
use App\Models\Wallet;
use Illuminate\Support\Facades\Schema;

class DebtService
{
    public const DEFAULT_DEBT_LIMIT = 500000;
    public const WARNING_THRESHOLD_PERCENT = 80;

    public function getWalletForOwner(int $ownerId): ?Wallet
    {
        $owner = User::query()->find($ownerId);

        if (! $owner) {
            return null;
        }

        if (method_exists($owner, 'wallet')) {
            $wallet = $owner->wallet;

            if ($wallet) {
                return $wallet;
            }
        }

        if (Schema::hasColumn('wallets', 'owner_id')) {
            return Wallet::query()->where('owner_id', $ownerId)->first();
        }

        if (Schema::hasColumn('wallets', 'user_id')) {
            return Wallet::query()->where('user_id', $ownerId)->first();
        }

        return null;
    }

    public function getDebtAmount(float|int|string|null $balance): float
    {
        $balance = (float) ($balance ?? 0);

        return $balance < 0 ? abs($balance) : 0.0;
    }

    public function isInDebt(float|int|string|null $balance): bool
    {
        return (float) ($balance ?? 0) < 0;
    }

    public function getDebtLimitForOwner(int $ownerId): float
    {
        $wallet = $this->getWalletForOwner($ownerId);

        if ($wallet) {
            $walletLimit = $this->getPositiveColumnValue($wallet, ['debt_limit', 'credit_limit']);

            if ($walletLimit > 0) {
                return $walletLimit;
            }
        }

        if (class_exists(Venue::class) && Schema::hasColumn('venues', 'owner_id')) {
            $venue = Venue::query()->where('owner_id', $ownerId)->first();

            if ($venue) {
                $venueLimit = $this->getPositiveColumnValue($venue, ['debt_limit', 'credit_limit']);

                if ($venueLimit > 0) {
                    return $venueLimit;
                }
            }
        }

        if (class_exists(Setting::class)) {
            try {
                $settingValue = Setting::get('default_debt_limit');

                if ((float) $settingValue > 0) {
                    return (float) $settingValue;
                }
            } catch (\Throwable) {
                // Fall back to the service default.
            }
        }

        return self::DEFAULT_DEBT_LIMIT;
    }

    public function getDebtUsagePercent(
        float|int|string|null $balance,
        float|int|string|null $debtLimit
    ): float {
        $debtAmount = $this->getDebtAmount($balance);
        $debtLimit = (float) ($debtLimit ?? 0);

        if ($debtAmount <= 0 || $debtLimit <= 0) {
            return 0.0;
        }

        return round(($debtAmount / $debtLimit) * 100, 2);
    }

    public function isOverDebtLimit(
        float|int|string|null $balance,
        float|int|string|null $debtLimit
    ): bool {
        $debtAmount = $this->getDebtAmount($balance);
        $debtLimit = (float) ($debtLimit ?? 0);

        return $debtAmount > 0 && $debtLimit > 0 && $debtAmount >= $debtLimit;
    }

    public function isNearDebtLimit(
        float|int|string|null $balance,
        float|int|string|null $debtLimit,
        float $threshold = self::WARNING_THRESHOLD_PERCENT
    ): bool {
        $usagePercent = $this->getDebtUsagePercent($balance, $debtLimit);

        return $usagePercent >= $threshold;
    }

    public function getOwnerDebtSummary(int $ownerId): array
    {
        $wallet = $this->getWalletForOwner($ownerId);

        $balance = $wallet ? (float) $wallet->balance : 0.0;
        $debtLimit = $this->getDebtLimitForOwner($ownerId);
        $debtAmount = $this->getDebtAmount($balance);
        $usagePercent = $this->getDebtUsagePercent($balance, $debtLimit);

        $isInDebt = $this->isInDebt($balance);
        $isNearLimit = $this->isNearDebtLimit($balance, $debtLimit);
        $isOverLimit = $this->isOverDebtLimit($balance, $debtLimit);

        $status = 'good';

        if ($isOverLimit) {
            $status = 'over_limit';
        } elseif ($isNearLimit) {
            $status = 'warning';
        } elseif ($isInDebt) {
            $status = 'in_debt';
        }

        return [
            'owner_id' => $ownerId,
            'wallet_id' => $wallet?->id,
            'balance' => $balance,
            'debt_amount' => $debtAmount,
            'debt_limit' => $debtLimit,
            'usage_percent' => $usagePercent,
            'is_in_debt' => $isInDebt,
            'is_near_limit' => $isNearLimit,
            'is_over_limit' => $isOverLimit,
            'status' => $status,
        ];
    }

    public function syncOwnerStatus(int $ownerId): array
    {
        return $this->syncOwnerDebtStatus($ownerId);
    }

    public function shouldSuspendOwner(int $ownerId): bool
    {
        $summary = $this->getOwnerDebtSummary($ownerId);

        return (bool) ($summary['is_over_limit'] ?? false);
    }

    public function suspendOwnerIfDebtLimitExceeded(int $ownerId): bool
    {
        if (! $this->shouldSuspendOwner($ownerId)) {
            return false;
        }

        $this->suspendOwnerVenues($ownerId, 'debt_limit_exceeded');

        return true;
    }

    public function suspendOwnerVenues(int $ownerId, ?string $reason = null): int
    {
        if (! Schema::hasColumn('venues', 'owner_id') || ! Schema::hasColumn('venues', 'status')) {
            return 0;
        }

        $query = Venue::query()
            ->where('owner_id', $ownerId)
            ->where('status', '!=', 'suspended');

        if (Schema::hasColumn('venues', 'auto_suspend_enabled')) {
            $query->where('auto_suspend_enabled', true);
        }

        $venues = $query->get();
        $updatedCount = 0;

        foreach ($venues as $venue) {
            $updates = [
                'status' => 'suspended',
            ];

            if (
                Schema::hasColumn('venues', 'status_before_debt_suspension')
                && empty($venue->status_before_debt_suspension)
            ) {
                $updates['status_before_debt_suspension'] = $venue->status;
            }

            if (Schema::hasColumn('venues', 'is_bookable_online')) {
                $updates['is_bookable_online'] = false;
            }

            if (Schema::hasColumn('venues', 'debt_suspended_at')) {
                $updates['debt_suspended_at'] = now();
            }

            if (Schema::hasColumn('venues', 'suspended_reason')) {
                $updates['suspended_reason'] = $reason ?: 'debt_limit_exceeded';
            } elseif (Schema::hasColumn('venues', 'suspension_reason')) {
                $updates['suspension_reason'] = $reason ?: 'debt_limit_exceeded';
            }

            $venue->update($updates);
            $updatedCount++;
        }

        return $updatedCount;
    }

    public function shouldReactivateOwner(int $ownerId): bool
    {
        $summary = $this->getOwnerDebtSummary($ownerId);

        return ! (bool) ($summary['is_over_limit'] ?? false);
    }

    public function reactivateOwnerVenuesIfDebtRepaid(int $ownerId): bool
    {
        if (! $this->shouldReactivateOwner($ownerId)) {
            return false;
        }

        $this->reactivateOwnerVenues($ownerId);

        return true;
    }

    public function reactivateOwnerVenues(int $ownerId): int
    {
        if (! Schema::hasColumn('venues', 'owner_id') || ! Schema::hasColumn('venues', 'status')) {
            return 0;
        }

        $query = Venue::query()
            ->where('owner_id', $ownerId)
            ->where('status', 'suspended');

        if (Schema::hasColumn('venues', 'auto_suspend_enabled')) {
            $query->where('auto_suspend_enabled', true);
        }

        if (Schema::hasColumn('venues', 'suspended_reason')) {
            $query->where('suspended_reason', 'debt_limit_exceeded');
        } elseif (Schema::hasColumn('venues', 'debt_suspended_at')) {
            $query->whereNotNull('debt_suspended_at');
        } else {
            return 0;
        }

        $updatedCount = 0;

        foreach ($query->get() as $venue) {
            $restoreStatus = 'approved';

            if (
                Schema::hasColumn('venues', 'status_before_debt_suspension')
                && ! empty($venue->status_before_debt_suspension)
            ) {
                $restoreStatus = $venue->status_before_debt_suspension;
            }

            $updates = [
                'status' => $restoreStatus,
            ];

            if (Schema::hasColumn('venues', 'is_bookable_online')) {
                $updates['is_bookable_online'] = true;
            }

            if (Schema::hasColumn('venues', 'debt_suspended_at')) {
                $updates['debt_suspended_at'] = null;
            }

            if (Schema::hasColumn('venues', 'suspended_reason')) {
                $updates['suspended_reason'] = null;
            }

            if (Schema::hasColumn('venues', 'suspension_reason')) {
                $updates['suspension_reason'] = null;
            }

            if (Schema::hasColumn('venues', 'status_before_debt_suspension')) {
                $updates['status_before_debt_suspension'] = null;
            }

            $venue->update($updates);
            $updatedCount++;
        }

        return $updatedCount;
    }

    public function syncOwnerDebtStatus(int $ownerId): array
    {
        $summary = $this->getOwnerDebtSummary($ownerId);

        if ((bool) ($summary['is_over_limit'] ?? false)) {
            $updated = $this->suspendOwnerVenues($ownerId, 'debt_limit_exceeded');

            return [
                'action' => 'suspended',
                'updated_venues' => $updated,
                'summary' => $summary,
            ];
        }

        $updated = $this->reactivateOwnerVenues($ownerId);

        return [
            'action' => 'reactivated',
            'updated_venues' => $updated,
            'summary' => $summary,
        ];
    }

    private function getPositiveColumnValue(object $model, array $columns): float
    {
        $table = $model->getTable();

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column) && (float) ($model->{$column} ?? 0) > 0) {
                return (float) $model->{$column};
            }
        }

        return 0.0;
    }
}
