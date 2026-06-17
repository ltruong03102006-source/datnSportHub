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
        $baseQuery = Booking::query();

        // Tìm kiếm theo tên người dùng, tên sân hoặc mã booking (nếu muốn)
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('court.venue', function($venueQuery) use ($search) {
                    $venueQuery->where('name', 'like', "%{$search}%");
                })->orWhere('id', $search);
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sắp xếp lịch đặt mới nhất
        $bookingStats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->whereIn('status', ['cancelled', 'rejected'])->count(),
            'revenue' => (clone $baseQuery)->whereIn('status', ['confirmed', 'completed'])->sum('total_price') ?? 0,
        ];

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'bookingStats'));
    }
}
