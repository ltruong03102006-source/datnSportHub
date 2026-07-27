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
            ? (float) (clone $platformTransactionQuery)->where('type', 'customer_online_payment_in')->sum('amount')
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
            'adminRevenueWithdrawal'
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

        $query->where(function ($referenceQuery) use ($bookingIds, $topupIds, $withdrawalIds) {
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
    public function withdrawRevenue(Request $request, PlatformWalletService $walletService, SettlementGatewayInterface $gateway)
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

            DB::transaction(function () use ($amount, $walletService, $gateway, &$referenceId) {
                $totalOwnerBalance = \App\Models\Wallet::where('balance', '>', 0)->sum('balance');
                $platformWallet = $walletService->getDefaultWallet();
                $safeWithdrawableAmount = $platformWallet->balance - $totalOwnerBalance;

                if ($amount > $safeWithdrawableAmount) {
                    throw new \Exception('Lỗi: Số tiền rút vượt quá lợi nhuận khả dụng thực tế!');
                }

                // 2. Tạo mã tham chiếu đúng chuẩn bạn yêu cầu (VD: WD-202607270001)
                $referenceId = 'WD-' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // 3. Trừ tiền Ví nền tảng (Hold tiền)
                $walletService->debit(
                    amount: $amount,
                    type: 'admin_revenue_withdrawal',
                    description: 'Đang xử lý lệnh rút doanh thu nền tảng (Ref: ' . $referenceId . ')',
                    referenceType: 'settlement', 
                    reference: $referenceId,
                    performedBy: auth()->id()
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
