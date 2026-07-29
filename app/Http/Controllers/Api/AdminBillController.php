<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminBillController extends Controller
{
    /**
     * Xác nhận thanh toán thành công cho Bill.
     */
    public function confirmPayment(Request $request, $billId)
    {
        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json([
                'message' => 'Bill không tồn tại'
            ], 404);
        }

        // 1. Kiểm tra trạng thái hiện tại của bill (chỉ cho phép nếu đang pending)
        if ($bill->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể xác nhận thanh toán cho bill đang ở trạng thái pending'
            ], 400);
        }

        $oldStatus = $bill->status;

        // Cập nhật trạng thái bill
        $bill->status = 'paid';
        $bill->payment_confirmed_at = now();
        $bill->admin_id = $request->user()->id;
        $bill->save();

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
                    'description' => "Cộng tiền từ xác nhận bill ID: {$bill->id}"
                ]);
            }
        }

        // 3. Ghi log hành động (audit trail)
        Log::info('Admin confirmed bill payment', [
            'admin_id' => $request->user()->id,
            'bill_id' => $bill->id,
            'amount' => $bill->amount,
            'old_status' => $oldStatus,
            'new_status' => $bill->status,
            'confirmed_at' => $bill->payment_confirmed_at->toDateTimeString()
        ]);

        // 4. Trả về response với thông tin bill đã cập nhật
        return response()->json([
            'message' => 'Xác nhận thanh toán thành công',
            'data' => $bill
        ]);
    }
}
