<?php

namespace App\Http\Controllers\Api;

use App\Models\Venue;
use App\Models\Booking;
use App\Models\VenueLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AdminVenueController extends Controller
{
    /**
     * Liệt kê danh sách sân với các tùy chọn lọc
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Venue::query()
            ->with(['owner', 'sport', 'courts']);

        // Lọc theo status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo tên hoặc địa chỉ
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Lọc theo chủ sân
        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Lọc theo thể thao
        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Sắp xếp
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Phân trang
        $per_page = $request->input('per_page', 15);
        $venues = $query->paginate($per_page);

        return response()->json([
            'success' => true,
            'message' => 'Danh sách sân',
            'data' => $venues,
        ]);
    }

    /**
     * Xem chi tiết một sân kèm lịch sử log
     * 
     * @param Venue $venue
     * @return JsonResponse
     */
    public function show(Venue $venue): JsonResponse
    {
        $venue->load(['owner', 'sport', 'courts', 'logs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        // Kiểm tra có booking trong tương lai không
        $futureBookings = $venue->courts()
            ->with('bookings')
            ->get()
            ->flatMap->bookings
            ->filter(function ($booking) {
                return $booking->date >= Carbon::now()->format('Y-m-d')
                    && in_array($booking->status, ['confirmed', 'pending']);
            });

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết sân',
            'data' => $venue,
            'future_bookings_count' => $futureBookings->count(),
        ]);
    }

    /**
     * Kích hoạt sân
     * 
     * @param Request $request
     * @param Venue $venue
     * @return JsonResponse
     */
    public function activate(Request $request, Venue $venue): JsonResponse
    {
        // Validate
        $request->validate([
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Kiểm tra nếu sân đã active
        if ($venue->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sân này đã kích hoạt.',
            ], 400);
        }

        $oldStatus = $venue->status;
        $newStatus = 'active';

        try {
            // Cập nhật status
            $venue->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            // Ghi log
            VenueLog::create([
                'venue_id' => $venue->id,
                'admin_id' => auth()->id(),
                'action' => 'activated',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $request->input('reason'),
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sân đã được kích hoạt thành công.',
                'data' => $venue->fresh(['owner', 'sport']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kích hoạt sân: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ẩn sân
     * 
     * @param Request $request
     * @param Venue $venue
     * @return JsonResponse
     */
    public function deactivate(Request $request, Venue $venue): JsonResponse
    {
        // Validate
        $request->validate([
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Kiểm tra nếu sân đã inactive
        if ($venue->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Sân này đã bị ẩn.',
            ], 400);
        }

        $oldStatus = $venue->status;
        $newStatus = 'inactive';

        // Kiểm tra có booking trong tương lai không
        $futureBookings = $venue->courts()
            ->with('bookings')
            ->get()
            ->flatMap->bookings
            ->filter(function ($booking) {
                return $booking->date >= Carbon::now()->format('Y-m-d')
                    && in_array($booking->status, ['confirmed', 'pending']);
            });

        try {
            // Cập nhật status
            $venue->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            // Ghi log
            VenueLog::create([
                'venue_id' => $venue->id,
                'admin_id' => auth()->id(),
                'action' => 'deactivated',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $request->input('reason'),
                'notes' => $request->input('notes') . 
                    ($futureBookings->count() > 0 
                        ? "\n\nGhi chú: Sân có {$futureBookings->count()} booking trong tương lai vẫn được giữ lại." 
                        : ''),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sân đã được ẩn thành công.' . 
                    ($futureBookings->count() > 0 
                        ? " ({$futureBookings->count()} booking trong tương lai vẫn được giữ lại.)" 
                        : ''),
                'data' => $venue->fresh(['owner', 'sport']),
                'future_bookings_count' => $futureBookings->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi ẩn sân: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xem lịch sử log của sân
     * 
     * @param Request $request
     * @param Venue $venue
     * @return JsonResponse
     */
    public function logs(Request $request, Venue $venue): JsonResponse
    {
        $per_page = $request->input('per_page', 20);
        
        $logs = $venue->logs()
            ->with(['admin'])
            ->orderBy('created_at', 'desc')
            ->paginate($per_page);

        return response()->json([
            'success' => true,
            'message' => 'Lịch sử thay đổi sân',
            'data' => $logs,
        ]);
    }
}
