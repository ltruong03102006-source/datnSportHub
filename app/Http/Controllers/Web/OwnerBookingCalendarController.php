<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Venue;
use App\Services\BookingCompletionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerBookingCalendarController extends Controller
{
    public function index(BookingCompletionService $completionService): View
    {
        $completionService->completeExpiredBookings(ownerId: Auth::id());

        // TỰ ĐỘNG CHUYỂN TRẠNG THÁI "ĐÃ HOÀN THÀNH" CHO CÁC CA ĐÃ QUA
        $now = now('Asia/Ho_Chi_Minh');
        
        // 1. Lấy tất cả các ca đang "Đã xác nhận" của Chủ sân
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->whereHas('court.venue', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->get();

        // 2. Nhóm các ca lại thành từng "Đơn hàng" (cùng user, sân, ngày, thời điểm tạo)
        $groupedBookings = $confirmedBookings->groupBy(function($item) {
            return $item->user_id . '_' . $item->court_id . '_' . $item->slot_date->format('Y-m-d') . '_' . $item->created_at;
        });

        // 3. Kiểm tra: Chỉ ghi nhận Hoàn thành khi CA CUỐI CÙNG đã kết thúc
        $idsToComplete = [];
        foreach ($groupedBookings as $group) {
            $maxEndTime = $group->max('end_time');
            $slotDate = $group->first()->slot_date->format('Y-m-d');
            $maxEndDateTime = \Carbon\Carbon::parse($slotDate . ' ' . $maxEndTime, 'Asia/Ho_Chi_Minh');

            if ($now->greaterThanOrEqualTo($maxEndDateTime)) {
                $idsToComplete = array_merge($idsToComplete, $group->pluck('id')->toArray());
            }
        }

        // 4. Cập nhật 1 lần vào Database
        if (!empty($idsToComplete)) {
            Booking::whereIn('id', $idsToComplete)->update(['status' => 'completed']);
        }
        $venues = Venue::query()
            ->where('owner_id', Auth::id())
            ->with(['courts' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name']);

        $ownerBookings = Booking::query()
            ->whereHas('court.venue', fn ($query) => $query->where('owner_id', Auth::id()));

        $todayBookings = (clone $ownerBookings)
            ->whereDate('slot_date', today())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $pendingBookings = (clone $ownerBookings)
            ->where('status', 'pending')
            ->count();

        $weekBookings = (clone $ownerBookings)
            ->whereBetween('slot_date', [today()->startOfWeek(), today()->endOfWeek()])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $confirmedBookings = (clone $ownerBookings)
            ->where('status', 'confirmed')
            ->count();

        $totalCourts = $venues->sum(fn (Venue $venue) => $venue->courts->count());

        return view('owner.bookings.calendar', compact(
            'venues',
            'todayBookings',
            'pendingBookings',
            'weekBookings',
            'confirmedBookings',
            'totalCourts'
        ));
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'venue_id' => ['nullable', 'integer'],
            'court_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,rejected'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        $end = Carbon::parse($validated['end'])->startOfDay();

        $bookings = Booking::query()
            ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
            ->when(
                $validated['venue_id'] ?? null,
                fn ($query, $venueId) => $query->whereHas(
                    'court',
                    fn ($court) => $court->where('venue_id', $venueId)
                )
            )
            ->when(
                $validated['court_id'] ?? null,
                fn ($query, $courtId) => $query->where('court_id', $courtId)
            )
            ->when(
                $validated['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->whereDate('slot_date', '>=', $start->toDateString())
            ->whereDate('slot_date', '<', $end->toDateString())
            ->with(['court.venue', 'user'])
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        return response()->json(
            $bookings->map(fn (Booking $booking) => $this->formatEvent($booking))->values()
        );
    }

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,rejected'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use ($booking, $request, $validated) {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== 'pending') {
                abort(409, 'Chỉ có thể xử lý booking đang chờ xác nhận.');
            }

            $oldStatus = $lockedBooking->status;
            $lockedBooking->update(['status' => $validated['status']]);

            BookingLog::create([
                'booking_id' => $lockedBooking->id,
                'changed_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'note' => $validated['note']
                    ?? ($validated['status'] === 'confirmed'
                        ? 'Chủ sân xác nhận booking từ lịch quản lý.'
                        : 'Chủ sân từ chối booking từ lịch quản lý.'),
            ]);

            return $lockedBooking->load(['court.venue', 'user']);
        });

        return response()->json([
            'message' => $validated['status'] === 'confirmed'
                ? 'Đã xác nhận booking.'
                : 'Đã từ chối booking.',
            'event' => $this->formatEvent($booking),
            'pending_count' => Booking::query()
                ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->where('status', 'pending')
                ->count(),
        ]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy.',
            'reason.max' => 'Lý do hủy tối đa 1000 ký tự.',
        ]);

        $booking = DB::transaction(function () use ($booking, $request, $validated) {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== 'confirmed') {
                abort(409, 'Chỉ có thể hủy booking đã được xác nhận.');
            }

            $oldStatus = $lockedBooking->status;
            
            // CẬP NHẬT: Thêm tiền tố và lưu logic hoàn tiền (Chủ sân hủy -> Khách không mất phí, hoàn 100% ca đó)
            $lockedBooking->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Chủ sân hủy: ' . $validated['reason'],
                'cancellation_fee' => 0, 
                'refund_amount' => $lockedBooking->total_price, 
                'refund_status' => 'pending'
            ]);

            BookingLog::create([
                'booking_id' => $lockedBooking->id,
                'changed_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'note' => 'Chủ sân chủ động hủy. Lý do: ' . $validated['reason'],
            ]);

            return $lockedBooking->load(['court.venue', 'user']);
        });

        return response()->json([
            'message' => 'Đã hủy ca sân và ghi nhận hoàn tiền 100% cho khách!',
            'event' => $this->formatEvent($booking),
        ]);
    }
    private function formatEvent(Booking $booking): array
    {
        $status = $this->statusMeta($booking->status);
        $date = $booking->slot_date->format('Y-m-d');
        
        // --- LOGIC MỚI: GHI ĐÈ HIỂN THỊ (VISUAL OVERRIDE) ---
        $now = now('Asia/Ho_Chi_Minh');
        $endDateTime = \Carbon\Carbon::parse($date . ' ' . $booking->end_time, 'Asia/Ho_Chi_Minh');
        $isPast = $now->greaterThanOrEqualTo($endDateTime);

        // Nếu DB đang là "Đã xác nhận" nhưng giờ đã qua -> Khoác áo "Đã hoàn thành"
        if ($booking->status === 'confirmed' && $isPast) {
            $status = ['label' => 'Đã đá xong', 'color' => '#2563eb']; // Đổi màu xanh dương
        }
        
        // Trạng thái giả lập gửi xuống Frontend để giấu nút "Hủy sân"
        $displayStatus = ($booking->status === 'confirmed' && $isPast) ? 'completed' : $booking->status;
        // --- KẾT THÚC LOGIC ---

        return [
            'id' => (string) $booking->id,
            'title' => $booking->court->name.' - '.$booking->user->name,
            'start' => $date.'T'.$booking->start_time,
            'end' => $date.'T'.$booking->end_time,
            'backgroundColor' => $status['color'],
            'borderColor' => $status['color'],
            'textColor' => '#ffffff',
            'extendedProps' => [
                'booking_id' => $booking->id,
                'venue_name' => $booking->court->venue->name,
                'court_name' => $booking->court->name,
                'customer_name' => $booking->user->name,
                'customer_email' => $booking->user->email,
                'customer_phone' => $booking->user->phone ?? 'Chưa cập nhật SĐT', 
                
                'status' => $displayStatus, // Gửi status đã ghi đè
                'status_label' => $status['label'], // Gửi nhãn tên đã ghi đè
                
                'total_price' => number_format((float) $booking->total_price, 0, ',', '.').' đ',
                'note' => $booking->note,
                'cancel_reason' => $booking->cancel_reason,
                'date_label' => $booking->slot_date->format('d/m/Y'),
                'time_label' => substr($booking->start_time, 0, 5).' - '.substr($booking->end_time, 0, 5),
            ],
        ];
    }

    private function statusMeta(string $status): array
    {
        return match ($status) {
            'confirmed' => ['label' => 'Đã xác nhận', 'color' => '#047857'],
            'completed' => ['label' => 'Đã hoàn thành', 'color' => '#2563eb'],
            'cancelled' => ['label' => 'Đã hủy', 'color' => '#64748b'],
            'rejected' => ['label' => 'Đã từ chối', 'color' => '#dc2626'],
            default => ['label' => 'Chờ xác nhận', 'color' => '#d97706'],
        };
    }
}
