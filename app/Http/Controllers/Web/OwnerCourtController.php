<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Thư viện quan trọng để check trùng lặp
use Carbon\Carbon;

class OwnerCourtController extends Controller
{
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
            'status' => 'required|in:active,inactive', // Đã sửa thành inactive
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
                'is_bookable_online' => true, // Mặc định luôn cho phép đặt online
            ]);

            return response()->json(['success' => true, 'message' => 'Đã thêm sân con thành công.', 'data' => $court], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo sân con. Vui lòng thử lại sau.'], 500);
        }
    }

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

        // LOGIC BẢO VỆ CHẶT CHẼ: Chặn bảo trì nếu đang có khách đặt lịch trong tương lai
        if ($validated['status'] === 'inactive' && $court->status === 'active') {
            // Lưu ý: Cần import \App\Models\Booking ở đầu file nếu chưa có
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
                'is_bookable_online' => true, // Luôn duy trì đặt online
            ]);

            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin sân con thành công.', 'data' => $court]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật. Vui lòng thử lại sau.'], 500);
        }
    }

    public function generateSlots(Request $request, Court $court): JsonResponse
    {
        $court->load('venue');
        
        if ((int) $court->venue->owner_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên sân này.'], 403);
        }

        $validated = $request->validate([
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'duration' => 'required|integer|min:30|max:240', // Đã thêm Validate hợp lý (30 - 240 phút)
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

            $createPricesForSlot = function($slot) use ($validated, $peakStart, $peakEnd) {
                $slotStart = Carbon::createFromFormat('H:i:s', $slot->start_time);
                
                // Kiểm tra xem start_time của ca có nằm lọt vào khoảng cao điểm không
                $isPeak = $slotStart->gte($peakStart) && $slotStart->lt($peakEnd);
                
                $price = $isPeak ? $validated['peak_price'] : $validated['regular_price'];
$priceType = $isPeak ? 'peak' : 'normal'; // <--- Đổi thành 'normal'
                
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

            while ($currentTime->copy()->addMinutes($duration)->lte($closeTime)) {
                $startTimeStr = $currentTime->format('H:i:s');
                $endTime = $currentTime->copy()->addMinutes($duration);
                $endTimeStr = $endTime->format('H:i:s');

                $overlappingSlots = $court->timeSlots()
                    ->where('start_time', '<', $endTimeStr)
                    ->where('end_time', '>', $startTimeStr)
                    ->get();

                if ($overlappingSlots->isNotEmpty()) {
                    
                    // --- CHỐT CHẶN MỚI DỰA TRÊN THỜI GIAN THỰC TẾ ---
                    $hasBookings = \App\Models\Booking::where('court_id', $court->id)
                        ->where('slot_date', '>=', now()->toDateString()) // Chỉ tính đơn từ hôm nay trở đi
                        ->whereIn('status', ['pending', 'confirmed']) // Chỉ tính đơn hợp lệ
                        ->where('start_time', '<', $endTimeStr)
                        ->where('end_time', '>', $startTimeStr)
                        ->exists();

                    if ($hasBookings) {
                        $skippedCount++;
                        $currentTime->addMinutes($duration);
                        continue; 
                    }
                    // --- KẾT THÚC CHỐT CHẶN ---

                    $exactMatch = $overlappingSlots->first(function($slot) use ($startTimeStr, $endTimeStr) {
                        return $slot->start_time === $startTimeStr && $slot->end_time === $endTimeStr;
                    });

                    if ($exactMatch && $overlappingSlots->count() === 1) {
                        $skippedCount++;
                    } else {
                        try {
                            $court->timeSlots()->whereIn('id', $overlappingSlots->pluck('id'))->delete();
                            
                            $newSlot = $court->timeSlots()->create([
                                'start_time' => $startTimeStr,
                                'end_time' => $endTimeStr,
                                'duration_minutes' => $duration
                            ]);
                            $createPricesForSlot($newSlot);
                            $replacedCount++;
                            $createdCount++;
                        } catch (\Exception $e) {
                            $skippedCount++;
                        }
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

            $msg = "Tạo thành công {$createdCount} ca.";
            if ($replacedCount > 0) $msg .= " (Đã gộp/ghi đè {$replacedCount} khung giờ cũ).";
            if ($skippedCount > 0) $msg .= " (Bỏ qua {$skippedCount} ca do trùng lặp hoặc đang có khách đặt).";

            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi trong quá trình sinh ca. Vui lòng thử lại.'], 500);
        }
    }
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
    // Hàm tạo ca thủ công (Tạo chay)
   // Hàm tạo ca thủ công (Tạo chay) - Phiên bản Ghi đè (Overwrite)
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
            // SỬA CHỮ 'regular' THÀNH 'normal' Ở DÒNG DƯỚI ĐÂY
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
            $hasBookings = \App\Models\Booking::where('court_id', $court->id)
                ->where('slot_date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_time', '<', $endTimeStr)
                ->where('end_time', '>', $startTimeStr)
                ->exists();

            if ($hasBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: Không thể ghi đè! Khung giờ này (hoặc một phần thời gian) đang có khách đặt.'
                ], 400);
            }

            $court->timeSlots()->whereIn('id', $overlappingSlots->pluck('id'))->delete();
            $message = 'Đã chèn ca mới và ghi đè (xóa) ' . $overlappingSlots->count() . ' ca cũ bị trùng giờ!';
        }

        $duration = Carbon::parse($startTimeStr)->diffInMinutes(Carbon::parse($endTimeStr));

        // 1. Tạo ca (TimeSlot)
        $newSlot = $court->timeSlots()->create([
            'start_time' => $startTimeStr,
            'end_time' => $endTimeStr,
            'duration_minutes' => $duration
        ]);

        // 2. Tạo giá cho ca đó (SlotPrice - Lặp 7 ngày trong tuần)
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
}