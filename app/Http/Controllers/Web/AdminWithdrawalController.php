<?php

namespace App\Http\Controllers\Web;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = WithdrawalRequest::query()
            ->with(['owner', 'wallet'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search): void {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('owner', function ($ownerQuery) use ($search): void {
                        $ownerQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $withdrawals = $query->paginate(15)->withQueryString();

        $totalPendingAmount = WithdrawalRequest::query()
            ->where('status', 'pending')
            ->sum('amount');

        $pendingCount = WithdrawalRequest::query()
            ->where('status', 'pending')
            ->count();

        $approvedCount = WithdrawalRequest::query()
            ->where('status', 'approved')
            ->count();

        $rejectedCount = WithdrawalRequest::query()
            ->where('status', 'rejected')
            ->count();

        return view('admin.withdrawals.index', compact(
            'withdrawals',
            'status',
            'search',
            'totalPendingAmount',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function show(WithdrawalRequest $withdrawal): View
    {
        $withdrawal->load(['owner', 'wallet', 'approver']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(WithdrawalRequest $withdrawal, WalletService $walletService): RedirectResponse
    {
        DB::transaction(function () use ($withdrawal, $walletService): void {
            $lockedWithdrawal = WithdrawalRequest::query()
                ->whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->statusValue($lockedWithdrawal) !== 'pending') {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Yêu cầu rút tiền này đã được xử lý.',
                ]);
            }

            $wallet = Wallet::query()
                ->whereKey($lockedWithdrawal->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $wallet->balance < (float) $lockedWithdrawal->amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Số dư ví không đủ để duyệt yêu cầu rút tiền.',
                ]);
            }

            $walletService->processTransaction(
                wallet: $wallet,
                type: TransactionType::WITHDRAWAL_DEBIT,
                amount: (float) $lockedWithdrawal->amount,
                description: 'Admin duyệt yêu cầu rút tiền: ' . $lockedWithdrawal->code,
                withdrawalRequestId: $lockedWithdrawal->id,
                metadata: [
                    'reference_type' => 'withdrawal_request',
                    'reference_id' => $lockedWithdrawal->id,
                    'withdrawal_code' => $lockedWithdrawal->code,
                    'approved_by' => auth()->id(),
                ],
                reference: $lockedWithdrawal->code
            );

            $lockedWithdrawal->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            if (class_exists(\App\Services\DebtService::class)) {
                app(\App\Services\DebtService::class)->syncOwnerDebtStatus((int) $lockedWithdrawal->owner_id);
            }
        });

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Đã duyệt yêu cầu rút tiền và trừ số dư ví chủ sân.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'admin_note.max' => 'Ghi chú admin không được vượt quá 1000 ký tự.',
        ]);

        if ($this->statusValue($withdrawal) !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $withdrawal->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? null,
            'rejected_at' => now(),
        ]);

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Đã từ chối yêu cầu rút tiền.');
    }

    private function statusValue(WithdrawalRequest $withdrawal): string
    {
        return $withdrawal->status instanceof \BackedEnum
            ? $withdrawal->status->value
            : (string) $withdrawal->status;
    }
}
