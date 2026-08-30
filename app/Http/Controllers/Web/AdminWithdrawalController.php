<?php

namespace App\Http\Controllers\Web;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\PlatformWalletService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\WalletTransaction;

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
        $withdrawal->load('owner');

        $withdrawal->loadMissing('owner', 'wallet');

        $wallet = $withdrawal->wallet ?? $withdrawal->owner?->getOrCreateWallet();
        $walletBalance = (float) ($wallet?->balance ?? 0);

        // Resolve owner's phone: user.phone -> ownerRegistration.phone -> first venue phone
        $ownerPhone = $withdrawal->owner?->phone
            ?? $withdrawal->owner?->ownerRegistration?->phone
            ?? optional($withdrawal->owner?->venues()->first())->phone;

        return view('admin.withdrawals.show', compact('withdrawal', 'walletBalance', 'ownerPhone'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $newStatus = $data['status'];
        $adminNote = $data['admin_note'] ?? null;

        $proofImagePath = null;
        if ($newStatus === 'approved' && $request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('withdrawals', 'public');
        }

        try {
            DB::transaction(function () use ($withdrawal, $newStatus, $adminNote, $proofImagePath) {
                $withdrawal->status = $newStatus;
                $withdrawal->admin_note = $adminNote;
                if ($proofImagePath) {
                    $withdrawal->proof_image = $proofImagePath;
                }

                if ($newStatus === 'approved') {
                    $withdrawal->approved_by = \Illuminate\Support\Facades\Auth::id();
                    $withdrawal->approved_at = now();

                    $ownerWallet = $withdrawal->wallet;
                    if (! $ownerWallet) {
                        throw new \Exception('Không tìm thấy ví của chủ sân.');
                    }

                    app(WalletService::class)->processTransaction(
                        wallet: $ownerWallet,
                        type: TransactionType::WITHDRAWAL_DEBIT,
                        amount: (float) $withdrawal->amount,
                        description: 'Rút tiền chủ sân #' . $withdrawal->code,
                        withdrawalRequestId: $withdrawal->id,
                        metadata: [
                            'reference_type' => 'withdrawal_request',
                            'reference_id' => $withdrawal->id,
                        ],
                    );

                    app(\App\Services\PlatformWalletService::class)->debit(
                        amount: (float) $withdrawal->amount,
                        type: 'owner_withdrawal_out',
                        description: 'Chi tiền rút về ngân hàng cho chủ sân #' . $withdrawal->code,
                        referenceType: 'withdrawal_request',
                        referenceId: $withdrawal->id,
                        reference: $withdrawal->code,
                        performedBy: \Illuminate\Support\Facades\Auth::id(),
                        metadata: [
                            'owner_id' => $withdrawal->owner_id,
                            'bank_name' => $withdrawal->bank_name,
                            'bank_account_number' => $withdrawal->bank_account_number ?? $withdrawal->bank_account_no,
                        ]
                    );
                }

                $withdrawal->save();
            });

            // Gửi thông báo cho user
            $title = $newStatus === 'approved' ? 'Yêu cầu rút tiền thành công' : 'Yêu cầu rút tiền bị từ chối';
            $message = $newStatus === 'approved'
                ? 'Yêu cầu rút ' . number_format($withdrawal->amount) . 'đ của bạn đã được chuyển khoản. Vui lòng kiểm tra tài khoản ngân hàng.'
                : 'Yêu cầu rút ' . number_format($withdrawal->amount) . 'đ bị từ chối. Số tiền đã được hoàn lại vào ví. Lý do: ' . $adminNote;

            if ($newStatus === 'approved' && $withdrawal->proof_image) {
                $message .= ' (Minh chứng: ' . asset('storage/' . $withdrawal->proof_image) . ')';
            }

            $ownerId = $withdrawal->owner_id ?? $withdrawal->owner?->id;
            if (! $ownerId) {
                throw new \Exception('Không tìm thấy chủ sân để gửi thông báo.');
            }

            app(\App\Services\NotificationService::class)->create(
                (int) $ownerId,
                $title,
                $message,
                route('account.profile.show'),
                'withdrawal_update'
            );

            if (class_exists(\App\Services\DebtService::class)) {
                app(\App\Services\DebtService::class)->syncOwnerDebtStatus((int) $withdrawal->owner_id);
            }

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Đã xử lý yêu cầu rút tiền.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xử lý yêu cầu: ' . $e->getMessage());
        }
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
