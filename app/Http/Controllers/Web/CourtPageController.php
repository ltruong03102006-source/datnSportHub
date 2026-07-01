<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\Venue; // Gọi model Venue
use App\Services\VenueRankingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourtPageController extends Controller
{
    public function index(VenueRankingService $rankingService): View
    {
        $sports = Sport::withActiveCourtsCount()
            ->orderBy('name')
            ->get()
            ->map(fn (Sport $sport) => [
                'id' => $sport->id,
                'name' => $sport->name,
                'icon' => $sport->icon,
                'slug' => $sport->slug,
                'courts_count' => (int) $sport->courts_count,
            ])
            ->values();

        // --- BẠN BỊ THIẾU ĐOẠN NÀY: LẤY DỮ LIỆU TỪ SESSION ---
        $recentSearches = session()->get('recent_searches', []);
        
        $recentlyViewedIds = session()->get('recently_viewed', []);
        $recentlyViewed = collect();
        
        if (!empty($recentlyViewedIds)) {
            // Lấy các sân ra và Sắp xếp đúng theo thứ tự mảng Session (Mới nhất lên đầu)
            $idsOrdered = implode(',', $recentlyViewedIds);
            $recentlyViewed = Venue::whereIn('id', $recentlyViewedIds)
                ->orderByRaw("FIELD(id, {$idsOrdered})")
                ->get();
        }
        // -----------------------------------------------------

        return view('courts.index', [
            'sports' => $sports,
            'defaultSport' => 'all',
            'totalCourts' => $sports->sum('courts_count'),
            'featured' => $rankingService->getRankings(4)['featured'],
            // --- BẠN BỊ THIẾU DÒNG NÀY: TRUYỀN DỮ LIỆU RA VIEW ---
            'recentSearches' => $recentSearches,    
            'recentlyViewed' => $recentlyViewed,    
            // -----------------------------------------------------
        ]);
    }

    public function saveSearch(Request $request): JsonResponse
    {
        $query = trim($request->input('query'));
        if (!empty($query)) {
            $searches = session()->get('recent_searches', []);
            $searches = array_diff($searches, [$query]);
            array_unshift($searches, $query);
            $searches = array_slice($searches, 0, 5); // Giữ tối đa 5 từ khóa
            session()->put('recent_searches', $searches);
        }
        return response()->json(['success' => true]);
    }
}