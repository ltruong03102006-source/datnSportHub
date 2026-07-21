<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class FinancialSettingController extends Controller
{
    public function index()
    {
        // Lấy danh sách các cấu hình tài chính
        $keys = ['default_commission_rate', 'owner_credit_limit', 'minimum_withdraw', 'minimum_topup'];
        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');
        
        return view('admin.financial-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'owner_credit_limit' => 'required|numeric|max:0', // Phải là số âm hoặc 0
            'minimum_withdraw' => 'required|numeric|min:0',
            'minimum_topup' => 'required|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Cập nhật cấu hình tài chính thành công!');
    }
}