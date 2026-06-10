<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourtController extends Controller
{
    public function indexBySport(Request $request, int $sportId): JsonResponse
    {
        return $this->venueListResponse($request, $sportId, 'Danh sách cơ sở theo môn thể thao');
    }

    public function index(Request $request): JsonResponse
    {
        return $this->venueListResponse($request, null, 'Danh sách cơ sở');
    }

    public function search(Request $request): JsonResponse
    {
        $sportId = $request->integer('sport') ?: null;

        return $this->venueListResponse($request, $sportId, 'Kết quả tìm kiếm cơ sở');
    }

    public function show(int $courtId): JsonResponse
    {
        // Hàm này giữ nguyên để phục vụ API lấy chi tiết Sân con (Task #08)
        $court = Court::with(['venue.sport', 'venue.ownerRegistration'])->findOrFail($courtId);

        return response()->json([
            'status' => 'success',
            'message' => 'Chi tiết sân',
            'data' => $this->formatCourt($court, true),
        ]);
    }

    private function venueListResponse(Request $request, ?int $sportId, string $message): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        
        // Chuyển từ query Court sang query Venue
        $query = Venue::query()
            ->select('venues.*')
            ->with(['sport'])
            ->withCount(['courts' => function ($q) {
                $q->where('status', 'active');
            }])
            ->join('sports', 'sports.id', '=', 'venues.sport_id')
            ->where('venues.status', 'active')
            ->whereExists(function ($query) {
                // Chỉ hiển thị các Cơ sở có ít nhất 1 Sân con đang hoạt động
                $query->select(DB::raw(1))
                      ->from('courts')
                      ->whereColumn('courts.venue_id', 'venues.id')
                      ->where('courts.status', 'active');
            })
            ->when($sportId !== null, fn (Builder $query) => $query->where('venues.sport_id', $sportId));

        $this->applySearch($query, $search);
        $this->applySorting($query, $search);

        $paginator = $query->paginate(15)->withQueryString();
        $payload = $paginator->getCollection()
            ->map(fn (Venue $venue) => $this->formatVenue($venue));

        $response = [
            'status' => 'success',
            'message' => $message,
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

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        if ($this->supportsFullTextSearch()) {
            $term = $this->toBooleanFullTextTerm($search);

            $query
                ->selectRaw(
                    '(MATCH(venues.name, venues.address) AGAINST (? IN BOOLEAN MODE)) as search_score',
                    [$term]
                )
                ->where(function (Builder $where) use ($search, $term) {
                    $where
                        ->whereRaw('MATCH(venues.name, venues.address) AGAINST (? IN BOOLEAN MODE)', [$term])
                        ->orWhere('venues.name', 'like', "%{$search}%")
                        ->orWhere('venues.address', 'like', "%{$search}%")
                        ->orWhere('sports.name', 'like', "%{$search}%")
                        ->orWhere('sports.slug', 'like', "%{$search}%");
                });

            return;
        }

        $query->where(function (Builder $where) use ($search) {
            $where
                ->where('venues.name', 'like', "%{$search}%")
                ->orWhere('venues.address', 'like', "%{$search}%")
                ->orWhere('sports.name', 'like', "%{$search}%")
                ->orWhere('sports.slug', 'like', "%{$search}%");
        });
    }

    private function applySorting(Builder $query, string $search): void
    {
        if ($search !== '' && $this->supportsFullTextSearch()) {
            $query->orderByDesc('search_score');
        }

        $query
            ->orderBy('venues.name')
            ->orderByDesc('venues.created_at');
    }

    private function supportsFullTextSearch(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function toBooleanFullTextTerm(string $search): string
    {
        $tokens = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_map(
            fn (string $token) => trim(str_replace(['+', '-', '~', '*', '"', '<', '>', '(', ')'], '', $token)),
            $tokens
        );
        $tokens = array_values(array_filter($tokens, fn (string $token) => mb_strlen($token) >= 2));

        if ($tokens === []) {
            return $search;
        }

        return collect($tokens)
            ->map(fn (string $token) => "+{$token}*")
            ->implode(' ');
    }

    private function formatCourt(Court $court, bool $includeFullPhone = false): array
    {
        $venue = $court->venue;
        $phone = $venue?->owner_phone;
        $thumbnail = $venue?->banner ? asset('storage/' . $venue->banner) : null;

        return [
            'court_id' => $court->id,
            'venue_id' => $venue?->id,
            'thumbnail' => $thumbnail,
            'name' => $venue->name ?? $court->name,
            'court_name' => $court->name,
            'sport_id' => $venue->sport?->id,
            'sport_name' => $venue->sport?->name,
            'address' => $venue->address ?? null,
            'lat' => $venue->lat ?? null,
            'lng' => $venue->lng ?? null,
            'phone' => $phone, // Đã đổi thành phone và trả về SĐT thật
            'phone_full' => $includeFullPhone ? $phone : null,
        ];
    }

    private function formatVenue(Venue $venue): array
    {
        $phone = $venue->owner_phone ?? null;

        return [
            'venue_id' => $venue->id,
            'thumbnail' => $venue->banner ? asset('storage/' . $venue->banner) : null,
            'name' => $venue->name,
            'sport_id' => $venue->sport_id,
            'sport_name' => $venue->sport?->name,
            'address' => $venue->address,
            'lat' => $venue->lat,
            'lng' => $venue->lng,
            'phone' => $phone, // Đã đổi thành phone và trả về SĐT thật
            'courts_count' => $venue->courts_count ?? 0,
        ];
    }

    private function countsPerSport(): array
    {
        // Đếm số lượng Cơ sở theo từng môn thể thao
        $rows = DB::table('venues')
            ->join('sports', 'sports.id', '=', 'venues.sport_id')
            ->where('venues.status', 'active')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('courts')
                      ->whereColumn('courts.venue_id', 'venues.id')
                      ->where('courts.status', 'active');
            })
            ->select('sports.id as sport_id', DB::raw('count(venues.id) as total'))
            ->groupBy('sports.id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->sport_id] = (int) $row->total;
        }

        return $result;
    }
}