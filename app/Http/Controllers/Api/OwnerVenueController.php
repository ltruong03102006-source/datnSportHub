<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OwnerVenueController extends Controller
{
    /**
     * Lấy danh sách sân của chủ sân
     * GET /api/owner/venues
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $venues = Venue::where('owner_id', $user->id)
                ->with('sport', 'courts')
                ->get();

            return response()->json([
                'message' => 'Danh sách sân của bạn',
                'data' => $venues
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách sân',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo sân mới
     * POST /api/owner/venues
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'sport_id' => 'required|exists:sports,id',
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'description' => 'nullable|string',
                'banner' => 'nullable|image|max:2048',
            ]);

            // Xử lý upload banner
            $banner = null;
            if ($request->hasFile('banner')) {
                $banner = $request->file('banner')->store('venues', 'public');
            }

            $user = $request->user();

            $venue = Venue::create([
                'owner_id' => $user->id,
                'sport_id' => $validated['sport_id'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'description' => $validated['description'] ?? null,
                'banner' => $banner,
                'status' => 'pending', // Sân mới cần duyệt
            ]);

            return response()->json([
                'message' => 'Tạo sân thành công. Sân của bạn đang chờ duyệt.',
                'data' => $venue->load('sport')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo sân',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một sân
     * GET /api/owner/venues/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $venue = Venue::where('owner_id', $user->id)
                ->where('id', $id)
                ->with('sport', 'courts')
                ->first();

            if (!$venue) {
                return response()->json([
                    'message' => 'Sân không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            return response()->json([
                'data' => $venue
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy chi tiết sân',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật sân
     * PUT /api/owner/venues/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $venue = Venue::where('owner_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$venue) {
                return response()->json([
                    'message' => 'Sân không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            $validated = $request->validate([
                'sport_id' => 'sometimes|exists:sports,id',
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string',
                'lat' => 'sometimes|numeric',
                'lng' => 'sometimes|numeric',
                'description' => 'nullable|string',
                'banner' => 'nullable|image|max:2048',
            ]);

            // Xử lý upload banner mới
            if ($request->hasFile('banner')) {
                $banner = $request->file('banner')->store('venues', 'public');
                $validated['banner'] = $banner;
            }

            $venue->update($validated);

            return response()->json([
                'message' => 'Cập nhật sân thành công',
                'data' => $venue->load('sport')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi cập nhật sân',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sân
     * DELETE /api/owner/venues/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $venue = Venue::where('owner_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$venue) {
                return response()->json([
                    'message' => 'Sân không được tìm thấy hoặc bạn không có quyền'
                ], 404);
            }

            // Kiểm tra có court chưa
            if ($venue->courts()->count() > 0) {
                return response()->json([
                    'message' => 'Không thể xóa sân vì có sân nhỏ đang tồn tại'
                ], 400);
            }

            $venue->delete();

            return response()->json([
                'message' => 'Xóa sân thành công'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi xóa sân',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách các loại thể thao (cho dropdown)
     * GET /api/owner/sports
     */
    public function getSports(): JsonResponse
    {
        try {
            $sports = Sport::all();

            return response()->json([
                'data' => $sports
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách thể thao',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
