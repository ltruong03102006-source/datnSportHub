<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;

class AdminSettingController extends Controller
{
    public function index()
    {
        $bookingHoldTime = Setting::get('booking_hold_time', 15);
        
        return view('admin.settings.index', compact('bookingHoldTime'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_hold_time' => 'required|integer|min:1|max:1440',
        ]);

        Setting::set('booking_hold_time', $request->input('booking_hold_time'));

        return back()->with('success', 'Đã cập nhật cài đặt thành công.');
    }
}
