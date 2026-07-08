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

        // Lọc theo trạng thái hoàn tiền
        if ($refundStatus = $request->input('refund_status')) {
            $query->where('refund_status', $refundStatus);
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

    /**
     * Xác nhận đã hoàn tiền cho khách
     */
    public function refund(Request $request, Booking $booking)
    {
        if ($booking->refund_status !== 'pending') {
            return back()->with('error', 'Đơn hàng này không có yêu cầu hoàn tiền hoặc đã được hoàn.');
        }

        $booking->update([
            'refund_status' => 'refunded'
        ]);

        \App\Models\BookingLog::create([
            'booking_id' => $booking->id,
            'changed_by' => \Illuminate\Support\Facades\Auth::id(),
            'old_status' => $booking->status,
            'new_status' => $booking->status,
            'note' => 'Hệ thống Admin đã xác nhận hoàn tiền cho khách hàng.',
        ]);

        return back()->with('success', 'Đã xác nhận hoàn tiền thành công.');
    }
}
