<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PlatformWallet;
use App\Models\PlatformWalletTransaction;
use App\Models\TopupTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\DebtService;
use App\Services\PlatformWalletService;
use App\Gateways\SettlementGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminFinanceDashboardController extends Controller
{
    public function index(Request $request, DebtService $debtService): View
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $ownerId = $request->filled('owner_id') ? (int) $request->query('owner_id') : null;
        $ownerSearch = trim((string) $request->query('owner_search', ''));
        $ownerStatus = (string) $request->query('owner_status', 'all');
        $ownerSort = (string) $request->query('owner_sort', 'debt_desc');
        $commissionChart = $this->buildCommissionRevenueChart($dateFrom, $dateTo, $ownerId);

        $ownerOptions = User::query()
            ->where('role', 'owner')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $selectedOwner = $ownerId
            ? $ownerOptions->firstWhere('id', $ownerId)
            : null;

        $bookingQuery = Booking::query();

        $bookingDateColumn = Schema::hasColumn('bookings', 'settled_at')
            ? 'settled_at'
            : 'created_at';

        $this->applyDateRange($bookingQuery, $bookingDateColumn, $dateFrom, $dateTo);
        $this->applyOwnerFilterToBookingQuery($bookingQuery, $ownerId);

        if (Schema::hasColumn('bookings', 'settlement_status')) {
            $bookingQuery->where('settlement_status', 'settled');
        } else {
            $bookingQuery->where('status', 'completed');

            if (Schema::hasColumn('bookings', 'payment_status')) {
                $bookingQuery->whereIn('payment_status', ['paid', 'success', 'completed']);
            }
        }

        $gmvColumn = Schema::hasColumn('bookings', 'gross_amount')
            ? 'gross_amount'
            : 'total_price';

        $gmv = Schema::hasColumn('bookings', $gmvColumn)
            ? (clone $bookingQuery)->sum($gmvColumn)
            : 0;

        $settledBookingCount = (clone $bookingQuery)->count();

        $walletTransactionQuery = WalletTransaction::query();
        $this->applyDateRange($walletTransactionQuery, 'created_at', $dateFrom, $dateTo);

        if ($ownerId) {
            $walletTransactionQuery->whereHas('wallet', fn ($query) => $query->where('owner_id', $ownerId));
        }

        $commissionColumn = Schema::hasColumn('bookings', 'platform_fee')
            ? 'platform_fee'
            : (Schema::hasColumn('bookings', 'commission_amount') ? 'commission_amount' : null);

        $ownerEarningsColumn = Schema::hasColumn('bookings', 'owner_earnings')
            ? 'owner_earnings'
            : (Schema::hasColumn('bookings', 'owner_amount') ? 'owner_amount' : null);

        $platformRevenue = $commissionColumn
            ? (clone $bookingQuery)->sum($commissionColumn)
            : abs((float) (clone $walletTransactionQuery)->whereIn('type', ['commission_fee', 'commission_cod_debit'])->sum('amount'));

        $ownerPayout = $ownerEarningsColumn
            ? (clone $bookingQuery)->sum($ownerEarningsColumn)
            : (clone $walletTransactionQuery)->whereIn('type', ['booking_income', 'booking_online_credit'])->sum('amount');

        if (! $ownerEarningsColumn && (float) $ownerPayout <= 0 && (float) $gmv > 0) {
            $ownerPayout = max(0, (float) $gmv - (float) $platformRevenue);
        }

        // TÍNH TOÁN CHI TIẾT BOOKING LẺ VS ĐẶT GÓI
        $singleBookingQuery = (clone $bookingQuery)->where(function ($q) {
            $q->whereNull('booking_package_id');
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $q->where('payment_method', '!=', 'package');
            }
        });

        $packageBookingQuery = (clone $bookingQuery)->where(function ($q) {
            $q->whereNotNull('booking_package_id');
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $q->orWhere('payment_method', 'package');
            }
        });

        $packageModelQuery = Schema::hasTable('booking_packages') ? \App\Models\BookingPackage::query() : null;
        if ($packageModelQuery) {
            if ($ownerId) {
                $packageModelQuery->whereHas('venue', fn($q) => $q->where('owner_id', $ownerId));
            }
            $this->applyDateRange($packageModelQuery, 'created_at', $dateFrom, $dateTo);
        }

        $activePackageCount = $packageModelQuery ? (clone $packageModelQuery)->whereIn('status', ['active', 'paused'])->count() : 0;
        $completedPackageCount = $packageModelQuery ? (clone $packageModelQuery)->where('status', 'completed')->count() : 0;
        $totalPackageCount = $packageModelQuery ? (clone $packageModelQuery)->whereIn('status', ['active', 'completed', 'paused'])->count() : 0;
        $totalPackageSalesAmount = $packageModelQuery ? (float) (clone $packageModelQuery)->whereIn('status', ['active', 'completed', 'paused'])->sum('final_amount') : 0;

        $singleGmv = Schema::hasColumn('bookings', $gmvColumn) ? (float) (clone $singleBookingQuery)->sum($gmvColumn) : 0;
        $rawPackageGmv = Schema::hasColumn('bookings', $gmvColumn) ? (float) (clone $packageBookingQuery)->sum($gmvColumn) : 0;
        $packageGmv = $totalPackageSalesAmount > 0 ? $totalPackageSalesAmount : $rawPackageGmv;
        $gmv = $singleGmv + $packageGmv;

        $singleCommission = $commissionColumn ? (float) (clone $singleBookingQuery)->sum($commissionColumn) : 0;
        $packageCommission = $commissionColumn ? (float) (clone $packageBookingQuery)->sum($commissionColumn) : 0;

        $cancelledBookingQuery = Booking::query()
            ->where('status', 'cancelled')
            ->where('cancellation_fee', '>', 0);
        $this->applyDateRange($cancelledBookingQuery, 'created_at', $dateFrom, $dateTo);
        $this->applyOwnerFilterToBookingQuery($cancelledBookingQuery, $ownerId);

        $cancellationCommission = $commissionColumn ? (float) (clone $cancelledBookingQuery)->sum($commissionColumn) : 0;

        $singleSettledCount = (clone $singleBookingQuery)->count();
        $packageSettledCount = (clone $packageBookingQuery)->count();

        // TÍNH TOÁN THEO PHƯƠNG THỨC THANH TOÁN
        $onlinePaymentGmv = Schema::hasColumn('bookings', 'payment_method')
            ? (float) (clone $bookingQuery)->whereIn('payment_method', ['vnpay', 'online', 'bank_transfer', 'momo', 'zalopay'])->sum($gmvColumn)
            : 0;

        $codPaymentGmv = Schema::hasColumn('bookings', 'payment_method')
            ? (float) (clone $bookingQuery)->whereIn('payment_method', ['cod', 'cash', 'offline'])->sum($gmvColumn)
            : 0;

        $walletPaymentGmv = Schema::hasColumn('bookings', 'payment_method')
            ? (float) (clone $bookingQuery)->where('payment_method', 'wallet')->sum($gmvColumn)
            : 0;

        $packagePaymentGmv = Schema::hasColumn('bookings', 'payment_method')
            ? (float) (clone $bookingQuery)->where('payment_method', 'package')->sum($gmvColumn)
            : 0;

        $unsettledPackageQuery = \App\Models\Booking::query();
        if (Schema::hasColumn('bookings', 'payment_status')) {
            $unsettledPackageQuery->whereIn('payment_status', ['paid', 'success', 'completed']);
        }
        $unsettledPackageQuery->where(function ($q) {
            $q->whereNotNull('booking_package_id');
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $q->orWhere('payment_method', 'package');
            }
        });
        if (Schema::hasColumn('bookings', 'settlement_status')) {
            $unsettledPackageQuery->where('settlement_status', '!=', 'settled');
        } else {
            $unsettledPackageQuery->whereNotIn('status', ['completed', 'cancelled']); 
        }
        if ($ownerId) {
            $unsettledPackageQuery->whereHas('court.venue', fn($q) => $q->where('owner_id', $ownerId));
        }
        $unsettledPackageFunds = Schema::hasColumn('bookings', $gmvColumn) ? (float) $unsettledPackageQuery->sum($gmvColumn) : 0;

        $wallets = Wallet::query()
            ->with('owner')
            ->whereHas('owner', fn ($query) => $query->where('role', 'owner'))
            ->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId))
            ->get();

        $totalWalletBalance = $wallets
            ->filter(fn (Wallet $wallet): bool => (float) $wallet->balance > 0)
            ->sum(fn (Wallet $wallet): float => (float) $wallet->balance);

        $totalDebt = $wallets
            ->filter(fn (Wallet $wallet): bool => (float) $wallet->balance < 0)
            ->sum(fn (Wallet $wallet): float => abs((float) $wallet->balance));

        $ownersInDebt = $wallets
            ->filter(fn (Wallet $wallet): bool => (float) $wallet->balance < 0)
            ->count();

        $topDebtOwners = $wallets
            ->filter(fn (Wallet $wallet): bool => (float) $wallet->balance < 0 && $wallet->owner)
            ->sortBy('balance')
            ->take(5)
            ->map(fn (Wallet $wallet): array => [
                'wallet' => $wallet,
                'owner' => $wallet->owner,
                'summary' => $debtService->getOwnerDebtSummary($wallet->owner->id),
            ])
            ->values();

        $ownerWalletQuery = Wallet::query()
            ->with('owner')
            ->whereHas('owner', function ($query) use ($ownerSearch, $ownerId) {
                $query->where('role', 'owner');

                if ($ownerId) {
                    $query->whereKey($ownerId);
                }

                if ($ownerSearch !== '') {
                    $query->where(function ($searchQuery) use ($ownerSearch) {
                        $searchQuery->where('name', 'like', '%' . $ownerSearch . '%')
                            ->orWhere('email', 'like', '%' . $ownerSearch . '%')
                            ->orWhere('phone', 'like', '%' . $ownerSearch . '%');
                    });
                }
            });

        $debtLimitExpression = 'COALESCE(NULLIF(wallets.credit_limit, 0), ' . DebtService::DEFAULT_DEBT_LIMIT . ')';

        match ($ownerStatus) {
            'good' => $ownerWalletQuery->where('wallets.balance', '>=', 0),
            'in_debt' => $ownerWalletQuery->where('wallets.balance', '<', 0)
                ->whereRaw('(ABS(wallets.balance) / ' . $debtLimitExpression . ') * 100 < ?', [DebtService::WARNING_THRESHOLD_PERCENT]),
            'warning' => $ownerWalletQuery->where('wallets.balance', '<', 0)
                ->whereRaw('(ABS(wallets.balance) / ' . $debtLimitExpression . ') * 100 >= ?', [DebtService::WARNING_THRESHOLD_PERCENT])
                ->whereRaw('ABS(wallets.balance) < ' . $debtLimitExpression),
            'over_limit' => $ownerWalletQuery->where('wallets.balance', '<', 0)
                ->whereRaw('ABS(wallets.balance) >= ' . $debtLimitExpression),
            default => null,
        };

        match ($ownerSort) {
            'balance_asc' => $ownerWalletQuery->orderBy('wallets.balance'),
            'balance_desc' => $ownerWalletQuery->orderByDesc('wallets.balance'),
            'newest' => $ownerWalletQuery->orderByDesc('wallets.created_at'),
            'owner_name' => $ownerWalletQuery
                ->join('users as wallet_owners', 'wallet_owners.id', '=', 'wallets.owner_id')
                ->select('wallets.*')
                ->orderBy('wallet_owners.name'),
            default => $ownerWalletQuery->orderBy('wallets.balance'),
        };

        $ownerWalletRows = $ownerWalletQuery
            ->paginate(10, ['*'], 'owner_wallets_page')
            ->withQueryString();

        $ownerWalletRows->setCollection(
            $ownerWalletRows->getCollection()
                ->map(fn (Wallet $wallet): array => [
                    'wallet' => $wallet,
                    'owner' => $wallet->owner,
                    'summary' => $wallet->owner
                        ? $debtService->getOwnerDebtSummary($wallet->owner->id)
                        : null,
                ])
        );

        $pendingWithdrawals = WithdrawalRequest::query()
            ->where('status', 'pending')
            ->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId))
            ->sum('amount');

        $withdrawalDateColumn = Schema::hasColumn('withdrawal_requests', 'approved_at')
            ? 'approved_at'
            : 'created_at';

        $approvedWithdrawalsQuery = WithdrawalRequest::query()
            ->where('status', 'approved');
        $approvedWithdrawalsQuery->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId));
        $this->applyDateRange($approvedWithdrawalsQuery, $withdrawalDateColumn, $dateFrom, $dateTo);
        $approvedWithdrawals = $approvedWithdrawalsQuery->sum('amount');

        $topupDateColumn = Schema::hasColumn('topup_transactions', 'paid_at')
            ? 'paid_at'
            : 'created_at';

        $topupQuery = TopupTransaction::query()
            ->where('status', 'success');
        $topupQuery->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId));
        $this->applyDateRange($topupQuery, $topupDateColumn, $dateFrom, $dateTo);
        $successfulTopups = $topupQuery->sum('amount');

        $codCommissionDebt = abs((float) (clone $walletTransactionQuery)
            ->whereIn('type', ['commission_fee', 'commission_cod_debit'])
            ->sum('amount'));

        $onlineBookingCredit = (clone $walletTransactionQuery)
            ->whereIn('type', ['booking_income', 'booking_online_credit'])
            ->sum('amount');

        $platformWallet = Schema::hasTable('platform_wallets')
            ? PlatformWallet::query()->where('code', 'main')->first()
            : null;

        $platformWalletBalance = $platformWallet
            ? (float) $platformWallet->balance
            : 0;

        $platformTransactionQuery = Schema::hasTable('platform_wallet_transactions')
            ? PlatformWalletTransaction::query()
            : null;

        if ($platformTransactionQuery) {
            $this->applyDateRange($platformTransactionQuery, 'created_at', $dateFrom, $dateTo);
            $this->applyOwnerFilterToPlatformTransactionQuery($platformTransactionQuery, $ownerId);
        }

        $platformCashIn = $platformTransactionQuery
            ? (float) (clone $platformTransactionQuery)->where('amount', '>', 0)->sum('amount')
            : 0;

        $platformCashOut = $platformTransactionQuery
            ? abs((float) (clone $platformTransactionQuery)->where('amount', '<', 0)->sum('amount'))
            : 0;

        $platformNetCashFlow = $platformCashIn - $platformCashOut;

        $customerOnlinePaymentIn = $platformTransactionQuery
            ? (float) (clone $platformTransactionQuery)->whereIn('type', ['customer_online_payment_in', 'booking_payment'])->sum('amount')
            : 0;

        $ownerTopupIn = $platformTransactionQuery
            ? (float) (clone $platformTransactionQuery)->where('type', 'owner_topup_in')->sum('amount')
            : 0;

        $ownerWithdrawalOut = $platformTransactionQuery
            ? abs((float) (clone $platformTransactionQuery)->where('type', 'owner_withdrawal_out')->sum('amount'))
            : 0;

        $adminRevenueWithdrawal = $platformTransactionQuery
            ? abs((float) (clone $platformTransactionQuery)->where('type', 'admin_revenue_withdrawal')->sum('amount'))
            : 0;

        $customerRefundOut = $platformTransactionQuery
            ? abs((float) (clone $platformTransactionQuery)->where('type', 'customer_refund_out')->sum('amount'))
            : 0;

        if (Schema::hasTable('platform_wallet_transactions')) {
            $latestPlatformTransactionQuery = PlatformWalletTransaction::query()
                ->with(['platformWallet', 'performer']);
            $this->applyOwnerFilterToPlatformTransactionQuery($latestPlatformTransactionQuery, $ownerId);

            $latestPlatformTransactions = $latestPlatformTransactionQuery
                ->latest()
                ->limit(10)
                ->get();
        } else {
            $latestPlatformTransactions = collect();
        }

        $latestTransactions = WalletTransaction::query()
            ->with(['wallet.owner'])
            ->when($ownerId, fn ($query) => $query->whereHas('wallet', fn ($walletQuery) => $walletQuery->where('owner_id', $ownerId)))
            ->latest()
            ->limit(10)
            ->get();

        // TÍNH TOÁN AN TOÀN TÀI CHÍNH (SOLVENCY)
        $ownerWalletLiability = (float) Wallet::query()
            ->where('balance', '>', 0)
            ->whereHas('owner', fn ($q) => $q->where('role', 'owner'))
            ->when($ownerId, fn ($q) => $q->where('owner_id', $ownerId))
            ->sum('balance');

        $customerWalletsQuery = Wallet::query()
            ->where('balance', '>', 0)
            ->whereHas('owner', fn ($q) => $q->where('role', '!=', 'owner'));
        $customerWalletLiability = (float) (clone $customerWalletsQuery)->sum('balance');
        $customerWalletCount = (clone $customerWalletsQuery)->count();

        $totalSystemLiability = $ownerWalletLiability + ($ownerId ? 0 : $customerWalletLiability);

        $unsettledQuery = Booking::query();
        if (Schema::hasColumn('bookings', 'payment_status')) {
            $unsettledQuery->whereIn('payment_status', ['paid', 'success', 'completed']);
        }
        $unsettledQuery->where(function ($q) {
            $q->whereNull('booking_package_id');
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $q->where('payment_method', '!=', 'package');
            }
        });
        $unsettledQuery->whereNotIn('status', ['completed', 'cancelled', 'rejected']);
        if (Schema::hasColumn('bookings', 'settlement_status')) {
            $unsettledQuery->where('settlement_status', '!=', 'settled');
        }
        $this->applyOwnerFilterToBookingQuery($unsettledQuery, $ownerId);
        $amountCol = Schema::hasColumn('bookings', 'gross_amount') ? 'gross_amount' : 'total_price';
        $unsettledFunds = (float) $unsettledQuery->sum($amountCol);

        $safeToWithdraw = $platformWalletBalance - $totalSystemLiability - $unsettledFunds;
        $displaySafeAmount = max(0, $safeToWithdraw);

        $platformRevenue = $singleCommission + $packageCommission + $cancellationCommission;

        $effectivePackageGmv = $packageGmv > 0 ? $packageGmv : $totalPackageSalesAmount;
        $calculatedGmv = $singleGmv + $effectivePackageGmv;
        $gmv = max($gmv, $calculatedGmv);

        return view('admin.finance.index', compact(
            'dateFrom',
            'dateTo',
            'ownerId',
            'ownerOptions',
            'selectedOwner',
            'gmv',
            'platformRevenue',
            'ownerPayout',
            'settledBookingCount',
            'totalWalletBalance',
            'totalDebt',
            'ownersInDebt',
            'pendingWithdrawals',
            'approvedWithdrawals',
            'successfulTopups',
            'codCommissionDebt',
            'onlineBookingCredit',
            'platformWallet',
            'platformWalletBalance',
            'platformCashIn',
            'platformCashOut',
            'platformNetCashFlow',
            'customerOnlinePaymentIn',
            'ownerTopupIn',
            'ownerWithdrawalOut',
            'latestPlatformTransactions',
            'ownerWalletRows',
            'ownerSearch',
            'ownerStatus',
            'ownerSort',
            'topDebtOwners',
            'latestTransactions',
            'adminRevenueWithdrawal',
            'customerRefundOut',
            'singleGmv',
            'packageGmv',
            'singleCommission',
            'packageCommission',
            'cancellationCommission',
            'activePackageCount',
            'completedPackageCount',
            'totalPackageSalesAmount',
            'unsettledPackageFunds',
            'singleSettledCount',
            'packageSettledCount',
            'totalPackageCount',
            'onlinePaymentGmv',
            'codPaymentGmv',
            'walletPaymentGmv',
            'packagePaymentGmv',
            'ownerWalletLiability',
            'customerWalletLiability',
            'customerWalletCount',
            'totalSystemLiability',
            'unsettledFunds',
            'displaySafeAmount'
        ), [
            'commissionChartLabels' => $commissionChart['labels'],
            'commissionChartOnlineData' => $commissionChart['online'],
            'commissionChartCodData' => $commissionChart['cod'],
            'commissionChartTotalData' => $commissionChart['total'],
            'commissionChartRows' => $commissionChart['rows'],
        ]);
    }

    private function applyDateRange($query, string $column, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom) {
            $query->whereDate($column, '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate($column, '<=', $dateTo);
        }
    }

    private function applyOwnerFilterToBookingQuery($query, ?int $ownerId): void
    {
        if (! $ownerId) {
            return;
        }

        $query->whereHas('court.venue', fn ($venueQuery) => $venueQuery->where('owner_id', $ownerId));
    }

    private function applyOwnerFilterToPlatformTransactionQuery($query, ?int $ownerId): void
    {
        if (! $ownerId) {
            return;
        }

        $bookingIds = Booking::query()
            ->whereHas('court.venue', fn ($venueQuery) => $venueQuery->where('owner_id', $ownerId))
            ->pluck('id');

        $topupIds = TopupTransaction::query()
            ->where('owner_id', $ownerId)
            ->pluck('id');

        $withdrawalIds = WithdrawalRequest::query()
            ->where('owner_id', $ownerId)
            ->pluck('id');

        $packageIds = Schema::hasTable('booking_packages')
            ? \App\Models\BookingPackage::query()
                ->whereHas('venue', fn ($venueQuery) => $venueQuery->where('owner_id', $ownerId))
                ->pluck('id')
            : collect();

        $query->where(function ($referenceQuery) use ($bookingIds, $topupIds, $withdrawalIds, $packageIds) {
            $referenceQuery
                ->where(function ($bookingQuery) use ($bookingIds) {
                    $bookingQuery->where('reference_type', 'booking')
                        ->whereIn('reference_id', $bookingIds);
                })
                ->orWhere(function ($topupQuery) use ($topupIds) {
                    $topupQuery->where('reference_type', 'topup_transaction')
                        ->whereIn('reference_id', $topupIds);
                })
                ->orWhere(function ($withdrawalQuery) use ($withdrawalIds) {
                    $withdrawalQuery->where('reference_type', 'withdrawal_request')
                        ->whereIn('reference_id', $withdrawalIds);
                })
                ->orWhere(function ($packageQuery) use ($packageIds) {
                    $packageQuery->where('reference_type', 'booking_package')
                        ->whereIn('reference_id', $packageIds);
                });
        });
    }

    private function buildCommissionRevenueChart(?string $dateFrom, ?string $dateTo, ?int $ownerId = null): array
    {
        $start = $dateFrom
            ? Carbon::parse($dateFrom)->startOfMonth()
            : now()->subMonths(5)->startOfMonth();

        $end = $dateTo
            ? Carbon::parse($dateTo)->endOfMonth()
            : now()->endOfMonth();

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfMonth(), $start->copy()->endOfMonth()];
        }

        $months = [];

        foreach (CarbonPeriod::create($start, '1 month', $end) as $month) {
            $months[$month->format('Y-m')] = [
                'label' => $month->format('m/Y'),
                'online_commission' => 0,
                'cod_commission' => 0,
                'total_commission' => 0,
            ];
        }

        if (
            Schema::hasTable('bookings')
            && (Schema::hasColumn('bookings', 'commission_amount') || Schema::hasColumn('bookings', 'platform_fee'))
        ) {
            $commissionColumn = Schema::hasColumn('bookings', 'commission_amount')
                ? 'commission_amount'
                : 'platform_fee';

            $dateColumn = Schema::hasColumn('bookings', 'settled_at')
                ? 'settled_at'
                : 'created_at';

            $query = Booking::query()
                ->whereNotNull($commissionColumn)
                ->whereDate($dateColumn, '>=', $start)
                ->whereDate($dateColumn, '<=', $end);

            $this->applyOwnerFilterToBookingQuery($query, $ownerId);

            if (Schema::hasColumn('bookings', 'settlement_status')) {
                $query->where('settlement_status', 'settled');
            } elseif (Schema::hasColumn('bookings', 'status')) {
                $query->whereIn('status', ['completed', 'confirmed']);
            }

            if (Schema::hasColumn('bookings', 'payment_status')) {
                $query->whereIn('payment_status', ['paid', 'completed']);
            }

            foreach ($query->get() as $booking) {
                $date = $booking->{$dateColumn};

                if (! $date) {
                    continue;
                }

                $key = Carbon::parse($date)->format('Y-m');

                if (! isset($months[$key])) {
                    continue;
                }

                $commission = (float) $booking->{$commissionColumn};
                $paymentMethod = strtolower((string) ($booking->payment_method ?? ''));

                if (in_array($paymentMethod, ['cod', 'cash', 'offline'], true)) {
                    $months[$key]['cod_commission'] += $commission;
                } else {
                    $months[$key]['online_commission'] += $commission;
                }

                $months[$key]['total_commission'] =
                    $months[$key]['online_commission'] + $months[$key]['cod_commission'];
            }
        } elseif (Schema::hasTable('wallet_transactions')) {
            $transactions = WalletTransaction::query()
                ->whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->when($ownerId, fn ($query) => $query->whereHas('wallet', fn ($walletQuery) => $walletQuery->where('owner_id', $ownerId)))
                ->where(function ($query) {
                    $query->where('type', 'commission_cod_debit')
                        ->orWhere('type', 'commission_fee')
                        ->orWhere('type', 'like', '%commission%');
                })
                ->get();

            foreach ($transactions as $transaction) {
                $key = Carbon::parse($transaction->created_at)->format('Y-m');

                if (! isset($months[$key])) {
                    continue;
                }

                $amount = abs((float) $transaction->amount);
                $type = $transaction->type instanceof \BackedEnum
                    ? $transaction->type->value
                    : (string) $transaction->type;

                if (in_array($type, ['commission_cod_debit', 'commission_fee'], true)) {
                    $months[$key]['cod_commission'] += $amount;
                } else {
                    $months[$key]['online_commission'] += $amount;
                }

                $months[$key]['total_commission'] =
                    $months[$key]['online_commission'] + $months[$key]['cod_commission'];
            }
        }

        $rows = collect($months)->map(function (array $row): array {
            return [
                'label' => $row['label'],
                'online_commission' => round((float) $row['online_commission'], 2),
                'cod_commission' => round((float) $row['cod_commission'], 2),
                'total_commission' => round((float) $row['total_commission'], 2),
            ];
        })->values();

        return [
            'labels' => $rows->pluck('label')->values(),
            'online' => $rows->pluck('online_commission')->values(),
            'cod' => $rows->pluck('cod_commission')->values(),
            'total' => $rows->pluck('total_commission')->values(),
            'rows' => $rows,
        ];
    }
    public function withdrawRevenue(Request $request, PlatformWalletService $platformWalletService, SettlementGatewayInterface $gateway)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ], [
            'amount.required' => 'Vui lòng nhập số tiền muốn rút.',
            'amount.min' => 'Số tiền rút tối thiểu là 10.000đ.'
        ]);
        
        $amount = (float) $request->amount;

        try {
            $referenceId = '';
            $bankCode = 'VCB' . rand(100000, 999999); // Sinh mã ngân hàng ảo

            DB::transaction(function () use ($amount, $platformWalletService, $gateway, &$referenceId) {
                // 1. Tổng tiền đang nằm trong ví của các Chủ sân và Khách hàng
                $totalSystemLiability = \App\Models\Wallet::where('balance', '>', 0)->sum('balance');

                // 2. TÍNH TỔNG TIỀN BOOKING ĐANG CHỜ ĐỐI SOÁT (Tiền khách đã trả nhưng chưa đá xong)
                $unsettledQuery = \App\Models\Booking::query();
                if (\Illuminate\Support\Facades\Schema::hasColumn('bookings', 'payment_status')) {
                    $unsettledQuery->where('payment_status', 'paid');
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('bookings', 'settlement_status')) {
                    $unsettledQuery->where('settlement_status', '!=', 'settled');
                } else {
                    $unsettledQuery->whereNotIn('status', ['completed', 'cancelled']); 
                }
                $amountCol = \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'gross_amount') ? 'gross_amount' : 'total_price';
                $unsettledFunds = (float) $unsettledQuery->sum($amountCol);

                // 3. TÍNH LỢI NHUẬN THỰC SỰ ĐƯỢC RÚT
                $platformWallet = $platformWalletService->getDefaultWallet();
                $safeWithdrawableAmount = $platformWallet->balance - $totalSystemLiability - $unsettledFunds;

                // 4. CHẶN NẾU RÚT QUÁ LỢI NHUẬN THỰC
                if ($amount > $safeWithdrawableAmount) {
                    throw new \Exception('Lỗi: Số dư khả dụng không đủ. Hệ thống đang tạm giữ tiền chờ đối soát!');
                }

                // (Đoạn code tạo mã Reference và trừ tiền phía dưới giữ nguyên)
                $referenceId = 'WD-' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                // ...

                // 3. Trừ tiền Ví nền tảng (Hold tiền)
                $platformWalletService->debit(
                    amount: $amount,
                    type: 'admin_revenue_withdrawal',
                    description: 'Đang xử lý lệnh rút doanh thu nền tảng (Ref: ' . $referenceId . ')',
                    referenceType: 'settlement', 
                    reference: $referenceId,
                    performedBy: Auth::id()
                );

                // 4. Bàn giao Gateway
                $gateway->processPayout($referenceId, $amount, []);
            });

            // NẾU GIAO DIỆN GỌI BẰNG JAVASCRIPT (AJAX), TRẢ VỀ DỮ LIỆU JSON CHỨ KHÔNG RELOAD TRANG
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'reference_id' => $referenceId,
                    'bank_code' => $bankCode
                ]);
            }

            return back()->with('success', 'Đã tiếp nhận lệnh rút doanh thu. Hệ thống đối soát đang xử lý!');
            
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }
    public function withdrawHistory()
    {
        // Lấy tất cả các giao dịch rút tiền và hoàn tiền của Admin
        $transactions = PlatformWalletTransaction::query()
            ->whereIn('type', ['admin_revenue_withdrawal', 'admin_revenue_refund'])
            ->with('performer')
            ->latest('created_at')
            ->paginate(20); // Phân trang 20 dòng/trang

        return view('admin.finance.withdraw_history', compact('transactions'));
    }
}
