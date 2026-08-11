<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\PlatformWalletTransaction;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\PackageBookingService;
use App\Services\PlatformWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class VnPayController extends Controller
{
    private function pendingPaymentExpired(Booking $booking): bool
    {
        if ($booking->status !== 'pending' || ! in_array($booking->payment_status, ['unpaid', 'pending'], true)) {
            return false;
        }

        $holdMinutes = max(1, (int) Setting::get('booking_hold_time', 15));

        return $booking->created_at && $booking->created_at->lte(now()->subMinutes($holdMinutes));
    }

    public function createPayment(Request $request, Booking $booking)
    {
        return redirect()->route('bookings.payment.vnpay_qr', $booking);
    }

    public function showVnpayQr(Request $request, Booking $booking)
    {
        $this->authorizeBookingPayment($booking);

        if ($booking->payment_status === 'paid') {
            return redirect()->route('web.bookings.success', ['booking' => $booking->id])
                ->with('success', 'Booking này đã được thanh toán.');
        }

        if (in_array($booking->status, ['cancelled', 'rejected'], true)) {
            return redirect()->route('web.bookings.success', ['booking' => $booking->id])
                ->with('error', 'Booking này đã bị hủy hoặc từ chối nên không thể thanh toán.');
        }

        if ($this->pendingPaymentExpired($booking)) {
            return redirect()->route('account.bookings.index')
                ->with('error', 'Thời gian giữ chỗ thanh toán đã hết. Vui lòng đặt lại ca mới.');
        }

        try {
            $paymentUrl = $this->createBookingPaymentUrl($request, $booking);
        } catch (\Throwable $e) {
            return redirect()->route('web.bookings.success', ['booking' => $booking->id])
                ->with('error', $e->getMessage());
        }

        $bookingGroup = $this->bookingPaymentGroup($booking)->get();
        $totalAmount = $bookingGroup->sum('total_price');

        return view('bookings.payment.vnpay-qr', compact('booking', 'bookingGroup', 'paymentUrl', 'totalAmount'));
    }

    public function startVnpay(Request $request, Booking $booking)
    {
        $this->authorizeBookingPayment($booking);

        if ($booking->payment_status === 'paid') {
            return redirect()->route('web.bookings.success', ['booking' => $booking->id])
                ->with('success', 'Booking này đã được thanh toán.');
        }

        if (in_array($booking->status, ['cancelled', 'rejected'], true)) {
            return redirect()->route('web.bookings.success', ['booking' => $booking->id])
                ->with('error', 'Booking này đã bị hủy hoặc từ chối nên không thể thanh toán.');
        }

        if ($this->pendingPaymentExpired($booking)) {
            return redirect()->route('account.bookings.index')
                ->with('error', 'Thời gian giữ chỗ thanh toán đã hết. Vui lòng đặt lại ca mới.');
        }

        try {
            return redirect()->away($this->createBookingPaymentUrl($request, $booking));
        } catch (\Throwable $e) {
            return redirect()->route('web.bookings.success', ['booking' => $booking->id])
                ->with('error', $e->getMessage());
        }
    }

    private function createBookingPaymentUrl(Request $request, Booking $booking): string
    {
        if ($this->pendingPaymentExpired($booking)) {
            throw new \RuntimeException('Thời gian giữ chỗ thanh toán đã hết. Vui lòng đặt lại ca mới.');
        }

        // Get all bookings in the same group (same court, date, created_at)
        $bookingGroup = $this->bookingPaymentGroup($booking)->get();

        if ($bookingGroup->isEmpty()) {
            throw new \RuntimeException('Đơn hàng không tồn tại hoặc đã bị hủy.');
        }

        $totalPrice = $bookingGroup->sum('total_price');

        if ($totalPrice <= 0) {
            throw new \RuntimeException('Số tiền thanh toán không hợp lệ.');
        }

        $bookingGroup->each(function (Booking $item): void {
            if ($item->payment_status !== 'paid') {
                $item->update([
                    'payment_method' => 'vnpay',
                    'payment_status' => 'pending',
                ]);
            }
        });

        Transaction::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->user_id,
                'transaction_code' => 'TXN-' . $booking->id . '-' . time(),
                'amount' => $totalPrice,
                'payment_method' => 'VNPay',
                'payment_gateway' => 'VNPay',
                'payment_status' => 'pending',
                'transaction_time' => now(),
                'note' => 'Khách hàng đang chuyển sang cổng thanh toán VNPay.',
            ]
        );

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

        return $vnp_Url;
    }

    public function vnpayReturn(Request $request, PlatformWalletService $platformWalletService, PackageBookingService $packageBookingService)
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
        $txnRef = (string) $request->input('vnp_TxnRef', '');
        $isPackageTransaction = str_starts_with($txnRef, 'PKG-');
        $isRescheduleTransaction = str_starts_with($txnRef, 'RS-');
        $orderId = null;

        if ($txnRef !== '') {
            $parts = explode('_', $txnRef);
            if ($isPackageTransaction) {
                $packageParts = explode('-', $txnRef);
                $orderId = isset($packageParts[1]) ? (int) $packageParts[1] : null;
            } else {
                $orderId = isset($parts[0]) ? (int) $parts[0] : null;
            }
        }

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                if ($isRescheduleTransaction) {
                    try {
                        $rawCode = explode('_', $txnRef)[0];
                        \App\Models\BookingRescheduleRequest::where('request_code', $rawCode)
                            ->update(['payment_status' => 'paid']);

                        return redirect()->route('account.bookings.index')
                            ->with('success', 'Thanh toán tiền chênh lệch đổi lịch qua VNPay thành công! Vui lòng chờ chủ sân duyệt.');
                    } catch (\Throwable $e) {
                        Log::error('Lỗi xử lý callback VNPay đổi lịch: ' . $e->getMessage());
                        return redirect()->route('account.bookings.index')
                            ->with('error', 'Thanh toán VNPay thành công nhưng chưa cập nhật được yêu cầu.');
                    }
                }

                if ($isPackageTransaction) {
                    try {
                        $bookingPackage = BookingPackage::findOrFail($orderId);

                        if ($packageBookingService->paymentHoldExpired($bookingPackage)) {
                            return redirect()->route('package-bookings.show', $bookingPackage)
                                ->with('error', 'Thời gian giữ chỗ thanh toán đã hết. Vui lòng tạo lại gói mới.');
                        }

                        $packageBookingService->activateAfterPayment(
                            bookingPackage: $bookingPackage,
                            changedBy: null,
                            transactionStatus: 'success'
                        );

                        return redirect()->route('package-bookings.show', $bookingPackage)
                            ->with('success', 'Thanh toán gói thành công. Hệ thống đã kích hoạt gói đặt sân.');
                    } catch (\Throwable $e) {
                        Log::error('Lỗi xử lý callback VNPay cho gói đặt sân: ' . $e->getMessage(), [
                            'txnRef' => $txnRef,
                            'request' => $request->all(),
                        ]);

                        return redirect()->route('package-bookings.create')
                            ->with('error', 'Thanh toán thành công nhưng có lỗi cập nhật gói. Vui lòng liên hệ hỗ trợ.');
                    }
                }

                try {
                    $booking = Booking::findOrFail($orderId);

                    if ($this->pendingPaymentExpired($booking)) {
                        return redirect()->route('account.bookings.index')
                            ->with('error', 'Thời gian giữ chỗ thanh toán đã hết. Ca đã được trả lại để người khác có thể đặt.');
                    }
                    
                    DB::transaction(function () use ($booking, $request, $platformWalletService): void {
                        $groupBookings = Booking::query()
                            ->where('user_id', $booking->user_id)
                            ->where('court_id', $booking->court_id)
                            ->where('slot_date', $booking->slot_date)
                            ->where('created_at', $booking->created_at)
                            ->whereNull('cancel_reason')
                            ->lockForUpdate()
                            ->get();

                        if ($groupBookings->isEmpty()) {
                            throw ValidationException::withMessages([
                                'booking' => 'Không tìm thấy đơn đặt sân cần thanh toán.',
                            ]);
                        }

                        $platformAmount = $groupBookings->sum(fn (Booking $item) => $this->getBookingPlatformAmount($item));

                        if ($platformAmount <= 0) {
                            throw ValidationException::withMessages([
                                'amount' => 'Số tiền booking không hợp lệ để ghi nhận vào ví nền tảng.',
                            ]);
                        }

                        $vnpAmount = (int) $request->input('vnp_Amount', 0);
                        $expectedVnpAmount = (int) round($platformAmount * 100);

                        if ($vnpAmount > 0 && $vnpAmount !== $expectedVnpAmount) {
                            throw ValidationException::withMessages([
                                'amount' => 'Số tiền thanh toán VNPay không khớp với booking.',
                            ]);
                        }

                        $now = now();

                        foreach ($groupBookings as $b) {
                            $oldStatus = $b->status;
                            $bookingAmount = $this->getBookingPlatformAmount($b);

                            if ($bookingAmount <= 0) {
                                throw ValidationException::withMessages([
                                    'amount' => 'Số tiền booking #' . $b->id . ' không hợp lệ.',
                                ]);
                            }

                            $bookingUpdates = [
                                'status' => 'confirmed',
                                'payment_status' => 'paid',
                                'payment_method' => 'vnpay',
                                'vnpay_tran_id' => $request->vnp_TransactionNo,
                            ];

                            if (Schema::hasColumn('bookings', 'paid_at') && ! $b->paid_at) {
                                $bookingUpdates['paid_at'] = $now;
                            }

                            $b->update($bookingUpdates);

                            Transaction::updateOrCreate(
                                ['booking_id' => $b->id],
                                [
                                    'user_id' => $b->user_id,
                                    'transaction_code' => 'TXN-' . $b->id . '-' . time(),
                                    'amount' => $bookingAmount,
                                    'payment_method' => 'VNPay',
                                    'payment_gateway' => 'VNPay',
                                    'payment_status' => 'success',
                                    'transaction_time' => $now,
                                    'note' => 'Thanh toán VNPay thành công.',
                                ]
                            );

                            if ($oldStatus !== 'confirmed') {
                                DB::table('booking_logs')->insert([
                                    'booking_id' => $b->id,
                                    'changed_by' => $b->user_id,
                                    'old_status' => $oldStatus,
                                    'new_status' => 'confirmed',
                                    'note' => 'Hệ thống tự động xác nhận sau khi thanh toán VNPay thành công.',
                                    'created_at' => $now,
                                ]);
                            }

                            $platformWalletService->credit(
                                amount: $bookingAmount,
                                type: PlatformWalletTransaction::TYPE_CUSTOMER_ONLINE_PAYMENT_IN,
                                description: 'Khách thanh toán booking online: BOOKING-' . $b->id,
                                referenceType: 'booking',
                                referenceId: $b->id,
                                reference: 'BOOKING-' . $b->id,
                                metadata: [
                                    'payment_method' => 'vnpay',
                                    'vnp_TxnRef' => $request->input('vnp_TxnRef'),
                                    'vnp_TransactionNo' => $request->input('vnp_TransactionNo'),
                                    'vnp_BankCode' => $request->input('vnp_BankCode'),
                                    'vnp_PayDate' => $request->input('vnp_PayDate'),
                                    'group_total_amount' => $platformAmount,
                                ]
                            );
                        }
                    });
                        
                    return redirect()->route('web.bookings.success', ['booking' => $orderId])
                                   ->with('success', 'Thanh toán thành công qua VNPay!');
                } catch (\Exception $e) {
                    Log::error('VNPay Success Processing Error: ' . $e->getMessage());
                    return redirect()->route('account.bookings.index')
                                   ->with('error', 'Thanh toán thành công nhưng có lỗi cập nhật. Vui lòng liên hệ hỗ trợ.');
                }
            } else {
                $booking = Booking::find($orderId);
                if ($booking) {
                    Transaction::updateOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'user_id' => $booking->user_id,
                            'transaction_code' => 'TXN-' . $booking->id . '-' . time(),
                            'amount' => $booking->total_price,
                            'payment_method' => 'VNPay',
                            'payment_gateway' => 'VNPay',
                            'payment_status' => 'failed',
                            'transaction_time' => now(),
                            'note' => 'Thanh toán VNPay bị hủy hoặc thất bại.',
                        ]
                    );
                }

                return redirect()->route('web.bookings.success', ['booking' => $orderId])
                               ->with('error', 'Giao dịch không thành công hoặc bị hủy.');
            }
        } else {
            // Invalid Signature
            return redirect()->route('account.bookings.index')
                           ->with('error', 'Chữ ký VNPay không hợp lệ. Giao dịch bị từ chối.');
        }
    }

    private function getBookingPlatformAmount(Booking $booking): float
    {
        if (Schema::hasColumn('bookings', 'gross_amount') && (float) $booking->gross_amount > 0) {
            return (float) $booking->gross_amount;
        }

        if (Schema::hasColumn('bookings', 'total_price') && (float) $booking->total_price > 0) {
            return (float) $booking->total_price;
        }

        if (Schema::hasColumn('bookings', 'total_amount') && (float) $booking->total_amount > 0) {
            return (float) $booking->total_amount;
        }

        return 0.0;
    }

    private function bookingPaymentGroup(Booking $booking)
    {
        return Booking::query()
            ->where('user_id', $booking->user_id)
            ->where('court_id', $booking->court_id)
            ->where('slot_date', $booking->slot_date)
            ->where('created_at', $booking->created_at)
            ->whereNull('cancel_reason');
    }

    private function authorizeBookingPayment(Booking $booking): void
    {
        if (! Auth::check() || (int) $booking->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
