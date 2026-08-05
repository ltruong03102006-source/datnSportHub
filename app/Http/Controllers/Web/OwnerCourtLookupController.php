<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Trả danh sách sân con của một cơ sở để đổ vào dropdown thống kê.
 */
class OwnerCourtLookupController extends Controller
{
    public function index(Venue $venue): JsonResponse
    {
        // Chỉ cho phép xem sân con thuộc cơ sở của chính chủ sân đang đăng nhập
        abort_unless($venue->owner_id === Auth::id(), 403);

        $courts = Court::forVenues([$venue->id])
            ->orderedByName()
            ->get(['id', 'name', 'status'])
            ->map(fn (Court $court) => [
                'id' => $court->id,
                'name' => $court->name,
                'status' => $court->status,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Danh sách sân con',
            'data' => $courts,
            'meta' => ['total' => $courts->count()],
        ]);
    }
}
