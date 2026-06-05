<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourtController extends Controller
{
    public function indexBySport(Request $request, int $sportId): JsonResponse
    {
        return $this->courtListResponse($request, $sportId, 'Danh sách sân theo môn thể thao');
    }

    public function index(Request $request): JsonResponse
    {
        return $this->courtListResponse($request, null, 'Danh sách sân');
    }

    public function search(Request $request): JsonResponse
    {
        $sportId = $request->integer('sport') ?: null;

        return $this->courtListResponse($request, $sportId, 'Kết quả tìm kiếm sân');
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

    private function courtListResponse(Request $request, ?int $sportId, string $message): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $query = Court::query()
            ->select('courts.*')
            ->with(['venue.sport', 'venue.ownerRegistration'])
            ->join('venues', 'venues.id', '=', 'courts.venue_id')
            ->join('sports', 'sports.id', '=', 'venues.sport_id')
            ->where('courts.status', 'active')
            ->where('venues.status', 'active')
            ->when($sportId !== null, fn (Builder $query) => $query->where('venues.sport_id', $sportId));

        $this->applySearch($query, $search);
        $this->applySorting($query, $search);

        $paginator = $query->paginate(15)->withQueryString();
        $payload = $paginator->getCollection()
            ->map(fn (Court $court) => $this->formatCourt($court, false));

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
                    '(MATCH(courts.name) AGAINST (? IN BOOLEAN MODE) + MATCH(venues.name, venues.address) AGAINST (? IN BOOLEAN MODE)) as search_score',
                    [$term, $term]
                )
                ->where(function (Builder $where) use ($search, $term) {
                    $where
                        ->whereRaw('MATCH(courts.name) AGAINST (? IN BOOLEAN MODE)', [$term])
                        ->orWhereRaw('MATCH(venues.name, venues.address) AGAINST (? IN BOOLEAN MODE)', [$term])
                        ->orWhere('courts.name', 'like', "%{$search}%")
                        ->orWhere('venues.name', 'like', "%{$search}%")
                        ->orWhere('venues.address', 'like', "%{$search}%")
                        ->orWhere('sports.name', 'like', "%{$search}%")
                        ->orWhere('sports.slug', 'like', "%{$search}%");
                });

            return;
        }

        $query->where(function (Builder $where) use ($search) {
            $where
                ->where('courts.name', 'like', "%{$search}%")
                ->orWhere('venues.name', 'like', "%{$search}%")
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
            ->orderBy('courts.name')
            ->orderByDesc('courts.created_at');
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
            'lat' => $venue->lat ?? null,
            'lng' => $venue->lng ?? null,
            'phone_hidden' => $this->maskPhone($phone),
            'phone_full' => $includeFullPhone ? $phone : null,
        ];
    }

    private function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $visible = mb_substr($phone, 0, 6);

        return $visible . '***';
    }

    private function countsPerSport(): array
    {
        $rows = DB::table('courts')
            ->join('venues', 'venues.id', '=', 'courts.venue_id')
            ->join('sports', 'sports.id', '=', 'venues.sport_id')
            ->where('courts.status', 'active')
            ->where('venues.status', 'active')
            ->select('sports.id as sport_id', DB::raw('count(courts.id) as total'))
            ->groupBy('sports.id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->sport_id] = (int) $row->total;
        }

        return $result;
    }
}
