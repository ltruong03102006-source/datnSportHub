<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueCommissionController extends Controller
{
    public function update(Request $request, Venue $venue)
    {
        $request->validate([
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ], [
            // Thêm các dòng thông báo lỗi tiếng Việt này
            'commission_rate.numeric' => 'Tỷ lệ hoa hồng phải là một số.',
            'commission_rate.min'     => 'Tỷ lệ hoa hồng không được là số âm.',
            'commission_rate.max'     => 'Tỷ lệ hoa hồng tối đa là 100%.',
        ]);

        $venue->update([
            'commission_rate' => $request->commission_rate,
        ]);

        return back()->with('success', 'Đã cập nhật tỷ lệ hoa hồng riêng cho cơ sở này!');
    }
}