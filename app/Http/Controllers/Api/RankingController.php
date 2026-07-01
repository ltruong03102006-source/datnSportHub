<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VenueRankingService;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller
{
    public function __construct(private readonly VenueRankingService $rankingService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Xếp hạng cơ sở',
            'data' => $this->rankingService->getRankings(),
        ]);
    }
}
