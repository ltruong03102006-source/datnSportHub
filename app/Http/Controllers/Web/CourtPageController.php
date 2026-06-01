<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Contracts\View\View;

class CourtPageController extends Controller
{
    public function index(): View
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

        return view('courts.index', [
            'sports' => $sports,
            'defaultSport' => 'all',
            'totalCourts' => $sports->sum('courts_count'),
        ]);
    }
}
