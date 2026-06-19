<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAvailabilityRequest;
// use App\Http\Resources\SlotAvailabilityResource; <-- ĐÃ BỎ KẺ GÁC CỔNG RESOURCE
use App\Models\Court;
use App\Models\CourtLock;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse; // <-- Dùng JSON trả thẳng
use Illuminate\Support\Carbon;

class CourtAvailabilityController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService,
    ) {
    }

    public function show(Court $courtId, GetAvailabilityRequest $request): JsonResponse
    {
        $date = Carbon::createFromFormat('Y-m-d', $request->validated('date'));
        $availability = $this->availabilityService->getAvailability($courtId, $date);

        $now = now();
        $isToday = $date->isToday();
        $isPastDate = $date->copy()->startOfDay()->isBefore($now->copy()->startOfDay());
        
        $locks = CourtLock::where('court_id', $courtId->id)
            ->where('lock_date', $date->toDateString())
            ->get();

        $sortedAvailability = collect($availability)->sortBy('start_time')->values()->map(function ($slot) use ($isToday, $isPastDate, $now, $locks) {
            
            // Ép kiểu an toàn về Array để có thể dễ dàng chèn thêm dữ liệu
            $item = (is_object($slot) && method_exists($slot, 'toArray')) ? $slot->toArray() : (array) $slot;
            $startTimeStr = date('H:i:s', strtotime($item['start_time']));

            $isOriginalAvailable = $item['is_available']; // Trạng thái gốc để biết có ai đặt chưa

            // Reset các cờ phân loại
            $item['is_past'] = false;
            $item['is_locked_by_owner'] = false;
            $item['lock_reason'] = null;

            // TRƯỜNG HỢP 1: Đã qua giờ (Quá khứ) -> Mờ xám, không cho click
            if ($isPastDate || ($isToday && $startTimeStr < $now->format('H:i:s'))) {
                $item['is_available'] = false;
                $item['is_past'] = true;
                return $item; 
            }

            // TRƯỜNG HỢP 2: Khách đã đặt -> Hiện màu ĐỎ, không cho click
            if (!$isOriginalAvailable) {
                $item['is_available'] = false;
                $item['is_booked'] = true; 
                return $item;
            }

            // TRƯỜNG HỢP 3: Chủ sân tự khóa -> Click được, hiện Modal lý do
            $lock = $locks->first(function (CourtLock $l) use ($startTimeStr) {
                return $startTimeStr >= $l->start_time && $startTimeStr < $l->end_time;
            });

            if ($lock) {
                $item['is_available'] = false;
                $item['is_locked_by_owner'] = true;
                $item['lock_reason'] = $lock->reason; // Gắn lý do vào đây
            }

            return $item;
        });

        // Trả trực tiếp array ra JSON để không bị mất biến lock_reason
        return response()->json(['data' => $sortedAvailability]);
    }
}