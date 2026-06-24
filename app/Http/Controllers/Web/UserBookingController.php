<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Services\BookingCompletionService;
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

        $query = Booking::with(['court.venue.sport', 'court.venue.ownerRegistration'])
            ->where('user_id', Auth::id())
            ->where('court_id', $booking->court_id)
            ->where('slot_date', $booking->slot_date)
            ->where('created_at', $booking->created_at)
            ->where('status', $booking->status);

        if ($booking->cancel_reason) {
            $query->where('cancel_reason', $booking->cancel_reason);
        } else {
            $query->whereNull('cancel_reason');
        }

        $bookingGroup = $query->orderBy('start_time')->get();
        $totalGroupPrice = $bookingGroup->sum('total_price');

        $totalMinutes = 0;
        foreach ($bookingGroup as $b) {
            $start = Carbon::parse($b->start_time);
            $end = Carbon::parse($b->end_time);
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

    public function history(BookingCompletionService $completionService): View
    {
        $completionService->completeExpiredBookings(userId: Auth::id());

        $now = now('Asia/Ho_Chi_Minh');
        $userConfirmedBookings = Booking::where('user_id', Auth::id())
            ->where('status', 'confirmed')
            ->get();

        $groupedUserBookings = $userConfirmedBookings->groupBy(function($item) {
            return $item->court_id . '_' . $item->slot_date->format('Y-m-d') . '_' . $item->created_at;
        });

        $idsToComplete = [];
        foreach ($groupedUserBookings as $group) {
            $maxEndTime = $group->max('end_time');
            $slotDate = $group->first()->slot_date->format('Y-m-d');
            $maxEndDateTime = \Carbon\Carbon::parse($slotDate . ' ' . $maxEndTime, 'Asia/Ho_Chi_Minh');

            if ($now->greaterThanOrEqualTo($maxEndDateTime)) {
                $idsToComplete = array_merge($idsToComplete, $group->pluck('id')->toArray());
            }
        }

        if (!empty($idsToComplete)) {
            Booking::whereIn('id', $idsToComplete)->update(['status' => 'completed']);
        }

        $bookings = Booking::select(
                'court_id', 'slot_date', 'created_at', 'status', 'cancel_reason',
                DB::raw('MIN(id) as id'), 
                DB::raw('MIN(start_time) as start_time'),
                DB::raw('MAX(end_time) as end_time'),
                DB::raw('SUM(total_price) as total_price'),
                DB::raw('COUNT(id) as slot_count')
            )
            ->where('user_id', Auth::id())
            ->groupBy('court_id', 'slot_date', 'created_at', 'status', 'cancel_reason') 
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Nạp dữ liệu Chủ sân để hiện SĐT ở Form Hủy
        $bookings->load(['court.venue.owner', 'court.venue.ownerRegistration']);

        $bookingIds = $bookings->pluck('id')->toArray();
        $reviewedBookingIds = \App\Models\Review::whereIn('booking_id', $bookingIds)->pluck('booking_id')->toArray();
        $this->hydrateHistoryBookings($bookings->getCollection(), $now);

        return view('bookings.history', [
            'bookings' => $bookings,
            'statusMap' => $this->statusMap(),
            'reviewedBookingIds' => $reviewedBookingIds,
        ]);
    }

    private function hydrateHistoryBookings($bookings, Carbon $now): void
    {
        if ($bookings->isEmpty()) {
            return;
        }

        $slotGroups = $this->loadHistorySlotGroups($bookings);

        foreach ($bookings as $booking) {
            $actualSlots = $slotGroups->get($this->historyGroupKey($booking), collect());
            if ($actualSlots->isEmpty()) {
                $actualSlots = collect([$booking]);
            }

            $firstSlot = $actualSlots->sortBy('start_time')->first();
            $isEligibleStatus = in_array($booking->status, self::CANCELLABLE_STATUSES, true);

            $booking->setAttribute('slot_date_label', $booking->slot_date?->format('d/m/Y') ?? '');
            $booking->setAttribute('merged_time_strings', $this->mergedTimeStrings($actualSlots, $booking->court?->name ?? 'Sân'));
            $booking->setAttribute('owner_phone', $this->ownerPhoneForBooking($booking));
            $booking->setAttribute('is_eligible_status', $isEligibleStatus);
            $booking->setAttribute('is_past_start_time', $firstSlot ? $now->greaterThanOrEqualTo($this->slotStartsAt($firstSlot)) : false);
        }
    }

    private function loadHistorySlotGroups($bookings)
    {
        return Booking::where('user_id', Auth::id())
            ->where(function ($query) use ($bookings): void {
                foreach ($bookings as $booking) {
                    $query->orWhere(function ($groupQuery) use ($booking): void {
                        $groupQuery
                            ->where('court_id', $booking->court_id)
                            ->where('slot_date', $booking->slot_date)
                            ->where('created_at', $this->normalDateTime($booking->created_at))
                            ->where('status', $booking->status);

                        if ($booking->cancel_reason === null) {
                            $groupQuery->whereNull('cancel_reason');
                        } else {
                            $groupQuery->where('cancel_reason', $booking->cancel_reason);
                        }
                    });
                }
            })
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Booking $slot): string => $this->historyGroupKey($slot));
    }

    private function mergedTimeStrings($actualSlots, string $courtName): array
    {
        $slots = $actualSlots->sortBy('start_time')->values();
        if ($slots->isEmpty()) {
            return [];
        }

        $merged = [];
        $currentStart = substr((string) $slots[0]->start_time, 0, 5);
        $currentEnd = substr((string) $slots[0]->end_time, 0, 5);

        for ($index = 1; $index < $slots->count(); $index++) {
            $nextStart = substr((string) $slots[$index]->start_time, 0, 5);
            $nextEnd = substr((string) $slots[$index]->end_time, 0, 5);

            if ($currentEnd === $nextStart) {
                $currentEnd = $nextEnd;
                continue;
            }

            $merged[] = "- Sân {$courtName}: {$currentStart} - {$currentEnd}";
            $currentStart = $nextStart;
            $currentEnd = $nextEnd;
        }

        $merged[] = "- Sân {$courtName}: {$currentStart} - {$currentEnd}";

        return $merged;
    }

    private function ownerPhoneForBooking(Booking $booking): ?string
    {
        return $booking->court?->venue?->ownerRegistration?->phone
            ?? $booking->court?->venue?->owner?->phone
            ?? null;
    }

    private function historyGroupKey(Booking $booking): string
    {
        $slotDate = $booking->slot_date instanceof Carbon
            ? $booking->slot_date->format('Y-m-d')
            : Carbon::parse($booking->slot_date)->format('Y-m-d');

        return implode('|', [
            (int) $booking->court_id,
            $slotDate,
            $this->normalDateTime($booking->created_at),
            (string) $booking->status,
            sha1((string) $booking->cancel_reason),
        ]);
    }

    private function normalDateTime($value): string
    {
        return $value instanceof Carbon
            ? $value->format('Y-m-d H:i:s')
            : Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function cancel(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        $this->ensureOwner($booking);
        $reasonInput = $request->input('reason', 'Không nhập lý do');
        $reason = 'Khách tự hủy: ' . $reasonInput;

        try {
            DB::transaction(function () use ($booking, $reason): void {
                $groupBookings = Booking::where('user_id', Auth::id())
                    ->where('court_id', $booking->court_id)->where('slot_date', $booking->slot_date)
                    ->where('created_at', $booking->created_at)
                    ->whereIn('status', self::CANCELLABLE_STATUSES)->lockForUpdate()->get();

                if ($groupBookings->isEmpty()) throw new HttpException(422, 'Đơn này không còn cho phép hủy.');

                $firstBooking = $groupBookings->sortBy('start_time')->first();
                $this->ensureCanCancel($firstBooking);
                
                // --- SỬA TẠI ĐÂY: GỌI THUẬT TOÁN TÍNH PHẠT ĐỘNG ---
                $feePercent = $this->determineCancellationFeePercent($firstBooking);

                foreach ($groupBookings as $b) {
                    // --- SỬA TẠI ĐÂY: DÙNG BIẾN $feePercent ---
                    $fee = ($b->total_price * $feePercent) / 100;
                    $refund = $b->total_price - $fee;
                    $refundStatus = $refund > 0 ? 'pending' : 'none';

                    $oldStatus = $b->status;
                    $b->update([
                        'status' => 'cancelled',
                        'cancel_reason' => $reason,
                        'cancellation_fee' => $fee,
                        'refund_amount' => $refund,
                        'refund_status' => $refundStatus
                    ]);

                    BookingLog::create([
                        'booking_id' => $b->id,
                        'changed_by' => Auth::id(),
                        'old_status' => $oldStatus,
                        'new_status' => 'cancelled',
                        // --- SỬA TẠI ĐÂY: DÙNG BIẾN $feePercent TRONG GHI CHÚ ---
                        'note' => "Khách hủy. Phạt {$feePercent}%. Lý do: {$reason}",
                    ]);
                }
            });
        } catch (HttpException $exception) {
            return $this->cancelErrorResponse($request, $exception->getMessage(), $exception->getStatusCode());
        }

        if ($this->expectsJson($request)) return response()->json(['message' => 'Hủy yêu cầu đặt sân thành công.']);
        return back()->with('success', 'Hủy yêu cầu đặt sân thành công.');
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
        if (Carbon::now('Asia/Ho_Chi_Minh')->greaterThanOrEqualTo($startsAt)) {
            throw new HttpException(403, 'Sân đã đến giờ hoặc quá giờ, không thể hủy!');
        }
    }

    public function calculateCancelFee(Request $request, Booking $booking): JsonResponse
    {
        $this->ensureOwner($booking);
        
        $groupBookings = Booking::where('user_id', Auth::id())
            ->where('court_id', $booking->court_id)->where('slot_date', $booking->slot_date)
            ->where('created_at', $booking->created_at)->whereIn('status', self::CANCELLABLE_STATUSES)->get();

        // FIX LỖI XOAY CHUỘT: Chặn lỗi nếu đơn đã bị hủy từ trước
        if ($groupBookings->isEmpty()) {
            return response()->json(['message' => 'Đơn này đã hủy hoặc không còn cho phép hủy.'], 422);
        }

        $firstBooking = $groupBookings->sortBy('start_time')->first();

        try {
            $this->ensureCanCancel($firstBooking);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        // GỌI THUẬT TOÁN ĐỘNG
        $feePercent = $this->determineCancellationFeePercent($firstBooking);
        
        $totalPrice = $groupBookings->sum('total_price');
        $fee = ($totalPrice * $feePercent) / 100;
        $refund = $totalPrice - $fee;

        return response()->json([
            'success' => true,
            'total_price' => $totalPrice,
            'fee_percent' => $feePercent,
            'cancellation_fee' => $fee,
            'refund_amount' => $refund,
        ]);
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
                'label' => 'Đã xác nhận',
                'class' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            ],
            'completed' => [
                'label' => 'Đã hoàn thành',
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
    /**
     * Thuật toán tìm % phí phạt thông minh
     */
    private function determineCancellationFeePercent(Booking $firstBooking): int
    {
        $startsAt = $this->slotStartsAt($firstBooking);
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        
        // Khoảng cách từ Bây giờ đến giờ bắt đầu (Tính bằng giờ)
        $diffInHours = $now->diffInHours($startsAt, false); 

        // Rủi ro lố giờ (Cố tình request API ảo) -> Phạt max 100%
        if ($diffInHours < 0) return 100;

        // Lấy danh sách cấu hình, SẮP XẾP TĂNG DẦN (Ví dụ: 6h, 12h, 24h)
        $policies = \App\Models\CancellationPolicy::where('venue_id', $firstBooking->court->venue_id)
            ->orderBy('hours_before', 'asc')
            ->get();

        // Fallback: Chủ sân chưa cấu hình gì -> Miễn phí hủy
        if ($policies->isEmpty()) return 0;

        // Quét tìm mốc vi phạm:
        // Nếu hủy cách 14h: 14 < 6(False) -> 14 < 12(False) -> 14 < 24(True). Bị phạt mốc 24h!
        foreach ($policies as $policy) {
            if ($diffInHours < $policy->hours_before) {
                return $policy->fee_percent;
            }
        }

        // Hủy quá sớm (VD hủy trước 30 tiếng, không vi phạm mốc 24h lớn nhất) -> An toàn
        return 0;
    }
}
