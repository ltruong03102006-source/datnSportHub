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
        // 1. Chỉ validate 2 trường còn hiển thị trên giao diện
        $validated = $request->validate([
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'minimum_withdraw'        => 'required|numeric|min:0',
        ], [
            // 2. Chỉ giữ lại Messages cho 2 trường này
            'default_commission_rate.required' => 'Vui lòng nhập tỷ lệ hoa hồng mặc định.',
            'default_commission_rate.numeric'  => 'Tỷ lệ hoa hồng phải là định dạng số.',
            'default_commission_rate.min'      => 'Tỷ lệ hoa hồng không được nhỏ hơn 0%.',
            'default_commission_rate.max'      => 'Tỷ lệ hoa hồng không được vượt quá 100%.',

            'minimum_withdraw.required'        => 'Vui lòng nhập số tiền rút tối thiểu.',
            'minimum_withdraw.numeric'         => 'Số tiền rút tối thiểu phải là định dạng số.',
            'minimum_withdraw.min'             => 'Số tiền rút tối thiểu không được nhỏ hơn 0.',
        ]);

        // 3. Tiến hành cập nhật
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Cập nhật cấu hình tài chính thành công!');
    }
}