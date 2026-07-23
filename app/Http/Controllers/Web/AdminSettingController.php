<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $bookingHoldTime = Setting::get('booking_hold_time', 15);
        $paymentQrBankName = Setting::get('payment_qr_bank_name');
        $paymentQrAccountNo = Setting::get('payment_qr_account_no');
        $paymentQrAccountName = Setting::get('payment_qr_account_name');

        return view('admin.settings.index', compact(
            'bookingHoldTime',
            'paymentQrBankName',
            'paymentQrAccountNo',
            'paymentQrAccountName'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_hold_time' => ['required', 'integer', 'min:1', 'max:1440'],
            'payment_qr_bank_name' => ['nullable', 'string', 'max:50'],
            'payment_qr_account_no' => ['nullable', 'string', 'max:50'],
            'payment_qr_account_name' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('booking_hold_time', $validated['booking_hold_time']);
        Setting::set('payment_qr_bank_name', $validated['payment_qr_bank_name'] ?? null);
        Setting::set('payment_qr_account_no', $validated['payment_qr_account_no'] ?? null);
        Setting::set(
            'payment_qr_account_name',
            mb_strtoupper((string) ($validated['payment_qr_account_name'] ?? ''), 'UTF-8')
        );

        return back()->with('success', 'Đã cập nhật cài đặt thành công.');
    }
}
