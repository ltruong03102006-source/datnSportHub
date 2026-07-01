<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageBookingRequest;
use App\Models\BookingPackage;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\VenuePackage;
use App\Services\PackageBookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PackageBookingController extends Controller
{
    public function preview(
        StorePackageBookingRequest $request,
        PackageBookingService $service
    ): JsonResponse {
        $validated = $request->validated();

        $package = VenuePackage::query()
            ->with('venue')
            ->findOrFail($validated['package_id']);

        $plans = $service->previewSessionPlans(
            $package,
            $validated['sessions'],
            $validated['start_date']
        )->values();

        $price = $service->calculatePrice($package, $plans);

        $conflicts = collect();

        $allDates = $plans
            ->flatMap(fn (array $plan) => $plan['dates'])
            ->sort()
            ->values();

        return response()->json([
            'available' => $conflicts->isEmpty(),

            'package' => [
                'id' => $package->id,
                'name' => $package->name,
                'type' => $package->type,
                'duration' => $package->duration,
                'max_sessions_per_week' => $package->max_sessions_per_week,
                'discount_percent' => (float) $package->discount_percent,
            ],

            'summary' => [
                'start_date' => Carbon::parse($validated['start_date'])->toDateString(),
                'end_date' => $allDates->isNotEmpty()
                    ? $allDates->max()->toDateString()
                    : null,
                'weekly_sessions' => $plans->count(),
                'total_sessions' => $price['session_count'],
                'total_amount' => $price['subtotal'],
                'discount_amount' => $price['discount_amount'],
                'final_amount' => $price['final_amount'],
            ],

            'sessions' => $plans->map(fn (array $plan, int $index) => [
                'session_order' => $index + 1,
                'weekday' => $plan['weekday'],
                'weekday_label' => $this->weekdayLabel($plan['weekday']),
                'court_id' => $plan['court']->id,
                'court_name' => $plan['court']->name,
                'time_slot_id' => $plan['first_time_slot']->id,
                'start_time' => $plan['start_time'],
                'end_time' => $plan['end_time'],
                'price_per_session' => $plan['price_per_session'],
                'time_slots' => $plan['time_slots']->map(fn (array $slot) => [
                    'id' => $slot['model']->id,
                    'start_time' => $slot['model']->start_time,
                    'end_time' => $slot['model']->end_time,
                    'price' => $slot['price'],
                ])->values(),
                'dates' => $plan['dates']
                    ->map(fn (Carbon $date) => $date->toDateString())
                    ->values(),
            ])->values(),

            'conflicts' => $conflicts->map(fn ($booking) => [
                'booking_id' => $booking->id,
                'court_id' => $booking->court_id,
                'slot_date' => Carbon::parse($booking->slot_date)->toDateString(),
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
            ])->values(),
        ]);
    }

    public function store(
        StorePackageBookingRequest $request,
        PackageBookingService $service
    ): JsonResponse {
        $validated = $request->validated();

        $package = VenuePackage::query()
            ->with('venue')
            ->findOrFail($validated['package_id']);

        try {
            $bookingPackage = $service->createPendingPackage(
                userId: $request->user()->id,
                package: $package,
                sessions: $validated['sessions'],
                startDate: $validated['start_date']
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            Log::error('API lỗi tạo gói đặt sân.', [
                'user_id' => $request->user()->id,
                'package_id' => $validated['package_id'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Không thể tạo yêu cầu đăng ký gói. Vui lòng thử lại.',
            ], 500);
        }

        return response()->json([
            'message' => 'Đã tạo yêu cầu đăng ký gói. Vui lòng thanh toán để hệ thống sinh lịch đặt sân.',
            'data' => $this->bookingPackageResource($bookingPackage),
        ], 201);
    }

    /**
     * Dùng cho API demo thanh toán.
     *
     * Sau khi thanh toán thành công:
     * pending_payment -> sinh bookings con -> active
     */
    public function paymentSuccess(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): JsonResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        try {
            $bookingPackage = $service->activateAfterPayment(
                bookingPackage: $bookingPackage,
                changedBy: $request->user()->id,
                transactionStatus: 'success'
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            Log::error('API lỗi kích hoạt gói sau thanh toán.', [
                'booking_package_id' => $bookingPackage->id,
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Thanh toán đã ghi nhận nhưng chưa thể sinh lịch. Vui lòng liên hệ chủ sân để kiểm tra.',
            ], 500);
        }

        return response()->json([
            'message' => 'Thanh toán thành công. Hệ thống đã sinh toàn bộ lịch đặt sân trong gói.',
            'data' => $this->bookingPackageResource($bookingPackage),
        ]);
    }

    public function cancel(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): JsonResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        $data = $request->validate([
            'mode' => ['nullable', 'in:all,future'],
        ]);

        try {
            $bookingPackage = $service->cancelPackage(
                bookingPackage: $bookingPackage,
                mode: $data['mode'] ?? 'future'
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            Log::error('API lỗi hủy gói đặt sân.', [
                'booking_package_id' => $bookingPackage->id,
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Không thể hủy gói đặt sân. Vui lòng thử lại.',
            ], 500);
        }

        return response()->json([
            'message' => 'Đã hủy gói đặt sân.',
            'data' => $this->bookingPackageResource($bookingPackage),
        ]);
    }

    public function pause(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): JsonResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        try {
            $bookingPackage = $service->pausePackage($bookingPackage);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Đã tạm dừng gói đặt sân.',
            'data' => $this->bookingPackageResource($bookingPackage),
        ]);
    }

    public function resume(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): JsonResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        try {
            $bookingPackage = $service->resumePackage($bookingPackage);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Đã kích hoạt lại gói đặt sân.',
            'data' => $this->bookingPackageResource($bookingPackage),
        ]);
    }

    private function bookingPackageResource(BookingPackage $bookingPackage): array
    {
        $bookingPackage->loadMissing([
            'package',
            'venue',
            'sessions.court',
            'sessions.timeSlot',
            'sessions.slots.timeSlot',
            'transactions',
            'bookings',
        ]);

        return [
            'id' => $bookingPackage->id,
            'user_id' => $bookingPackage->user_id,
            'venue_id' => $bookingPackage->venue_id,
            'venue_name' => $bookingPackage->venue?->name,
            'package_id' => $bookingPackage->package_id,
            'package_name' => $bookingPackage->package?->name,

            'start_date' => optional($bookingPackage->start_date)->toDateString(),
            'end_date' => optional($bookingPackage->end_date)->toDateString(),

            'weekly_sessions' => $bookingPackage->weekly_sessions,
            'total_sessions' => $bookingPackage->total_sessions,
            'used_sessions' => $bookingPackage->used_sessions,
            'remaining_sessions' => method_exists($bookingPackage, 'remainingSessions')
                ? $bookingPackage->remainingSessions()
                : max(0, (int) $bookingPackage->total_sessions - (int) $bookingPackage->used_sessions),

            'total_amount' => (float) $bookingPackage->total_amount,
            'discount_amount' => (float) $bookingPackage->discount_amount,
            'final_amount' => (float) $bookingPackage->final_amount,

            'status' => $bookingPackage->status,
            'status_label' => method_exists($bookingPackage, 'statusLabel')
                ? $bookingPackage->statusLabel()
                : $bookingPackage->status,

            'paid_at' => optional($bookingPackage->paid_at)->toDateTimeString(),
            'paused_at' => optional($bookingPackage->paused_at)->toDateTimeString(),
            'cancelled_at' => optional($bookingPackage->cancelled_at)->toDateTimeString(),
            'completed_at' => optional($bookingPackage->completed_at)->toDateTimeString(),

            'sessions' => $bookingPackage->sessions->map(fn ($session) => [
                'id' => $session->id,
                'session_order' => $session->session_order,
                'weekday' => $session->weekday,
                'weekday_label' => method_exists($session, 'weekdayLabel')
                    ? $session->weekdayLabel()
                    : $this->weekdayLabel($session->weekday),
                'court_id' => $session->court_id,
                'court_name' => $session->court?->name,
                'time_slot_id' => $session->time_slot_id,
                'start_time' => $session->timeSlot?->start_time,
                'end_time' => $session->timeSlot?->end_time,
                'price_per_session' => (float) $session->price_per_session,
                'time_slots' => $session->slots->map(fn ($slot) => [
                    'id' => $slot->time_slot_id,
                    'start_time' => $slot->timeSlot?->start_time,
                    'end_time' => $slot->timeSlot?->end_time,
                    'price' => (float) $slot->price,
                ])->values(),
            ])->values(),

            'bookings' => $bookingPackage->bookings->map(fn ($booking) => [
                'id' => $booking->id,
                'court_id' => $booking->court_id,
                'time_slot_id' => $booking->time_slot_id,
                'slot_date' => Carbon::parse($booking->slot_date)->toDateString(),
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'total_price' => (float) $booking->total_price,
                'status' => $booking->status,
            ])->values(),

            'transactions' => $bookingPackage->transactions->map(fn ($transaction) => [
                'id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code ?? null,
                'amount' => (float) $transaction->amount,
                'payment_method' => $transaction->payment_method ?? null,
                'payment_gateway' => $transaction->payment_gateway ?? null,
                'payment_status' => $transaction->payment_status ?? null,
                'transaction_time' => optional($transaction->transaction_time)->toDateTimeString(),
            ])->values(),
        ];
    }

    private function weekdayLabel(int $weekday): string
    {
        return match ((int) $weekday) {
            0 => 'Chủ nhật',
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            default => 'Không xác định',
        };
    }
}
