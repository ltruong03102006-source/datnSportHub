<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $withdrawals = $query->paginate(15)->withQueryString();

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function updateStatus(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500',
            'proof_image' => 'required_if:status,approved|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $newStatus = $request->input('status');
        $adminNote = $request->input('admin_note');

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
                $withdrawal->save();

                if ($newStatus === 'rejected') {
                    // Hoàn lại tiền cho user
                    $user = $withdrawal->user;
                    $user->balance += $withdrawal->amount;
                    $user->save();

                    // Log transaction
                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'refund',
                        'amount' => $withdrawal->amount,
                        'balance_after' => $user->balance,
                        'description' => 'Hoàn tiền do yêu cầu rút tiền bị từ chối. #' . $withdrawal->id,
                    ]);
                }
            });

            // Gửi thông báo cho user
            $title = $newStatus === 'approved' ? 'Yêu cầu rút tiền thành công' : 'Yêu cầu rút tiền bị từ chối';
            $message = $newStatus === 'approved' 
                ? 'Yêu cầu rút ' . number_format($withdrawal->amount) . 'đ của bạn đã được chuyển khoản. Vui lòng kiểm tra tài khoản ngân hàng.' 
                : 'Yêu cầu rút ' . number_format($withdrawal->amount) . 'đ bị từ chối. Số tiền đã được hoàn lại vào ví. Lý do: ' . $adminNote;

            if ($newStatus === 'approved' && $withdrawal->proof_image) {
                $message .= ' (Minh chứng: ' . asset('storage/' . $withdrawal->proof_image) . ')';
            }
            app(\App\Services\NotificationService::class)->create(
                $withdrawal->user_id,
                $title,
                $message,
                route('account.profile.show'), 
                'withdrawal_update'
            );

            return back()->with('success', 'Đã cập nhật trạng thái yêu cầu rút tiền.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
