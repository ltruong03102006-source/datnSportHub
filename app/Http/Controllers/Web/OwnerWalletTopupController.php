<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TopupTransaction;
use App\Services\VnpayService;
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

    public function callback(Request $request): RedirectResponse
    {
        return redirect()
            ->route('owner.web.wallet.topup.create')
            ->with('error', 'Callback VNPay sẽ được xử lý ở bước tiếp theo. Giao dịch chưa được cộng vào ví.');
    }
}
