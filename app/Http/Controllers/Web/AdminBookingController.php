<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    /**
     * Hiển thị danh sách Lịch đặt (Bookings) toàn hệ thống
     */
    public function index(Request $request): View
    {
        $query = Booking::with(['user', 'court.venue']);

        // Tìm kiếm theo tên người dùng, tên sân hoặc mã booking (nếu muốn)
        if ($search = $request->input('search')) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('court.venue', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sắp xếp lịch đặt mới nhất
        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }
}
