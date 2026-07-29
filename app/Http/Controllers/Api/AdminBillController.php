<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

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

        $bill->status = 'paid'; // Hoặc 'completed'
        $bill->payment_confirmed_at = now();
        $bill->admin_id = $request->user()->id;
        $bill->save();

        return response()->json([
            'message' => 'Xác nhận thanh toán thành công',
            'data' => $bill
        ]);
    }
}
