<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourtController extends Controller
{
    public function indexBySport(Request $request, int $sportId): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $query = Court::query()
            ->select('courts.*')
            ->with(['venue.sport', 'venue.ownerRegistration'])
            ->join('venues', 'venues.id', '=', 'courts.venue_id')
            ->where('venues.sport_id', $sportId)
            ->where('venues.status', 'active')
            ->where('courts.status', 'active');

        // Full-text style search on court name, venue name or address
        if ($search !== '') {
            $query->where(function ($where) use ($search) {
                $where->where('courts.name', 'like', "%{$search}%")
                    ->orWhere('venues.name', 'like', "%{$search}%")
                    ->orWhere('venues.address', 'like', "%{$search}%");
            });
        }

        // Default sort: newest first
        $paginator = $query
            ->orderBy('courts.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $payload = $paginator->getCollection()->map(function (Court $court) {
            return $this->formatCourt($court, false);
        });

        $paginator->setCollection($payload);

        // Sidebar counts: active courts per sport
        $counts = $this->countsPerSport();

        $response = [
            'status' => 'success',
            'message' => 'Danh sách sân theo môn thể thao',
            'data' => $payload,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'counts' => $counts,
        ];

        if ($paginator->total() === 0) {
            $response['empty'] = [
                'message' => 'Không tìm thấy kết quả phù hợp.',
                'suggestion' => 'Thử thay đổi từ khóa hoặc bỏ lọc môn thể thao.',
            ];
        }

        return response()->json($response);
    }

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $paginator = Court::query()
            ->where('status', 'active')
            ->whereHas('venue', fn ($venue) => $venue->where('status', 'active'))
            ->when($search !== '', fn ($q) => $q->where(function ($where) use ($search) {
                $where->where('name', 'like', "%{$search}%")
                    ->orWhereHas('venue', fn ($venue) => $venue
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%"));
            }))
            ->with(['venue.sport', 'venue.ownerRegistration'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $payload = $paginator->getCollection()->map(function (Court $court) {
            return $this->formatCourt($court, false);
        });

        $paginator->setCollection($payload);

        $response = [
            'status' => 'success',
            'message' => 'Danh sách sân',
            'data' => $payload,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'counts' => $this->countsPerSport(),
        ];

        if ($paginator->total() === 0) {
            $response['empty'] = [
                'message' => 'Không tìm thấy kết quả phù hợp.',
                'suggestion' => 'Thử thay đổi từ khóa hoặc bỏ lọc môn thể thao.',
            ];
        }

        return response()->json($response);
    }

    public function show(int $courtId): JsonResponse
    {
        $court = Court::with(['venue.sport', 'venue.ownerRegistration'])->findOrFail($courtId);

        return response()->json([
            'status' => 'success',
            'message' => 'Chi tiết sân',
            'data' => $this->formatCourt($court, true),
        ]);
    }

    private function formatCourt(Court $court, bool $includeFullPhone = false): array
    {
        $venue = $court->venue;
        $phone = $venue?->owner_phone;
        $thumbnail = $venue?->banner ?? null;

        return [
            'court_id' => $court->id,
            'venue_id' => $venue?->id,
            'thumbnail' => $thumbnail,
            'name' => $venue->name ?? $court->name,
            'court_name' => $court->name,
            'sport_id' => $venue->sport?->id,
            'sport_name' => $venue->sport?->name,
            'address' => $venue->address ?? null,
            'phone_hidden' => $this->maskPhone($phone),
            'phone_full' => $includeFullPhone ? $phone : null,
        ];
    }

    private function maskPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // show first 6 chars then mask rest
        $visible = mb_substr($phone, 0, 6);

        return $visible . '***';
    }

    private function countsPerSport(): array
    {
        // returns [sport_id => count]
        $rows = DB::table('courts')
            ->join('venues', 'venues.id', '=', 'courts.venue_id')
            ->join('sports', 'sports.id', '=', 'venues.sport_id')
            ->where('courts.status', 'active')
            ->where('venues.status', 'active')
            ->select('sports.id as sport_id', DB::raw('count(courts.id) as total'))
            ->groupBy('sports.id')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $result[$r->sport_id] = (int) $r->total;
        }

        return $result;
    }
}
