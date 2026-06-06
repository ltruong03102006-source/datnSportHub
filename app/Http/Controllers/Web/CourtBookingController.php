<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Contracts\View\View;

class CourtBookingController extends Controller
{
    public function show(Court $court): View
    {
        $court->load([
            'venue' => fn($query) => $query->select('id', 'name', 'address', 'sport_id', 'banner'),
            'venue.sport' => fn($query) => $query->select('id', 'name'),
            'timeSlots' => fn($query) => $query->select('id', 'court_id', 'start_time', 'end_time', 'duration_minutes'),
        ]);

        return view('courts.booking', [
            'court' => $court,
            'bannerUrl' => $court->venue?->banner ?? '/images/default-court.jpg',
        ]);
    }
}
