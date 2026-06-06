<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAvailabilityRequest;
use App\Http\Resources\SlotAvailabilityResource;
use App\Models\Court;
use App\Services\AvailabilityService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class CourtAvailabilityController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService,
    ) {
    }

    public function show(Court $courtId, GetAvailabilityRequest $request): AnonymousResourceCollection
    {
        $date = Carbon::createFromFormat('Y-m-d', $request->validated('date'));

        // Đổi $court thành $courtId ở đây nhé
        $availability = $this->availabilityService->getAvailability($courtId, $date);

        return SlotAvailabilityResource::collection($availability);
    }
}
