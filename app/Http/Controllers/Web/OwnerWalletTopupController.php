<?php

namespace App\Http\Controllers\Web;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\PlatformWalletTransaction;
use App\Models\TopupTransaction;
use App\Services\PlatformWalletService;
use App\Services\VnpayService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerWalletTopupController extends Controller
{
    public function create(): View
    {
        $owner = auth()->user();
        $wallet = $owner->getOrCreateWallet();
        $debtAmount = $wallet->balance < 0 ? abs((float) $wallet->balance) : 0;

        return view('owner.wallet.topup', compact('wallet', 'debtAmount'));
    }

    public function store(Request $request, VnpayService $vnpayService): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:50000000'],
            'payment_method' => ['nullable', 'string', 'in:vnpay'],
        ], [
            'amount.required' => 'Vui lòng nhập số tiền muốn nạp.',
            'amount.numeric' => 'Số tiền nạp không hợp lệ.',
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000đ.',
            'amount.max' => 'Số tiền nạp tối đa là 50.000.000đ.',
        ]);

        $owner = auth()->user();
        $wallet = $owner->getOrCreateWallet();

        $topup = DB::transaction(function () use ($owner, $wallet, $data): TopupTransaction {
            $topup = TopupTransaction::create([
                'owner_id' => $owner->id,
                'wallet_id' => $wallet->id,
                'code' => 'TOPUP-' . now()->format('YmdHis') . '-' . $owner->id . '-' . Str::upper(Str::random(4)),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'vnpay',
                'status' => 'pending',
            ]);

            $topup->update([
                'vnpay_txn_ref' => $topup->code,
            ]);

            return $topup->fresh();
        });

        try {
            $paymentUrl = $vnpayService->createTopupUrl($topup);
        } catch (\Throwable $exception) {
            $topup->update([
                'status' => 'failed',
            ]);

            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()->away($paymentUrl);
    }

    public function callback(
        Request $request,
        VnpayService $vnpayService,
        WalletService $walletService,
        PlatformWalletService $platformWalletService
    ): RedirectResponse {
        $txnRef = $request->input('vnp_TxnRef');

        if (! $txnRef) {
            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('error', 'Thiếu mã giao dịch VNPay.');
        }

        $topup = TopupTransaction::query()
            ->where('vnpay_txn_ref', $txnRef)
            ->orWhere('code', $txnRef)
            ->first();

        if (! $topup) {
            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('error', 'Không tìm thấy giao dịch nạp tiền.');
        }

        if ($topup->status === 'success') {
            $this->recordPlatformTopup($topup, $request, $platformWalletService);

            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('success', 'Giao dịch nạp tiền đã được xử lý trước đó.');
        }

        if (! $vnpayService->verifyReturn($request->all())) {
            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('error', 'Chữ ký VNPay không hợp lệ.');
        }

        $responseCode = $request->input('vnp_ResponseCode');
        $transactionStatus = $request->input('vnp_TransactionStatus');

        $isSuccess = $responseCode === '00'
            && ($transactionStatus === null || $transactionStatus === '00');

        if (! $isSuccess) {
            if ($topup->status === 'pending') {
                $topup->update([
                    'status' => 'failed',
                    'vnpay_response_code' => $responseCode,
                    'vnpay_transaction_no' => $request->input('vnp_TransactionNo'),
                ]);
            }

            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('error', 'Nạp tiền thất bại hoặc giao dịch đã bị hủy.');
        }

        $vnpAmount = (int) $request->input('vnp_Amount');
        $expectedAmount = (int) round((float) $topup->amount * 100);

        if ($vnpAmount !== $expectedAmount) {
            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('error', 'Số tiền thanh toán không khớp với giao dịch nạp tiền.');
        }

        DB::transaction(function () use ($topup, $request, $walletService, $platformWalletService, $responseCode): void {
            $lockedTopup = TopupTransaction::query()
                ->whereKey($topup->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTopup->status === 'success') {
                $this->recordPlatformTopup($lockedTopup, $request, $platformWalletService);

                return;
            }

            $lockedTopup->update([
                'status' => 'success',
                'paid_at' => now(),
                'vnpay_response_code' => $responseCode,
                'vnpay_transaction_no' => $request->input('vnp_TransactionNo'),
            ]);

            $walletService->processTransaction(
                wallet: $lockedTopup->wallet,
                type: TransactionType::TOPUP_CREDIT,
                amount: (float) $lockedTopup->amount,
                description: 'Nạp tiền vào ví qua VNPay: ' . $lockedTopup->code,
                metadata: [
                    'reference_type' => 'topup_transaction',
                    'reference_id' => $lockedTopup->id,
                    'topup_code' => $lockedTopup->code,
                    'vnpay_txn_ref' => $lockedTopup->vnpay_txn_ref,
                    'vnpay_transaction_no' => $request->input('vnp_TransactionNo'),
                ],
            );

            $this->recordPlatformTopup($lockedTopup, $request, $platformWalletService);

            if (class_exists(\App\Services\DebtService::class)) {
                app(\App\Services\DebtService::class)->syncOwnerStatus($lockedTopup->owner_id);
            }
        });

        return redirect()
            ->route('owner.web.wallet.topup.create')
            ->with('success', 'Nạp tiền vào ví thành công.');
    }

    private function recordPlatformTopup(
        TopupTransaction $topup,
        Request $request,
        PlatformWalletService $platformWalletService
    ): void {
        $platformWalletService->credit(
            amount: (float) $topup->amount,
            type: PlatformWalletTransaction::TYPE_OWNER_TOPUP_IN,
            description: 'Owner nạp tiền vào ví qua VNPay: ' . $topup->code,
            referenceType: 'topup_transaction',
            referenceId: $topup->id,
            reference: $topup->code,
            metadata: [
                'owner_id' => $topup->owner_id,
                'wallet_id' => $topup->wallet_id,
                'vnp_TxnRef' => $request->input('vnp_TxnRef'),
                'vnp_TransactionNo' => $request->input('vnp_TransactionNo'),
                'vnp_BankCode' => $request->input('vnp_BankCode'),
                'vnp_PayDate' => $request->input('vnp_PayDate'),
            ]
        );
    }
}
