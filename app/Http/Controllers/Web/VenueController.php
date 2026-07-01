<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Sport;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function nearbyPage(): View
    {
        $sports = Sport::all();
        return view('venues.nearby', [
            'sports' => $sports
        ]);
    }

    public function show(int $id): View
    {
        // --- 1. LOGIC LƯU SESSION SÂN ĐÃ XEM ---
        $recentlyViewed = session()->get('recently_viewed', []);
        
        // Xóa ID nếu đã tồn tại để tránh trùng lặp
        $recentlyViewed = array_diff($recentlyViewed, [$id]);
        
        // Đẩy ID sân vừa xem lên đầu danh sách
        array_unshift($recentlyViewed, $id);
        
        // Chỉ giữ lại tối đa 8 sân gần nhất cho nhẹ Session
        $recentlyViewed = array_slice($recentlyViewed, 0, 8);
        
        session()->put('recently_viewed', $recentlyViewed);
        // ----------------------------------------
        $venue = Venue::with([
            'sport',
            'ownerRegistration',
            // Lọc: Chỉ lấy các sân con đang hoạt động và cho đặt online
            'courts' => function ($query) {
                $query->where('status', 'active')
                      ->where('is_bookable_online', true);
            },
            'packages' => fn ($query) => $query->where('status', 'active')->orderBy('type')->orderBy('duration'),
        ])->findOrFail($id);

        return view('venues.show', [
            'venue' => $venue,
            'ownerPhone' => $venue->ownerRegistration?->phone, 
        ]);
    }
}
