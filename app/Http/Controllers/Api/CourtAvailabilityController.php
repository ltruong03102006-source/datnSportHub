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

        // Lấy dữ liệu các ca trống từ Service
        $availability = $this->availabilityService->getAvailability($courtId, $date);

        // THÊM LOGIC SẮP XẾP Ở ĐÂY:
        // Chuyển kết quả thành Collection, dùng sortBy để sắp xếp theo giờ bắt đầu (start_time)
        // Lệnh ->values() rất quan trọng để reset lại index của mảng (từ 0, 1, 2...) giúp JSON trả về chuẩn array thay vì object.
        $sortedAvailability = collect($availability)->sortBy('start_time')->values();

        // Trả kết quả đã sắp xếp qua Resource
        return SlotAvailabilityResource::collection($sortedAvailability);
    }
}