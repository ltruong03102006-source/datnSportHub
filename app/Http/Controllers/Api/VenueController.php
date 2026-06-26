<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenueController extends Controller
{
    public function nearby(Request $request): JsonResponse
    {
        try {
            $lat = $request->query('lat');
            $lng = $request->query('lng');
            $radius = $request->query('radius'); // in km, e.g. 1, 3, 5, 10
            $sportId = $request->query('sport_id');
            $search = trim((string) $request->query('q', ''));

            $query = Venue::query()
                ->select('venues.*')
                ->with(['sport'])
                ->withCount(['courts' => function ($q) {
                    $q->where('status', 'active');
                }])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->whereIn('venues.status', ['active', 'approved'])
                ->whereExists(function ($query) {
                    // Chỉ hiển thị các Cơ sở có ít nhất 1 Sân con đang hoạt động
                    $query->select(DB::raw(1))
                          ->from('courts')
                          ->whereColumn('courts.venue_id', 'venues.id')
                          ->where('courts.status', 'active');
                });

            // Lọc theo từ khóa tìm kiếm nếu có
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('venues.name', 'like', "%{$search}%")
                      ->orWhere('venues.address', 'like', "%{$search}%");
                });
            }

            // Nếu người dùng cung cấp tọa độ, tính toán khoảng cách
            if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
                $lat = (float) $lat;
                $lng = (float) $lng;

                $query->selectRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
                    [$lat, $lng, $lat]
                );

                if ($radius !== null && $radius !== '' && $radius !== 'all') {
                    $radius = (float) $radius;
                    $query->having('distance', '<=', $radius);
                }

                $query->orderBy('distance', 'asc');
            } else {
                $query->orderBy('venues.name', 'asc');
            }

            if ($sportId !== null && $sportId !== '' && $sportId !== 'all') {
                $query->where('venues.sport_id', (int) $sportId);
            }

            $venues = $query->get()->map(function (Venue $venue) {
                $phone = $venue->phone ?: ($venue->ownerRegistration?->phone ?? null);
                
                $banner = $venue->banner;
                if ($banner && !str_starts_with($banner, 'http://') && !str_starts_with($banner, 'https://')) {
                    $banner = asset('storage/' . $banner);
                }

                return [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                    'lat' => (float) $venue->lat,
                    'lng' => (float) $venue->lng,
                    'banner' => $banner,
                    'phone' => $phone,
                    'open_hours' => $venue->open_hours,
                    'close_hours' => $venue->close_hours,
                    'sport' => [
                        'id' => $venue->sport->id,
                        'name' => $venue->sport->name,
                        'icon' => $venue->sport->icon,
                        'slug' => $venue->sport->slug,
                    ],
                    'courts_count' => $venue->courts_count ?? 0,
                    'distance' => isset($venue->distance) ? round((float) $venue->distance, 2) : null,
                    'reviews_avg_rating' => isset($venue->reviews_avg_rating) ? round((float) $venue->reviews_avg_rating, 1) : null,
                    'reviews_count' => (int) ($venue->reviews_count ?? 0),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Danh sách cơ sở gần đây',
                'data' => $venues,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $venue = Venue::with([
                'sport',
                'courts',
                'ownerRegistration'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Venue detail',
                'data' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                    'description' => $venue->description,
                    'banner' => $venue->banner ? asset('storage/' . $venue->banner) : null,
                    'status' => $venue->status,
                    'lat' => $venue->lat,
                    'lng' => $venue->lng,
                    'sport' => [
                        'id' => $venue->sport->id,
                        'name' => $venue->sport->name,
                        'icon' => $venue->sport->icon,
                        'slug' => $venue->sport->slug,
                    ],
                    'owner_phone' => maskPhone($venue->ownerRegistration?->phone),
                    'courts' => $venue->courts->map(fn ($court) => [
                        'id' => $court->id,
                        'name' => $court->name,
                        'status' => $court->status,
                        'is_bookable_online' => $court->is_bookable_online,
                    ]),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'data' => null,
            ], 500);
        }
    }
}
