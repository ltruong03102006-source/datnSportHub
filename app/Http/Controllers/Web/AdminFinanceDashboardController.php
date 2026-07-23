<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TopupTransaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\DebtService;
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
        $commissionChart = $this->buildCommissionRevenueChart($dateFrom, $dateTo);

        $bookingQuery = Booking::query();

        $bookingDateColumn = Schema::hasColumn('bookings', 'settled_at')
            ? 'settled_at'
            : 'created_at';

        $this->applyDateRange($bookingQuery, $bookingDateColumn, $dateFrom, $dateTo);

        if (Schema::hasColumn('bookings', 'settlement_status')) {
            $bookingQuery->where('settlement_status', 'settled');
        } else {
            $bookingQuery->whereIn('status', ['completed', 'confirmed']);

            if (Schema::hasColumn('bookings', 'payment_status')) {
                $bookingQuery->where('payment_status', 'paid');
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

        $platformRevenue = Schema::hasColumn('bookings', 'commission_amount')
            ? (clone $bookingQuery)->sum('commission_amount')
            : (clone $walletTransactionQuery)->whereIn('type', ['commission_fee', 'commission_cod_debit'])->sum('amount');

        $ownerPayout = Schema::hasColumn('bookings', 'owner_amount')
            ? (clone $bookingQuery)->sum('owner_amount')
            : (clone $walletTransactionQuery)->whereIn('type', ['booking_income', 'booking_online_credit'])->sum('amount');

        if (! Schema::hasColumn('bookings', 'owner_amount') && (float) $ownerPayout <= 0 && (float) $gmv > 0) {
            $ownerPayout = max(0, (float) $gmv - (float) $platformRevenue);
        }

        $wallets = Wallet::query()
            ->with('owner')
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

        $pendingWithdrawals = WithdrawalRequest::query()
            ->where('status', 'pending')
            ->sum('amount');

        $withdrawalDateColumn = Schema::hasColumn('withdrawal_requests', 'approved_at')
            ? 'approved_at'
            : 'created_at';

        $approvedWithdrawalsQuery = WithdrawalRequest::query()
            ->where('status', 'approved');
        $this->applyDateRange($approvedWithdrawalsQuery, $withdrawalDateColumn, $dateFrom, $dateTo);
        $approvedWithdrawals = $approvedWithdrawalsQuery->sum('amount');

        $topupDateColumn = Schema::hasColumn('topup_transactions', 'paid_at')
            ? 'paid_at'
            : 'created_at';

        $topupQuery = TopupTransaction::query()
            ->where('status', 'success');
        $this->applyDateRange($topupQuery, $topupDateColumn, $dateFrom, $dateTo);
        $successfulTopups = $topupQuery->sum('amount');

        $codCommissionDebt = abs((float) (clone $walletTransactionQuery)
            ->whereIn('type', ['commission_fee', 'commission_cod_debit'])
            ->sum('amount'));

        $onlineBookingCredit = (clone $walletTransactionQuery)
            ->whereIn('type', ['booking_income', 'booking_online_credit'])
            ->sum('amount');

        $latestTransactions = WalletTransaction::query()
            ->with(['wallet.owner'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.finance.index', compact(
            'dateFrom',
            'dateTo',
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
            'topDebtOwners',
            'latestTransactions'
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

    private function buildCommissionRevenueChart(?string $dateFrom, ?string $dateTo): array
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
}
