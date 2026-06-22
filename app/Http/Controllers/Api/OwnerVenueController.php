<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Models\Venue;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
    public function store(StoreVenueRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $bannerPath = $request->file('banner')->store('venues', 'public');

        $venueData = [
            'owner_id' => $request->user()->id,
            'sport_id' => $validated['sport_id'],
            'name' => $validated['name'],
            'address' => $validated['address'],
            'description' => $validated['description'] ?? null,
            'banner' => $bannerPath,
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'status' => 'pending',
        ];

        if (Schema::hasColumn('venues', 'phone') && isset($validated['phone'])) {
            $venueData['phone'] = $validated['phone'];
        }
        if (Schema::hasColumn('venues', 'email') && isset($validated['email'])) {
            $venueData['email'] = $validated['email'];
        }
        if (Schema::hasColumn('venues', 'open_hours') && isset($validated['open_hours'])) {
            $venueData['open_hours'] = $validated['open_hours'];
        }
        if (Schema::hasColumn('venues', 'close_hours') && isset($validated['close_hours'])) {
            $venueData['close_hours'] = $validated['close_hours'];
        }
        if (Schema::hasColumn('venues', 'google_maps_address') && isset($validated['google_maps_address'])) {
            $venueData['google_maps_address'] = $validated['google_maps_address'];
        }

        $venue = Venue::create($venueData);

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('venues/gallery', 'public');
                $venue->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        if (
            Schema::hasTable('venue_legal_documents')
            && $request->hasFile('citizen_front_image')
            && $request->hasFile('citizen_back_image')
            && $request->hasFile('business_license_file')
        ) {
            $venue->legalDocument()->create([
                'owner_name' => $validated['owner_name'] ?? null,
                'citizen_id' => $validated['citizen_id'] ?? null,
                'business_license_number' => $validated['business_license_number'] ?? null,
                'address' => $validated['address'],
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_account_holder' => $validated['bank_account_holder'] ?? null,
                'citizen_front_image' => $request->file('citizen_front_image')->store('venue-documents', 'public'),
                'citizen_back_image' => $request->file('citizen_back_image')->store('venue-documents', 'public'),
                'business_license_file' => $request->file('business_license_file')->store('venue-documents', 'public'),
                'rental_contract_file' => $request->hasFile('rental_contract_file')
                    ? $request->file('rental_contract_file')->store('venue-documents', 'public')
                    : null,
                'land_certificate_file' => $request->hasFile('land_certificate_file')
                    ? $request->file('land_certificate_file')->store('venue-documents', 'public')
                    : null,
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'message' => 'Venue created successfully',
            'data' => [
                'id' => $venue->id,
                'name' => $venue->name,
                'status' => $venue->status,
                'banner_url' => $bannerPath ? asset('storage/' . $bannerPath) : null,
            ],
        ], 201);
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
