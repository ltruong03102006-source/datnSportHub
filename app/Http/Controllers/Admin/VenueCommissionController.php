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
        ]);

        $venue->update([
            'commission_rate' => $request->commission_rate,
        ]);

        return back()->with('success', 'Đã cập nhật tỷ lệ hoa hồng riêng cho cơ sở này!');
    }
}