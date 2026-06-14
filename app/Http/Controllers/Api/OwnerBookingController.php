<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingCancelledMail;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Venue;
use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
                'completed' => (clone $allBookings)->where('status', 'completed')->count(),
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

    public function cancel(Request $request, $bookingId): JsonResponse
    {
        try {
            $user = $request->user();
            $reason = $request->input('reason');

            $booking = Booking::where('id', $bookingId)
                ->whereHas('court', function ($query) use ($user) {
                    $query->whereHas('venue', function ($q) use ($user) {
                        $q->where('owner_id', $user->id);
                    });
                })
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            if (!is_string($reason) || mb_strlen(trim($reason)) > 500) {
                return response()->json([
                    'message' => 'Lý do hủy không hợp lệ'
                ], 422);
            }

            $result = DB::transaction(function () use ($booking, $user, $reason) {
                $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();

                if (!$lockedBooking) {
                    throw new HttpException(404, 'Booking không được tìm thấy');
                }

                if ($lockedBooking->status !== 'confirmed') {
                    throw new HttpException(422, 'Chỉ booking đã xác nhận mới có thể hủy');
                }

                $oldStatus = $lockedBooking->status;
                $lockedBooking->status = 'cancelled';
                $lockedBooking->save();

                $lockedBooking->recordStatusChange($user->id, $oldStatus, 'cancelled', $reason ?: 'Owner cancelled booking');
                dispatch(new SendBookingCancelledMail($lockedBooking));

                return $lockedBooking;
            });

            return response()->json([
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking_id' => $result->id,
                    'status' => $result->status,
                ],
            ], 200);
        } catch (HttpException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi hủy booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
