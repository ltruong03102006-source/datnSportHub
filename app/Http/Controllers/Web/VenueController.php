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
        $venue = Venue::with([
            'sport',
            'ownerRegistration',
            // Lọc: Chỉ lấy các sân con đang hoạt động và cho đặt online
            'courts' => function ($query) {
                $query->where('status', 'active')
                      ->where('is_bookable_online', true);
            }
        ])->findOrFail($id);

        return view('venues.show', [
            'venue' => $venue,
            'ownerPhone' => $venue->ownerRegistration?->phone, 
        ]);
    }
}