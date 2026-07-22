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
        // max:0 bắt buộc người dùng phải nhập số âm hoặc bằng 0 theo đúng ghi chú trên UI
        'owner_credit_limit'      => 'required|numeric|max:0', 
        'minimum_withdraw'        => 'required|numeric|min:0',
        'minimum_topup'           => 'required|numeric|min:0',
    ], [
        // Messages cho Hoa hồng
        'default_commission_rate.required' => 'Vui lòng nhập tỷ lệ hoa hồng mặc định.',
        'default_commission_rate.numeric'  => 'Tỷ lệ hoa hồng phải là định dạng số.',
        'default_commission_rate.min'      => 'Tỷ lệ hoa hồng không được nhỏ hơn 0%.',
        'default_commission_rate.max'      => 'Tỷ lệ hoa hồng không được vượt quá 100%.',

        // Messages cho Hạn mức nợ
        'owner_credit_limit.required'      => 'Vui lòng nhập hạn mức nợ tối đa.',
        'owner_credit_limit.numeric'       => 'Hạn mức nợ phải là định dạng số.',
        'owner_credit_limit.max'           => 'Hạn mức nợ phải là số âm hoặc bằng 0 (VD: -1000000).',

        // Messages cho Rút tiền
        'minimum_withdraw.required'        => 'Vui lòng nhập số tiền rút tối thiểu.',
        'minimum_withdraw.numeric'         => 'Số tiền rút tối thiểu phải là định dạng số.',
        'minimum_withdraw.min'             => 'Số tiền rút tối thiểu không được nhỏ hơn 0.',

        // Messages cho Nạp tiền
        'minimum_topup.required'           => 'Vui lòng nhập số tiền nạp tối thiểu.',
        'minimum_topup.numeric'            => 'Số tiền nạp tối thiểu phải là định dạng số.',
        'minimum_topup.min'                => 'Số tiền nạp tối thiểu không được nhỏ hơn 0.',
    ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Cập nhật cấu hình tài chính thành công!');
    }
}