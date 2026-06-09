<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerVenueController extends Controller
{
    public function index(): View
    {
        return view('owner.venues.index');
    }

    public function create(): View
    {
        $sports = Sport::query()->orderBy('name')->get();

        return view('owner.venues.create', compact('sports'));
    }

    public function store(StoreVenueRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $bannerPath = null;

        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('venues', 'public');
        }

        Venue::create([
            'owner_id' => Auth::id(),
            'sport_id' => $validated['sport_id'],
            'name' => $validated['name'],
            'address' => $validated['address'],
            'description' => $validated['description'] ?? null,
            'banner' => $bannerPath,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('owner.web.venues.create')
            ->with('success', 'Venue created successfully');
    }
}
