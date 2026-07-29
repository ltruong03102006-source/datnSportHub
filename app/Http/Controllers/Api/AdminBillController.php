<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminBillController extends Controller
{
    /**
     * Xác nhận thanh toán thành công cho Bill (Giao dịch).
     */
    public function confirmPayment(Request $request, $billId)
    {
        // Trong hệ thống, danh sách Bills thực chất là Transactions
        $bill = Transaction::find($billId);

        if (!$bill) {
            return response()->json([
                'message' => 'Giao dịch (Bill) không tồn tại'
            ], 404);
        }

        // 1. Kiểm tra trạng thái hiện tại của bill (chỉ cho phép nếu đang pending)
        if ($bill->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể xác nhận thanh toán cho giao dịch đang ở trạng thái pending'
            ], 400);
        }

        $oldStatus = $bill->payment_status;

        // Cập nhật trạng thái giao dịch
        $bill->payment_status = 'success'; // hoặc 'paid' tuỳ cấu hình của bạn
        $bill->transaction_time = now();
        $bill->save();

        $user = null;
        // 2. Cập nhật số dư/dữ liệu liên quan của thành viên
        if ($bill->user_id && $bill->amount) {
            $user = User::find($bill->user_id);
            if ($user) {
                $user->balance += $bill->amount;
                $user->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $bill->amount,
                    'balance_after' => $user->balance,
                    'description' => "Cộng tiền từ xác nhận giao dịch ID: {$bill->id}"
                ]);
            }
        }

        // 3. Ghi log hành động (audit trail)
        Log::info('Admin confirmed bill payment', [
            'admin_id' => $request->user()->id,
            'transaction_id' => $bill->id,
            'amount' => $bill->amount,
            'old_status' => $oldStatus,
            'new_status' => $bill->payment_status,
            'confirmed_at' => $bill->transaction_time->toDateTimeString()
        ]);

        // Gửi notification cho thành viên
        if ($bill->user_id) {
            Notification::create([
                'user_id' => $bill->user_id,
                'type' => 'system',
                'title' => 'Xác nhận thanh toán',
                'content' => 'Admin đã xác nhận chuyển tiền thành công cho giao dịch #' . $bill->id,
                'link' => '#',
                'is_read' => false,
            ]);
        }

        // 4. Trả về response
        return response()->json([
            'message' => 'Xác nhận thanh toán thành công',
            'data' => $bill
        ]);
    }
}
