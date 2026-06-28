<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\SlotPrice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class CourtBookingController extends Controller
{
    /**
     * Hiển thị trang đặt sân
     */
    public function show(Court $court): View
    {
        // Kiểm tra sân có hoạt động không
        if ($court->status !== 'active') {
            abort(404, 'Sân này hiện không hoạt động hoặc đã bị ẩn.');
        }

        // Kiểm tra sân có thể đặt trực tuyến không
        if (!$court->is_bookable_online) {
            abort(403, 'Sân này không cho phép đặt trực tuyến. Vui lòng liên hệ quản lý.');
        }

        $court->load([
            'venue' => fn($query) => $query->select('id', 'name', 'address', 'sport_id', 'banner'),
            'venue.sport' => fn($query) => $query->select('id', 'name'),
            'timeSlots' => fn($query) => $query->select('id', 'court_id', 'start_time', 'end_time', 'duration_minutes'),
        ]);

        return view('courts.booking', [
            'court' => $court,
            'bannerUrl' => $court->venue?->banner ?? '/images/default-court.jpg',
        ]);
    }

    /**
     * API Tạo Booking mới
     * Có validate trùng giờ, pessimistic lock chống Race Condition và DB Transaction
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Khởi tạo Validator để kiểm duyệt dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'court_id'   => 'required|exists:courts,id',
            'slot_date'  => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'note'       => 'nullable|string|max:1000',
        ], [
            'court_id.required'    => 'Vui lòng chọn sân cần đặt.',
            'court_id.exists'      => 'Sân được chọn không tồn tại trong hệ thống.',
            'slot_date.required'   => 'Vui lòng chọn ngày đặt sân.',
            'slot_date.date'       => 'Ngày đặt sân không đúng định dạng.',
            'slot_date.after_or_equal' => 'Ngày đặt sân phải từ hôm nay trở đi.',
            'start_time.required'  => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.date_format' => 'Giờ bắt đầu không đúng định dạng (Giờ:Phút).',
            'end_time.required'    => 'Vui lòng chọn giờ kết thúc.',
            'end_time.date_format'   => 'Giờ kết thúc không đúng định dạng (Giờ:Phút).',
            'end_time.after'       => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để đặt sân.'
            ], 401);
        }
        $userId = $user->id;

        $courtId   = $request->input('court_id');
        $slotDate  = $request->input('slot_date');
        $startTime = $request->input('start_time');
        $endTime   = $request->input('end_time');
        $note      = $request->input('note');

        try {
            // Thực hiện giao dịch DB Transaction để đảm bảo tính nhất quán dữ liệu
            $booking = DB::transaction(function () use ($courtId, $userId, $slotDate, $startTime, $endTime, $note) {
                
                // 2. Áp dụng Pessimistic Locking (lockForUpdate) đối với Sân để chặn các tiến trình khác đặt cùng sân lúc này
                $court = Court::where('id', $courtId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Kiểm tra xem sân có hoạt động không
                if ($court->status !== 'active') {
                    throw new Exception("Sân thể thao này hiện tại không hoạt động. Vui lòng chọn sân khác.", 403);
                }

                // Kiểm tra xem sân có cho phép đặt trực tuyến hay không
                if (isset($court->is_bookable_online) && !$court->is_bookable_online) {
                    throw new Exception("Sân thể thao này hiện tại đã tắt tính năng nhận lịch đặt trực tuyến.", 403);
                }

                // 3. Kiểm tra xem khung giờ yêu cầu có trùng lặp với booking hiện tại không
                // Một slot bị coi là trùng khi: start_time < $endTime && end_time > $startTime
                $isOverlapped = Booking::where('court_id', $courtId)
                    ->where('slot_date', $slotDate)
                    ->whereIn('status', ['pending', 'confirmed', 'completed']) // Chỉ tính lịch hoạt động
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $endTime)
                              ->where('end_time', '>', $startTime);
                    })
                    ->exists();

                if ($isOverlapped) {
                    throw new Exception("Khung giờ từ {$startTime} đến {$endTime} vào ngày {$slotDate} đã có người đặt trước. Vui lòng chọn khung giờ khác.", 409);
                }

                // 4. Tính toán giá tiền thực tế dựa trên cấu hình slot_prices (nếu có)
                // Tìm time_slot khớp nhất hoặc tính theo giờ
                $dayOfWeek = date('w', strtotime($slotDate)); // 0 (CN) -> 6 (Thứ 7)
                
                // Thử tìm giá cấu hình phù hợp với khung giờ này
                $price = null;
                $timeSlot = TimeSlot::where('court_id', $courtId)
                    ->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime)
                    ->first();

                if ($timeSlot) {
                    $slotPrice = SlotPrice::where('time_slot_id', $timeSlot->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->first();
                    if ($slotPrice) {
                        $price = $slotPrice->price;
                    }
                }

                // Nếu không tìm thấy cấu hình giá cụ thể, áp dụng giá mặc định hợp lý (ví dụ: 150,000 VND / giờ)
                if (is_null($price)) {
                    // Tính thời gian chơi để tính tiền
                    $startSecs = strtotime($startTime);
                    $endSecs   = strtotime($endTime);
                    $hours     = ($endSecs - $startSecs) / 3600;
                    $price     = round(max(0.5, $hours) * 150000); // 150k/giờ, tối thiểu nửa giờ
                }

                // 5. Lưu booking mới vào database
                $newBooking = Booking::create([
                    'court_id'    => $courtId,
                    'user_id'     => $userId,
                    'slot_date'   => $slotDate,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime,
                    'total_price' => $price,
                    'status'      => 'pending', // Chờ phê duyệt
                    'note'        => $note
                ]);

                // 6. Ghi log thay đổi trạng thái booking vào bảng booking_logs (Audit Trail)
                DB::table('booking_logs')->insert([
                    'booking_id'  => $newBooking->id,
                    'changed_by'  => $userId,
                    'old_status'  => 'none', // trạng thái trước khi tạo
                    'new_status'  => 'pending',
                    'note'        => 'Khách hàng tạo mới yêu cầu đặt sân thành công từ website.',
                    'created_at'  => now()
                ]);

                return $newBooking;
            });

            // Notify customer and owner about new booking (best-effort)
            try {
                app(\App\Services\NotificationService::class)->notifyBookingPlaced($booking);
            } catch (\Throwable $e) {
                Log::warning('Không thể tạo thông báo đặt sân cho khách.', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $booking->loadMissing(['court.venue.owner']);
                $ownerId = $booking->court->venue->owner?->id;
                if ($ownerId) {
                    app(\App\Services\NotificationService::class)->notifyOwnerNewBooking($ownerId, $booking);
                }
            } catch (\Throwable $e) {
                Log::warning('Không thể tạo thông báo booking mới cho chủ sân.', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gửi yêu cầu đặt sân thành công! Vui lòng chờ chủ sân phê duyệt.',
                'data'    => $booking
            ], 201);

        } catch (Exception $e) {
            Log::error('Lỗi khi thực hiện đặt sân: ' . $e->getMessage(), [
                'user_id'   => $userId,
                'court_id'  => $courtId,
                'slot_date' => $slotDate,
                'times'     => "{$startTime}-{$endTime}"
            ]);

            $statusCode = in_array($e->getCode(), [403, 409]) ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Hệ thống bận, vui lòng thử lại sau.'
            ], $statusCode);
        }
    }
}
