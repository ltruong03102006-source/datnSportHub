<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserBookingController extends Controller
{
    private const CANCELLABLE_STATUSES = ['pending', 'confirmed'];

    public function success(Booking $booking): View
    {
        $this->ensureOwner($booking);

        // THÊM 'court.venue.ownerRegistration' VÀO ĐÂY
        $bookingGroup = Booking::with(['court.venue.sport', 'court.venue.ownerRegistration'])
            ->where('user_id', Auth::id())
            ->where('court_id', $booking->court_id)
            ->where('slot_date', $booking->slot_date)
            ->where('created_at', $booking->created_at)
            ->orderBy('start_time')
            ->get();

        $totalGroupPrice = $bookingGroup->sum('total_price');

        // ... (phần code tính giờ và return view giữ nguyên)

        // SỬA LỖI TỔNG GIỜ ÂM TẠI ĐÂY
        $totalMinutes = 0;
        foreach ($bookingGroup as $b) {
            $start = Carbon::parse($b->start_time);
            $end = Carbon::parse($b->end_time);
            // Sử dụng diffInMinutes theo chiều thuận
            $totalMinutes += $start->diffInMinutes($end); 
        }
        
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $totalDurationStr = $hours > 0 
            ? $hours . 'h' . ($minutes > 0 ? $minutes . 'p' : '') 
            : $minutes . 'p';

        return view('bookings.success', [
            'booking' => $booking,
            'bookingGroup' => $bookingGroup,
            'totalGroupPrice' => $totalGroupPrice,
            'totalDurationStr' => $totalDurationStr,
            'statusMeta' => $this->statusMeta($booking->status),
        ]);
    }

    public function history(): View
    {
        $bookings = Booking::select(
                'court_id', 'slot_date', 'created_at', 'status',
                DB::raw('MIN(id) as id'), 
                DB::raw('MIN(start_time) as start_time'),
                DB::raw('MAX(end_time) as end_time'),
                DB::raw('SUM(total_price) as total_price'),
                DB::raw('COUNT(id) as slot_count'),
                DB::raw('MAX(cancel_reason) as cancel_reason')
            )
            ->where('user_id', Auth::id())
            ->groupBy('court_id', 'slot_date', 'created_at', 'status')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $bookings->load(['court.venue']);

        // --- ĐOẠN MỚI THÊM: Lấy danh sách ID các đơn đã được đánh giá ---
        $bookingIds = $bookings->pluck('id')->toArray();
        $reviewedBookingIds = \App\Models\Review::whereIn('booking_id', $bookingIds)->pluck('booking_id')->toArray();

        return view('bookings.history', [
            'bookings' => $bookings,
            'statusMap' => $this->statusMap(),
            'reviewedBookingIds' => $reviewedBookingIds, // Truyền biến ra ngoài View
        ]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        $this->ensureOwner($booking);

        try {
            DB::transaction(function () use ($booking): void {
                // Lấy TẤT CẢ các ca thuộc cùng nhóm (cùng ngày, cùng sân, cùng lúc đặt)
                $groupBookings = Booking::where('user_id', Auth::id())
                    ->where('court_id', $booking->court_id)
                    ->where('slot_date', $booking->slot_date)
                    ->where('created_at', $booking->created_at)
                    ->whereIn('status', self::CANCELLABLE_STATUSES)
                    ->lockForUpdate()
                    ->get();

                if ($groupBookings->isEmpty()) {
                    throw new HttpException(422, 'Đơn này không còn cho phép hủy.');
                }

                // Kiểm tra điều kiện thời gian dựa trên ca đá ĐẦU TIÊN của nhóm
                $firstBooking = $groupBookings->sortBy('start_time')->first();
                $this->ensureCanCancel($firstBooking);

                // Hủy toàn bộ các ca trong nhóm
                foreach ($groupBookings as $b) {
                    $oldStatus = $b->status;
                    $b->update(['status' => 'cancelled']);

                    BookingLog::create([
                        'booking_id' => $b->id,
                        'changed_by' => Auth::id(),
                        'old_status' => $oldStatus,
                        'new_status' => 'cancelled',
                        'note' => 'Khách hàng tự hủy trên web',
                    ]);
                }
            });
        } catch (HttpException $exception) {
            return $this->cancelErrorResponse(
                $request,
                $exception->getMessage(),
                $exception->getStatusCode()
            );
        }

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => 'Hủy toàn bộ yêu cầu đặt sân thành công.',
            ]);
        }

        return back()->with('success', 'Hủy toàn bộ yêu cầu đặt sân thành công.');
    }
    private function ensureOwner(Booking $booking): void
    {
        if ((int) $booking->user_id !== (int) Auth::id()) {
            throw new HttpException(403, 'Bạn không có quyền truy cập lịch đặt sân này.');
        }
    }

    private function ensureCanCancel(Booking $booking): void
    {
        if (! in_array($booking->status, self::CANCELLABLE_STATUSES, true)) {
            throw new HttpException(422, 'Đơn này không còn cho phép hủy.');
        }

        $startsAt = $this->slotStartsAt($booking);
        $deadline = $startsAt->copy()->subHours(12);

        if (Carbon::now('Asia/Ho_Chi_Minh')->greaterThan($deadline)) {
            throw new HttpException(403, 'Không thể hủy sân trước 12h.');
        }
    }

    private function slotStartsAt(Booking $booking): Carbon
    {
        $slotDate = $booking->slot_date instanceof Carbon
            ? $booking->slot_date->format('Y-m-d')
            : Carbon::parse($booking->slot_date)->format('Y-m-d');

        $startTime = substr((string) $booking->start_time, 0, 8);

        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            "{$slotDate} {$startTime}",
            'Asia/Ho_Chi_Minh'
        );
    }

    private function cancelErrorResponse(
        Request $request,
        string $message,
        int $status
    ): JsonResponse|RedirectResponse {
        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return back()->with('error', $message);
    }

    private function expectsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    private function statusMeta(string $status): array
    {
        return $this->statusMap()[$status] ?? [
            'label' => ucfirst($status),
            'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
        ];
    }

    private function statusMap(): array
    {
        return [
            'pending' => [
                'label' => 'Đang chờ',
                'class' => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            ],
            'confirmed' => [
                'label' => 'Đã chốt',
                'class' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            ],
            'completed' => [
                'label' => 'Đã đá xong',
                'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
            ],
            'cancelled' => [
                'label' => 'Đã hủy',
                'class' => 'bg-red-100 text-red-800 ring-red-600/20',
            ],
            'rejected' => [
                'label' => 'Bị từ chối',
                'class' => 'bg-red-100 text-red-800 ring-red-600/20',
            ],
        ];
    }
}
