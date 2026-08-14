<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:10000',
            'bank_name' => 'required|string|max:255',
            'bank_account_no' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:255',
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là 10,000đ.',
            'amount.required' => 'Vui lòng nhập số tiền muốn rút.',
            'bank_name.required' => 'Vui lòng chọn ngân hàng.',
            'bank_account_no.required' => 'Vui lòng nhập số tài khoản.',
            'bank_account_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $amount = (float) $data['amount'];

        try {
            DB::transaction(function () use ($user, $wallet, $amount, $data) {
                // Calculate pending withdrawal amount
                $pendingWithdrawAmount = WithdrawalRequest::query()
                    ->where('owner_id', $user->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->sum('amount');

                $availableBalance = max(0, (float) $wallet->balance - (float) $pendingWithdrawAmount);

                if ($amount > $availableBalance) {
                    throw new \Exception('Số dư không đủ để rút số tiền này.');
                }

                $bankAccountHolder = strtoupper($data['bank_account_name']);

                // Save/update bank details for future auto-fill
                $user->update([
                    'bank_name' => $data['bank_name'],
                    'bank_account_no' => $data['bank_account_no'],
                    'bank_account_name' => $bankAccountHolder,
                ]);

                // Create withdrawal request
                WithdrawalRequest::create([
                    'owner_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'code' => 'WD-' . now()->format('YmdHis') . '-' . $user->id . '-' . Str::upper(Str::random(4)),
                    'amount' => $amount,
                    'bank_name' => $data['bank_name'],
                    'bank_account_number' => $data['bank_account_no'],
                    'bank_account_holder' => $bankAccountHolder,
                    'bank_account_no' => $data['bank_account_no'],
                    'bank_account_name' => $bankAccountHolder,
                    'status' => 'pending',
                ]);
            });

            // Send notification to Admin
            $adminIds = User::where('role', 'admin')->pluck('id');
            foreach ($adminIds as $adminId) {
                app(\App\Services\NotificationService::class)->create(
                    $adminId,
                    'Yêu cầu rút tiền mới',
                    "Người dùng {$user->name} vừa yêu cầu rút " . number_format($amount) . "đ.",
                    route('admin.withdrawals.index'),
                    'new_withdrawal_request'
                );
            }

        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Gửi yêu cầu rút tiền thành công. Vui lòng chờ admin duyệt.');
    }
}
