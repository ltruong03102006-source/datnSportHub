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
            'ownerPhone' => maskPhone($venue->ownerRegistration?->phone),
        ]);
    }
}
