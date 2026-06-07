<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function show(int $id): View
    {
        $venue = Venue::with([
            'sport',
            'courts',
            'ownerRegistration'
        ])->findOrFail($id);

        return view('venues.show', [
            'venue' => $venue,
            // ĐÃ BỎ HÀM maskPhone(), truyền trực tiếp dữ liệu gốc
            'ownerPhone' => $venue->ownerRegistration?->phone, 
        ]);
    }
}