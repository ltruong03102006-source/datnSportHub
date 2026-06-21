<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OwnerCourtController extends Controller
{
    // 1. TẠO SÂN CON MỚI
    public function store(Request $request, Venue $venue): JsonResponse
    {
        if ((int) $venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên sân này.'], 403);
        }

        if ($venue->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải được Admin duyệt cơ sở trước khi tạo sân.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courts')->where(function ($query) use ($venue) {
                    return $query->where('venue_id', $venue->id);
                })
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Vui lòng nhập tên sân.',
            'name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên sân này đã tồn tại. Vui lòng chọn tên khác.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        try {
            $court = $venue->courts()->create([
                'name' => $validated['name'],
                'status' => $validated['status'],
                'is_bookable_online' => true, 
            ]);

            return response()->json(['success' => true, 'message' => 'Đã thêm sân con thành công.', 'data' => $court], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo sân con. Vui lòng thử lại sau.'], 500);
        }
    }

    // 2. CẬP NHẬT THÔNG TIN SÂN CON
    public function update(Request $request, Court $court): JsonResponse
    {
        $court->load('venue');

        if ((int) $court->venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên sân này.'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courts')->where(function ($query) use ($court) {
                    return $query->where('venue_id', $court->venue_id);
                })->ignore($court->id)
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Vui lòng nhập tên sân.',
            'name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên sân này đã tồn tại. Vui lòng chọn tên khác.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        // LÁ CHẮN: Chặn bảo trì nếu đang có khách đặt lịch trong tương lai
        if ($validated['status'] === 'inactive' && $court->status === 'active') {
            $hasUpcomingBookings = \App\Models\Booking::where('court_id', $court->id)
                ->where('slot_date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($hasUpcomingBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: Không thể chuyển sang "Bảo trì"! Sân này đang có lịch đặt của khách trong tương lai. Vui lòng xử lý đơn (Hủy/Chờ đá xong) trước khi bảo trì.'
                ], 400); 
            }
        }

        try {
            $court->update([
                'name' => $validated['name'],
                'status' => $validated['status'],
                'is_bookable_online' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin sân con thành công.', 'data' => $court]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật. Vui lòng thử lại sau.'], 500);
        }
    }

    // 3. XÓA SÂN CON VĨNH VIỄN
    public function destroy(Court $court): JsonResponse
    {
        $court->load('venue');

        if ((int) $court->venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên sân này.'], 403);
        }

        if ($court->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: Không thể xóa! Sân này đã có dữ liệu khách hàng đặt lịch. Bạn chỉ có thể xóa các sân chưa từng có giao dịch.'
            ], 400); 
        }

        try {
            $court->timeSlots()->delete();
            $court->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa sân con thành công.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa sân lúc này.'], 500);
        }
    }

    // 4. SINH CA TỰ ĐỘNG (GHI ĐÈ THÔNG MINH)
    public function generateSlots(Request $request, Court $court): JsonResponse
    {
        $court->load('venue');
        
        if ((int) $court->venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên sân này.'], 403);
        }

        $validated = $request->validate([
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'duration' => 'required|integer|min:30|max:240', 
            'regular_price' => 'required|numeric|min:0',
            'peak_price' => 'required|numeric|min:0',
            'peak_start_time' => 'required|date_format:H:i',
            'peak_end_time' => 'required|date_format:H:i|after:peak_start_time',
        ], [
            'duration.min' => 'Thời lượng ca tối thiểu là 30 phút.',
            'duration.max' => 'Thời lượng ca tối đa không được vượt quá 240 phút (4 tiếng).',
            'peak_end_time.after' => 'Giờ kết thúc cao điểm phải sau giờ bắt đầu cao điểm.'
        ]);

        if (Carbon::parse($validated['open_time'])->gte(Carbon::parse($validated['close_time']))) {
            return response()->json(['success' => false, 'message' => 'Lỗi: Giờ mở cửa phải nhỏ hơn giờ đóng cửa!'], 422);
        }
        
        try {
            $openTime = Carbon::createFromFormat('H:i', $validated['open_time']);
            $closeTime = Carbon::createFromFormat('H:i', $validated['close_time']);
            $duration = (int) $validated['duration'];

            $totalAvailableMinutes = $openTime->diffInMinutes($closeTime);
            if ($duration > $totalAvailableMinutes) {
                return response()->json([
                    'success' => false,
                    'message' => "Lỗi: Thời lượng 1 ca ({$duration} phút) đang lớn hơn tổng thời gian bạn chọn ({$totalAvailableMinutes} phút). Vui lòng nhập lại!"
                ], 422); 
            }

            $createdCount = 0;
            $skippedCount = 0;
            $replacedCount = 0;
            $currentTime = $openTime->copy();

            $peakStart = Carbon::createFromFormat('H:i', $validated['peak_start_time']);
            $peakEnd = Carbon::createFromFormat('H:i', $validated['peak_end_time']);

            // Closure sinh giá tiền tự động
            $createPricesForSlot = function($slot) use ($validated, $peakStart, $peakEnd) {
                $slotStart = Carbon::createFromFormat('H:i:s', $slot->start_time);
                $isPeak = $slotStart->gte($peakStart) && $slotStart->lt($peakEnd);
                
                $price = $isPeak ? $validated['peak_price'] : $validated['regular_price'];
                $priceType = $isPeak ? 'peak' : 'normal'; 
                
                $pricesData = [];
                for ($day = 0; $day <= 6; $day++) {
                    $pricesData[] = [
                        'time_slot_id' => $slot->id,
                        'price' => $price,
                        'price_type' => $priceType,
                        'day_of_week' => $day,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                \App\Models\SlotPrice::insert($pricesData);
            };

            // Vòng lặp chia ca
            while ($currentTime->copy()->addMinutes($duration)->lte($closeTime)) {
                $startTimeStr = $currentTime->format('H:i:s');
                $endTime = $currentTime->copy()->addMinutes($duration);
                $endTimeStr = $endTime->format('H:i:s');

                $overlappingSlots = $court->timeSlots()
                    ->where('start_time', '<', $endTimeStr)
                    ->where('end_time', '>', $startTimeStr)
                    ->get();

                if ($overlappingSlots->isNotEmpty()) {
                    
                    // LÁ CHẮN PHP BẤT KHẢ XÂM PHẠM: Check Khách đặt
                    $hasBookings = \App\Models\Booking::where('court_id', $court->id)
                        ->where('slot_date', '>=', now()->toDateString()) 
                        ->whereIn('status', ['pending', 'confirmed']) 
                        ->where('start_time', '<', $endTimeStr)
                        ->where('end_time', '>', $startTimeStr)
                        ->exists();

                    if ($hasBookings) {
                        $skippedCount++;
                        $currentTime->addMinutes($duration);
                        continue; 
                    }

                    $exactMatch = $overlappingSlots->first(function($slot) use ($startTimeStr, $endTimeStr) {
                        return $slot->start_time === $startTimeStr && $slot->end_time === $endTimeStr;
                    });

                    if ($exactMatch && $overlappingSlots->count() === 1) {
                        $skippedCount++;
                    } else {
                        // Vượt qua lá chắn -> Xóa đè
                        $court->timeSlots()->whereIn('id', $overlappingSlots->pluck('id'))->delete();
                        
                        $newSlot = $court->timeSlots()->create([
                            'start_time' => $startTimeStr,
                            'end_time' => $endTimeStr,
                            'duration_minutes' => $duration
                        ]);
                        $createPricesForSlot($newSlot);
                        $replacedCount++;
                        $createdCount++;
                    }
                } else {
                    $newSlot = $court->timeSlots()->create([
                        'start_time' => $startTimeStr,
                        'end_time' => $endTimeStr,
                        'duration_minutes' => $duration
                    ]);
                    $createPricesForSlot($newSlot);
                    $createdCount++;
                }

                $currentTime->addMinutes($duration);
            }

            if ($createdCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Lỗi: Không thể sinh ca! Toàn bộ {$skippedCount} ca bạn muốn tạo đều bị vướng dữ liệu lịch đặt của khách hàng."
                ], 400); 
            }

            $msg = "Tạo thành công {$createdCount} ca.";
            if ($replacedCount > 0) $msg .= " (Đã gộp/ghi đè {$replacedCount} khung giờ cũ).";
            if ($skippedCount > 0) $msg .= " (Bỏ qua {$skippedCount} ca do vướng đơn đặt sân).";

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi trong quá trình sinh ca. Vui lòng thử lại.'], 500);
        }
    }

    // 5. THÊM CA LẺ THỦ CÔNG
    public function storeSlot(Request $request, Court $court): JsonResponse
    {
        $court->load('venue');
        if ((int) $court->venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền.'], 403);
        }

        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'price' => 'required|numeric|min:0', 
            'price_type' => 'required|in:normal,peak' 
        ], [
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu!',
            'price.required' => 'Vui lòng nhập giá cho ca này.'
        ]);

        $startTimeStr = $validated['start_time'] . ':00';
        $endTimeStr = $validated['end_time'] . ':00';

        $overlappingSlots = $court->timeSlots()
            ->where('start_time', '<', $endTimeStr)
            ->where('end_time', '>', $startTimeStr)
            ->get();

        $message = 'Đã thêm ca mới thành công!';

        if ($overlappingSlots->isNotEmpty()) {
            
            // LÁ CHẮN PHP BẤT KHẢ XÂM PHẠM
            $hasBookings = \App\Models\Booking::where('court_id', $court->id)
                ->where('slot_date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_time', '<', $endTimeStr)
                ->where('end_time', '>', $startTimeStr)
                ->exists();

            if ($hasBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: Không thể ghi đè! Khung giờ này đang có khách đặt lịch trong tương lai. Bạn cần xử lý đơn của khách (Hủy/Chờ đá xong) trước khi thay đổi ca.'
                ], 400);
            }

            // An toàn 100%, thực hiện xóa đè
            $court->timeSlots()->whereIn('id', $overlappingSlots->pluck('id'))->delete();
            $message = 'Đã chèn ca mới và ghi đè (xóa) ' . $overlappingSlots->count() . ' ca cũ bị trùng giờ!';
        }

        $duration = \Carbon\Carbon::parse($startTimeStr)->diffInMinutes(\Carbon\Carbon::parse($endTimeStr));

        $newSlot = $court->timeSlots()->create([
            'start_time' => $startTimeStr,
            'end_time' => $endTimeStr,
            'duration_minutes' => $duration
        ]);

        $pricesData = [];
        for ($day = 0; $day <= 6; $day++) {
            $pricesData[] = [
                'time_slot_id' => $newSlot->id,
                'price' => $validated['price'],
                'price_type' => $validated['price_type'],
                'day_of_week' => $day,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        \App\Models\SlotPrice::insert($pricesData);

        return response()->json(['success' => true, 'message' => $message]);
    }

    // 6. KHÓA CA SÂN (BẢO TRÌ) - GỘP CA JSON THÔNG MINH
    public function lockSlot(Request $request, Court $court): JsonResponse
    {
        $court->load('venue');
        if ((int) $court->venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Lỗi xác thực quyền.'], 403);
        }

        $validated = $request->validate([
            'lock_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
            'selected_slots' => 'required|json', 
        ], [
            'lock_date.required' => 'Vui lòng chọn ngày khóa.',
            'lock_date.after_or_equal' => 'Không thể khóa sân ở ngày trong quá khứ.',
            'reason.required' => 'Vui lòng nhập lý do khóa sân.',
            'reason.max' => 'Lý do khóa sân không được vượt quá 255 ký tự.',
            'selected_slots.required' => 'Lỗi dữ liệu ca được chọn.'
        ]);

        $slots = json_decode($validated['selected_slots'], true);

        if (empty($slots)) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => ['selected_slots' => ['Vui lòng click chọn ít nhất 1 ca màu trắng trên sơ đồ để tiến hành khóa!']]
            ], 422);
        }

        $lockDate = \Carbon\Carbon::parse($validated['lock_date']);
        $isToday = $lockDate->isToday();
        $nowTime = now()->format('H:i:s');

        // BƯỚC 1: Validate tường tận từng ca được gửi lên
        foreach ($slots as $slot) {
            $start = $slot['start'];
            $end = $slot['end'];

            if ($isToday && $start < $nowTime) {
                return response()->json(['success' => false, 'message' => "Lỗi: Ca {$start} - {$end} đã trôi qua, không thể khóa!"], 400);
            }

            $hasBookings = \App\Models\Booking::where('court_id', $court->id)
                ->where('slot_date', $validated['lock_date'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();

            if ($hasBookings) {
                return response()->json(['success' => false, 'message' => "Lỗi: Ca {$start} - {$end} đang có khách đặt. Giao dịch bị từ chối."], 400);
            }
        }

        // BƯỚC 2: THUẬT TOÁN GỘP CA THÔNG MINH
        usort($slots, function($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $mergedLocks = [];
        $currentLock = null;

        foreach ($slots as $slot) {
            if (!$currentLock) {
                $currentLock = $slot;
            } else {
                if ($currentLock['end'] === $slot['start']) {
                    $currentLock['end'] = $slot['end'];
                } else {
                    $mergedLocks[] = $currentLock;
                    $currentLock = $slot;
                }
            }
        }
        if ($currentLock) {
            $mergedLocks[] = $currentLock;
        }

        // BƯỚC 3: Insert các dải đã gộp vào Database
        foreach ($mergedLocks as $mLock) {
            \App\Models\CourtLock::create([
                'court_id' => $court->id,
                'lock_date' => $validated['lock_date'],
                'start_time' => $mLock['start'],
                'end_time' => $mLock['end'],
                'reason' => $validated['reason']
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Đã khóa ' . count($slots) . ' ca (Gộp thành '.count($mergedLocks).' dải bảo trì) thành công!']);
    }

    // 7. MỞ KHÓA SÂN BẢO TRÌ
    public function unlockSlot(Request $request, \App\Models\CourtLock $lock): JsonResponse
    {
        if ((int) $lock->court->venue->owner_id !== (int) Auth::id()) {
             return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }
        
        $lock->delete();
        return response()->json(['success' => true, 'message' => 'Đã mở khóa sân.']);
    }
}