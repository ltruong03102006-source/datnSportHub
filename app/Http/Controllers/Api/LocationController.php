<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function provinces(): JsonResponse
    {
        $provinces = Province::orderedByName()
            ->get(['code', 'name'])
            ->map(fn (Province $p) => [
                'code' => $p->code,
                'name' => $p->name,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Danh sách tỉnh/thành phố',
            'data' => $provinces,
            'meta' => ['total' => $provinces->count()],
        ]);
    }

    public function wards(string $provinceCode): JsonResponse
    {
        $wards = Ward::forProvince($provinceCode)
            ->get(['code', 'name'])
            ->map(fn (Ward $w) => [
                'code' => $w->code,
                'name' => $w->name,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Danh sách phường/xã',
            'data' => $wards,
            'meta' => ['total' => $wards->count()],
        ]);
    }
}
