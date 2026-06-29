<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VnPayController extends Controller
{
    public function createPayment(Request $request, Booking $booking)
    {
        // Get all bookings in the same group (same court, date, created_at)
        $bookingGroup = Booking::where('user_id', auth()->id())
            ->where('court_id', $booking->court_id)
            ->where('slot_date', $booking->slot_date)
            ->where('created_at', $booking->created_at)
            ->where('status', $booking->status)
            ->whereNull('cancel_reason')
            ->get();

        if ($bookingGroup->isEmpty()) {
            return redirect()->back()->with('error', 'Đơn hàng không tồn tại hoặc đã bị hủy.');
        }

        $totalPrice = $bookingGroup->sum('total_price');

        $vnp_TmnCode = config('vnpay.vnp_TmnCode');
        $vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $vnp_Url = config('vnpay.vnp_Url');
        $vnp_Returnurl = route('vnpay.callback');

        $vnp_TxnRef = $booking->id . '_' . time(); // Mã đơn hàng + time để tránh trùng lặp khi retry
        $vnp_OrderInfo = 'Thanh toan don hang SportHub ' . $booking->id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $totalPrice * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return redirect($vnp_Url);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        $orderIdArr = explode('_', $request->vnp_TxnRef);
        $bookingId = $orderIdArr[0];

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                // Payment Success
                try {
                    $booking = Booking::findOrFail($bookingId);
                    
                    $groupBookings = Booking::where('user_id', $booking->user_id)
                        ->where('court_id', $booking->court_id)
                        ->where('slot_date', $booking->slot_date)
                        ->where('created_at', $booking->created_at)
                        ->where('status', $booking->status)
                        ->get();

                    $now = now();
                    foreach ($groupBookings as $b) {
                        $oldStatus = $b->status;
                        
                        $b->update([
                            'status' => 'confirmed',
                            'payment_status' => 'paid',
                            'payment_method' => 'vnpay',
                            'vnpay_tran_id' => $request->vnp_TransactionNo
                        ]);
                        
                        \DB::table('booking_logs')->insert([
                            'booking_id' => $b->id,
                            'changed_by' => $b->user_id,
                            'old_status' => $oldStatus,
                            'new_status' => 'confirmed',
                            'note' => 'Hệ thống tự động xác nhận sau khi thanh toán VNPay thành công.',
                            'created_at' => $now
                        ]);
                    }
                        
                    return redirect()->route('web.bookings.success', ['booking' => $bookingId])
                                   ->with('success', 'Thanh toán thành công qua VNPay!');
                } catch (\Exception $e) {
                    Log::error('VNPay Success Processing Error: ' . $e->getMessage());
                    return redirect()->route('account.bookings.index')
                                   ->with('error', 'Thanh toán thành công nhưng có lỗi cập nhật. Vui lòng liên hệ hỗ trợ.');
                }
            } else {
                // Payment Failed
                return redirect()->route('web.bookings.success', ['booking' => $bookingId])
                               ->with('error', 'Giao dịch không thành công hoặc bị hủy.');
            }
        } else {
            // Invalid Signature
            return redirect()->route('account.bookings.index')
                           ->with('error', 'Chữ ký VNPay không hợp lệ. Giao dịch bị từ chối.');
        }
    }
}
