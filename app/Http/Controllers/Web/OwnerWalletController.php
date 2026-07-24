<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerWalletController extends Controller
{
    public function index(Request $request, DebtService $debtService): View
    {
        $owner = Auth::user();
        $wallet = method_exists($owner, 'getOrCreateWallet')
            ? $owner->getOrCreateWallet()
            : $owner->wallet;

        $debtSummary = $debtService->getOwnerDebtSummary($owner->id);

        $type = $request->query('type');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $transactionsQuery = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->latest();

        if ($type) {
            $transactionsQuery->where('type', $type);
        }

        if ($dateFrom) {
            $transactionsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $transactionsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $transactionsQuery
            ->paginate(15)
            ->withQueryString();

        $baseTransactionQuery = WalletTransaction::query()
            ->where('wallet_id', $wallet->id);

        $totalTopup = (clone $baseTransactionQuery)
            ->whereIn('type', ['topup', 'topup_credit', 'deposit'])
            ->sum('amount');

        $totalWithdrawal = (clone $baseTransactionQuery)
            ->whereIn('type', ['withdraw', 'withdrawal_debit'])
            ->sum('amount');

        $totalBookingOnlineCredit = (clone $baseTransactionQuery)
            ->whereIn('type', ['booking_income', 'booking_online_credit'])
            ->sum('amount');

        $totalCommissionCodDebit = (clone $baseTransactionQuery)
            ->whereIn('type', ['commission_fee', 'commission_cod_debit'])
            ->sum('amount');

        $totalTransactions = (clone $baseTransactionQuery)->count();

        $pendingWithdrawAmount = WithdrawalRequest::query()
            ->where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->sum('amount');

        $transactionTypes = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->map(fn ($value) => $value instanceof \BackedEnum ? $value->value : (string) $value);

        return view('owner.wallet.index', compact(
            'wallet',
            'debtSummary',
            'transactions',
            'type',
            'dateFrom',
            'dateTo',
            'totalTopup',
            'totalWithdrawal',
            'totalBookingOnlineCredit',
            'totalCommissionCodDebit',
            'totalTransactions',
            'pendingWithdrawAmount',
            'transactionTypes'
        ));
    }
}
