<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;

class VenueController extends Controller
{
    public function show(int $id): JsonResponse
    {
        try {
            $venue = Venue::with([
                'sport',
                'courts',
                'ownerRegistration'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Venue detail',
                'data' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                    'description' => $venue->description,
                    'banner' => $venue->banner ? asset('storage/' . $venue->banner) : null,
                    'status' => $venue->status,
                    'lat' => $venue->lat,
                    'lng' => $venue->lng,
                    'sport' => [
                        'id' => $venue->sport->id,
                        'name' => $venue->sport->name,
                        'icon' => $venue->sport->icon,
                        'slug' => $venue->sport->slug,
                    ],
                    'owner_phone' => maskPhone($venue->ownerRegistration?->phone),
                    'courts' => $venue->courts->map(fn ($court) => [
                        'id' => $court->id,
                        'name' => $court->name,
                        'status' => $court->status,
                        'is_bookable_online' => $court->is_bookable_online,
                    ]),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'data' => null,
            ], 500);
        }
    }
}
