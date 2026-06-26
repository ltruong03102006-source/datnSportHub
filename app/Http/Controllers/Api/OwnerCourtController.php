<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OwnerCourtController extends Controller
{
    /**
     * Lấy danh sách sân nhỏ của chủ sân
     * GET /api/owner/courts
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $courts = Court::whereHas('venue', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })->with('venue', 'timeSlots')->get();

            return response()->json([
                'message' => 'Danh sách sân nhỏ của bạn',
                'data' => $courts
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách sân nhỏ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo sân nhỏ
     * POST /api/owner/venues/{venueId}/courts
     */
    public function store(Request $request, $venueId): JsonResponse
    {
        try {
            $user = $request->user();

            // Kiểm tra venue thuộc về chủ sân này
            $venue = Venue::where('owner_id', $user->id)
                ->where('id', $venueId)
                ->first();

            if (!$venue) {
                return response()->json([
                    'message' => 'Sân không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            if ($venue->status !== 'approved') {
                return response()->json([
                    'message' => 'Bạn phải được Admin duyệt cơ sở trước khi tạo sân.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            $court = Court::create([
                'venue_id' => $venueId,
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json([
                'message' => 'Tạo sân nhỏ thành công',
                'data' => $court->load('venue')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo sân nhỏ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật sân nhỏ
     * PUT /api/owner/courts/{courtId}
     */
    public function update(Request $request, $courtId): JsonResponse
    {
        try {
            $user = $request->user();

            $court = Court::whereHas('venue', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })->where('id', $courtId)->first();

            if (!$court) {
                return response()->json([
                    'message' => 'Sân nhỏ không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'type' => 'sometimes|string|max:100',
                'description' => 'nullable|string',
            ]);

            $court->update($validated);

            return response()->json([
                'message' => 'Cập nhật sân nhỏ thành công',
                'data' => $court->load('venue')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi cập nhật sân nhỏ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sân nhỏ
     * DELETE /api/owner/courts/{courtId}
     */
    public function destroy(Request $request, $courtId): JsonResponse
    {
        try {
            $user = $request->user();

            $court = Court::whereHas('venue', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })->where('id', $courtId)->first();

            if (!$court) {
                return response()->json([
                    'message' => 'Sân nhỏ không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            // Kiểm tra có booking chưa
            if ($court->bookings()->count() > 0) {
                return response()->json([
                    'message' => 'Không thể xóa sân nhỏ vì có đơn đặt chỗ'
                ], 400);
            }

            $court->delete();

            return response()->json([
                'message' => 'Xóa sân nhỏ thành công'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi xóa sân nhỏ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy khung giờ của sân nhỏ
     * GET /api/owner/courts/{courtId}/time-slots
     */
    public function getTimeSlots(Request $request, $courtId): JsonResponse
    {
        try {
            $user = $request->user();

            $court = Court::whereHas('venue', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })->where('id', $courtId)->first();

            if (!$court) {
                return response()->json([
                    'message' => 'Sân nhỏ không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            $timeSlots = $court->timeSlots()->get();

            return response()->json([
                'data' => $timeSlots
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy khung giờ',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
