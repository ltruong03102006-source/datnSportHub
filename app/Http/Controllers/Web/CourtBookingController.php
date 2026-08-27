<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\SlotPrice;
use App\Models\Transaction;
use App\Models\Setting;
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
        $court->load('venue');

        // Kiểm tra cơ sở sân thể thao có ở trạng thái hoạt động không
        if ($court->venue?->status !== 'active') {
            abort(404, 'Cơ sở sân thể thao này hiện không hoạt động hoặc chưa ký hợp đồng mới với Admin.');
        }

        // Kiểm tra sân có hoạt động không
        if ($court->status !== 'active') {
            abort(404, 'Sân này hiện không hoạt động hoặc đã bị ẩn.');
        }

        // Kiểm tra sân có thể đặt trực tuyến không
        if (!$court->is_bookable_online) {
            abort(403, 'Sân này không cho phép đặt trực tuyến. Vui lòng liên hệ quản lý.');
        }
        // =========================================================================
        // BẮT ĐẦU: LAZY CRON - TỰ ĐỘNG DỌN DẸP ĐƠN QUÁ HẠN & HOÀN KHO
        // =========================================================================
        $holdTimeMinutes = Setting::get('booking_hold_time', 15);
        
        $expiredBookings = Booking::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes($holdTimeMinutes))
            ->get();

        foreach ($expiredBookings as $expiredBooking) {
            // Lệnh update này sẽ TỰ ĐỘNG KÍCH HOẠT hàm booted() trong Model Booking 
            // để nhả số lượng chai nước/cái vợt về lại kho.
            $expiredBooking->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Hệ thống tự động hủy do hết hạn thanh toán giữ chỗ'
            ]);
        }
        // =========================================================================
        // KẾT THÚC LAZY CRON
        // =========================================================================
        $court->load([
            'venue' => fn($query) => $query->select('id', 'name', 'address', 'sport_id', 'banner'),
            'venue.sport' => fn($query) => $query->select('id', 'name'),
            'timeSlots' => fn($query) => $query->select('id', 'court_id', 'start_time', 'end_time', 'duration_minutes'),
            // THÊM DÒNG DƯỚI ĐÂY: Chỉ lấy dịch vụ Đang bán & Còn hàng
            'venue.services' => fn($query) => $query->where('is_active', true) // Bỏ check stock ở đây
        ]);

        return view('courts.booking', [
            'court' => $court,
            'services' => $court->venue->services ?? collect(), // Truyền dịch vụ ra View
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
            'payment_method' => 'nullable|in:COD,wallet',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
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
        $paymentMethod = $request->input('payment_method', 'COD');
        $voucherCode = $request->input('voucher_code');

        try {
            // Thực hiện giao dịch DB Transaction để đảm bảo tính nhất quán dữ liệu
            $booking = DB::transaction(function () use ($courtId, $userId, $slotDate, $startTime, $endTime, $note, $paymentMethod, $voucherCode) {
                
                // 2. Áp dụng Pessimistic Locking (lockForUpdate) đối với Sân để chặn các tiến trình khác đặt cùng sân lúc này
                $court = Court::with('venue')->where('id', $courtId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Kiểm tra xem cơ sở có ở trạng thái hoạt động (đã ký hợp đồng với Admin) hay không
                if ($court->venue?->status !== 'active') {
                    throw new Exception("Cơ sở sân thể thao này hiện tại chưa ở trạng thái hoạt động (chưa ký hợp đồng với Admin). Vui lòng chọn cơ sở khác.", 403);
                }

                // Kiểm tra xem sân có hoạt động không
                if ($court->status !== 'active') {
                    throw new Exception("Sân thể thao này hiện tại không hoạt động. Vui lòng chọn sân khác.", 403);
                }

                // Kiểm tra xem sân có cho phép đặt trực tuyến hay không
                if (isset($court->is_bookable_online) && !$court->is_bookable_online) {
                    throw new Exception("Sân thể thao này hiện tại đã tắt tính năng nhận lịch đặt trực tuyến.", 403);
                }

                // 3. Kiểm tra khung giờ có bị giữ bởi booking đã xác nhận không.
                // Luồng giữ chỗ: các booking 'pending' chưa quá hạn cũng được tính là đang bận.
                $holdTimeMinutes = Setting::get('booking_hold_time', 15);
                $isOverlapped = Booking::where('court_id', $courtId)
                    ->where('slot_date', $slotDate)
                    ->where(function ($q) use ($holdTimeMinutes) {
                        $q->where('status', 'confirmed')
                          ->orWhere(function ($q2) use ($holdTimeMinutes) {
                              $q2->where('status', 'pending')
                                 ->where('created_at', '>=', now()->subMinutes($holdTimeMinutes));
                          });
                    })
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $endTime)
                              ->where('end_time', '>', $startTime);
                    })
                    ->exists();

                if ($isOverlapped) {
                    throw new Exception("Khung giờ từ {$startTime} đến {$endTime} vào ngày {$slotDate} đã có người đặt trước. Vui lòng chọn khung giờ khác.", 409);
                }

                if (app(\App\Services\AvailabilityService::class)->hasActivePackageBooking($court, $slotDate, $startTime, $endTime)) {
                    throw new Exception("Khung gio tu {$startTime} den {$endTime} vao ngay {$slotDate} da co khach dat theo goi. Vui long chon khung gio khac.", 409);
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

                // Tính toán voucher giảm giá
                $discount = 0.0;
                $voucher = null;
                if (!empty($voucherCode)) {
                    $voucher = \App\Models\Voucher::where('code', $voucherCode)->first();
                    if ($voucher) {
                        $voucherService = app(\App\Services\VoucherService::class);
                        $bookingSlots = [['start_time' => $startTime, 'end_time' => $endTime]];
                        $eligibility = $voucherService->checkEligibility($voucher, $courtId, $slotDate, $bookingSlots, $price, $userId);
                        if (!$eligibility['eligible']) {
                            throw new Exception($eligibility['reason'], 422);
                        }
                        $discount = $eligibility['discount'];
                    }
                }

                $finalPrice = $price - $discount;

                // Check wallet balance if payment_method is wallet
                $userModel = \App\Models\User::find($userId);
                $customerWallet = $userModel ? $userModel->getOrCreateWallet() : null;
                if ($paymentMethod === 'wallet') {
                    if (!$customerWallet || (float)$customerWallet->balance < $finalPrice) {
                        throw new Exception("Số dư ví không đủ để thanh toán (" . number_format($customerWallet?->balance ?? 0) . "đ). Vui lòng nạp thêm hoặc chọn phương thức khác.", 402);
                    }
                }

                // 5. Lưu booking mới vào database
                $newBooking = Booking::create([
                    'court_id'    => $courtId,
                    'user_id'     => $userId,
                    'slot_date'   => $slotDate,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime,
                    'total_price' => $finalPrice,
                    'payment_method' => $paymentMethod,
                    'status'      => $paymentMethod === 'wallet' ? 'confirmed' : 'pending', // Auto confirm if paid via wallet
                    'payment_status' => $paymentMethod === 'wallet' ? 'paid' : 'unpaid',
                    'note'        => $note
                ]);

                // Attach voucher if applied
                if ($voucher) {
                    $newBooking->vouchers()->attach($voucher->id, ['discount_amount' => $discount]);
                    app(\App\Services\VoucherService::class)->incrementUsage($voucher);
                }

                // Deduct balance and record if wallet
                if ($paymentMethod === 'wallet' && $customerWallet) {
                    app(\App\Services\WalletService::class)->processTransaction(
                        wallet: $customerWallet,
                        type: \App\Enums\TransactionType::PAYMENT,
                        amount: $finalPrice,
                        description: "Thanh toán đặt sân #" . $newBooking->id,
                        bookingId: $newBooking->id
                    );

                    app(\App\Services\PlatformWalletService::class)->credit(
                        amount: $finalPrice,
                        type: 'booking_payment',
                        description: "Thanh toán đặt sân bằng ví khách hàng #" . $newBooking->id,
                        referenceType: 'booking',
                        referenceId: $newBooking->id,
                        reference: 'BOOKING-' . $newBooking->id
                    );
                }

                // 6. Tạo bản ghi giao dịch ban đầu cho booking mới để lịch sử thanh toán có dữ liệu.
                Transaction::updateOrCreate(
                    ['booking_id' => $newBooking->id],
                    [
                        'user_id' => $newBooking->user_id,
                        'transaction_code' => 'TXN-' . $newBooking->id . '-' . now()->format('YmdHis'),
                        'amount' => $newBooking->total_price,
                        'payment_method' => $paymentMethod,
                        'payment_gateway' => $paymentMethod === 'wallet' ? 'WALLET' : null,
                        'payment_status' => $paymentMethod === 'wallet' ? 'success' : 'pending',
                        'transaction_time' => now(),
                        'note' => 'Giao dịch được tạo khi khách hàng đặt sân.',
                    ]
                );

                // 7. Ghi log thay đổi trạng thái booking vào bảng booking_logs (Audit Trail)
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

    /**
     * Trả về bảng giá ca cho sân theo ngày (dành cho AJAX trên trang booking)
     * URL: GET /courts/{court}/shifts/prices?date=YYYY-MM-DD
     */
    public function prices(Request $request, Court $court): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $weekView = $request->boolean('week');

        try {
            $dayIndex = (int) date('w', strtotime($date)); // 0 (Sun) - 6 (Sat)

            // Lấy tất cả khung giờ và tất cả bản ghi giá liên quan (để có thể build tuần)
            $timeSlots = $court->timeSlots()->with('prices')->get();

            if ($weekView) {
                // Trả về dạng ma trận: mỗi time slot kèm giá cho từng ngày trong tuần (Thứ 2 .. Chủ nhật)
                $dayOrder = [1,2,3,4,5,6,0]; // T2..T7, CN
                $result = $timeSlots->map(function($slot) use ($dayOrder) {
                    $dayPrices = [];
                    foreach ($dayOrder as $d) {
                        // tìm bản ghi đúng ngày, nếu không có thì lấy bản ghi mặc định (day_of_week null)
                        $exact = $slot->prices->first(fn($p) => $p->day_of_week !== null && (int)$p->day_of_week === (int)$d);
                        $fallback = $slot->prices->first(fn($p) => $p->day_of_week === null);
                        $entry = $exact ?? $fallback;

                        $dayPrices[] = [
                            'day_of_week' => $d,
                            'price' => $entry?->price ? (float) $entry->price : null,
                            'price_type' => $entry?->price_type ?? null,
                            'is_peak' => isset($entry) ? (($entry->price_type ?? '') === 'peak' || ($entry->is_peak ?? false)) : false,
                        ];
                    }

                    return [
                        'time_slot_id' => $slot->id,
                        'name' => sprintf('Ca %s', $slot->id),
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'day_prices' => $dayPrices,
                    ];
                })->values();

                return response()->json(['success' => true, 'data' => $result]);
            }

            // Default: trả giá cho ngày được yêu cầu (date)
            $result = $timeSlots->map(function($slot) use ($dayIndex) {
                // Tìm giá phù hợp cho ngày đó hoặc fallback
                $exact = $slot->prices->first(fn($p) => $p->day_of_week !== null && (int)$p->day_of_week === $dayIndex);
                $fallback = $slot->prices->first(fn($p) => $p->day_of_week === null);
                $priceEntry = $exact ?? $fallback;

                return [
                    'time_slot_id' => $slot->id,
                    'name' => sprintf('Ca %s', $slot->id),
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'price' => $priceEntry?->price ? (float) $priceEntry->price : null,
                    'price_type' => $priceEntry?->price_type ?? null,
                    'is_peak' => isset($priceEntry) ? (($priceEntry->price_type ?? '') === 'peak' || ($priceEntry->is_peak ?? false)) : false,
                ];
            })->values();

            return response()->json(['success' => true, 'data' => $result]);

        } catch (Exception $e) {
            Log::error('Lỗi khi lấy bảng giá: ' . $e->getMessage(), ['court_id' => $court->id, 'date' => $date]);
            return response()->json(['success' => false, 'message' => 'Không thể tải bảng giá.'], 500);
        }
    }
}
