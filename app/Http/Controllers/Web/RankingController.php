<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\VenueRankingService;
use Illuminate\Contracts\View\View;

class RankingController extends Controller
{
    public function __construct(private readonly VenueRankingService $rankingService)
    {
    }

    public function index(): View
    {
        return view('rankings.index', $this->rankingService->getRankings());
    }
}
