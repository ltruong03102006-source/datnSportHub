<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OwnerBookingController extends Controller
{
    /**
     * Lấy danh sách booking của chủ sân
     * GET /api/owner/bookings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Lấy tất cả bookings cho các sân của owner này
            $bookings = Booking::whereHas('court', function ($query) use ($user) {
                $query->whereHas('venue', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                });
            })
                ->with('court', 'court.venue', 'user', 'slotPrice')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Danh sách booking',
                'count' => $bookings->count(),
                'data' => $bookings
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy booking của một sân
     * GET /api/owner/venues/{venueId}/bookings
     */
    public function venueBookings(Request $request, $venueId): JsonResponse
    {
        try {
            $user = $request->user();

            // Kiểm tra venue thuộc về owner
            $venue = Venue::where('owner_id', $user->id)
                ->where('id', $venueId)
                ->first();

            if (!$venue) {
                return response()->json([
                    'message' => 'Sân không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            $bookings = Booking::whereHas('court', function ($query) use ($venueId) {
                $query->where('venue_id', $venueId);
            })
                ->with('court', 'user', 'slotPrice')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Danh sách booking của sân',
                'count' => $bookings->count(),
                'data' => $bookings
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy booking của một sân nhỏ
     * GET /api/owner/courts/{courtId}/bookings
     */
    public function courtBookings(Request $request, $courtId): JsonResponse
    {
        try {
            $user = $request->user();

            // Kiểm tra court thuộc về owner
            $court = Court::whereHas('venue', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })->where('id', $courtId)->first();

            if (!$court) {
                return response()->json([
                    'message' => 'Sân nhỏ không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            $bookings = Booking::where('court_id', $courtId)
                ->with('user', 'slotPrice')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Danh sách booking của sân nhỏ',
                'count' => $bookings->count(),
                'data' => $bookings
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy chi tiết booking
     * GET /api/owner/bookings/{bookingId}
     */
    public function show(Request $request, $bookingId): JsonResponse
    {
        try {
            $user = $request->user();

            $booking = Booking::where('id', $bookingId)
                ->whereHas('court', function ($query) use ($user) {
                    $query->whereHas('venue', function ($q) use ($user) {
                        $q->where('owner_id', $user->id);
                    });
                })
                ->with('court', 'court.venue', 'user', 'slotPrice')
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            return response()->json([
                'data' => $booking
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy chi tiết booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê booking theo trạng thái
     * GET /api/owner/bookings/stats
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Tất cả bookings của owner
            $allBookings = Booking::whereHas('court', function ($query) use ($user) {
                $query->whereHas('venue', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                });
            });

            $stats = [
                'total' => (clone $allBookings)->count(),
                'confirmed' => (clone $allBookings)->where('status', 'confirmed')->count(),
                'pending' => (clone $allBookings)->where('status', 'pending')->count(),
                'cancelled' => (clone $allBookings)->where('status', 'cancelled')->count(),
            ];

            return response()->json([
                'message' => 'Thống kê booking',
                'data' => $stats
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy thống kê',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
