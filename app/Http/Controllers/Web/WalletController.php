<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'bank_name' => 'required|string|max:255',
            'bank_account_no' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:255',
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là 10,000đ.',
            'amount.required' => 'Vui lòng nhập số tiền muốn rút.',
        ]);

        $user = Auth::user();
        $amount = $request->input('amount');

        if ($user->balance < $amount) {
            return back()->withErrors(['amount' => 'Số dư không đủ để rút số tiền này.'])->withInput();
        }

        try {
            DB::transaction(function () use ($user, $amount, $request) {
                // Deduct balance
                $user->balance -= $amount;
                $user->save();

                // Create withdrawal request
                $withdrawal = WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'bank_name' => $request->input('bank_name'),
                    'bank_account_no' => $request->input('bank_account_no'),
                    'bank_account_name' => strtoupper($request->input('bank_account_name')),
                    'status' => 'pending',
                ]);

                // Create wallet transaction
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdraw',
                    'amount' => $amount,
                    'balance_after' => $user->balance,
                    'description' => 'Yêu cầu rút tiền #' . $withdrawal->id,
                ]);
            });

            // Gửi thông báo cho Admin (optional)
            $adminIds = \App\Models\User::where('role', 'admin')->pluck('id');
            foreach ($adminIds as $adminId) {
                app(\App\Services\NotificationService::class)->create(
                    $adminId,
                    'Yêu cầu rút tiền mới',
                    "User {$user->name} vừa yêu cầu rút " . number_format($amount) . "đ.",
                    route('admin.withdrawals.index'), // Giả định route này sẽ có
                    'new_withdrawal_request'
                );
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        return back()->with('success', 'Gửi yêu cầu rút tiền thành công. Vui lòng đợi 1-3 phút để hệ thống xử lý.');
    }
}
