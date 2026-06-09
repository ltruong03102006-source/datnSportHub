<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;

class SportController extends Controller
{
    public function index(): JsonResponse
    {
        $sports = Sport::withActiveCourtsCount()
            ->orderBy('name')
            ->get()
            ->map(fn (Sport $sport) => [
                'id' => $sport->id,
                'name' => $sport->name,
                'slug' => $sport->slug,
                'icon' => $sport->icon,
                'courts_count' => (int) $sport->courts_count,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Danh sách loại môn thể thao',
            'data' => $sports,
            'meta' => [
                'total_sports' => $sports->count(),
                'total_courts' => $sports->sum('courts_count'),
            ],
        ]);
    }
}
