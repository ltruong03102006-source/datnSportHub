<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageBookingRequest;
use App\Models\BookingPackage;
use App\Models\Venue;
use App\Models\VenuePackage;
use App\Services\PackageBookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PackageBookingController extends Controller
{
    public function create(Venue $venue): View
    {
        abort_unless(
            $venue->status === 'active',
            403,
            'Cơ sở sân thể thao này hiện chưa ở trạng thái hoạt động (chưa ký hợp đồng mới với Admin).'
        );

        abort_unless(
            $venue->allow_package_booking,
            403,
            'Cơ sở sân chưa bật chức năng đặt theo gói.'
        );

        $venue->load([
            'packages' => function ($query) {
                $query->where('status', 'active')
                    ->orderBy('type')
                    ->orderBy('duration');
            },

            'courts' => function ($query) {
                $query->where('status', 'active')
                    ->where('is_bookable_online', true)
                    ->with([
                        'timeSlots' => function ($slotQuery) {
                            $slotQuery->with('prices')
                                ->orderBy('start_time');
                        },
                    ]);
            },
        ]);

        abort_if(
            $venue->packages->isEmpty(),
            404,
            'Cơ sở sân hiện chưa có gói đặt sân khả dụng.'
        );

        abort_if(
            $venue->courts->isEmpty(),
            404,
            'Cơ sở sân hiện chưa có sân khả dụng để đặt gói.'
        );

        return view('package-bookings.create', compact('venue'));
    }

    public function store(
        StorePackageBookingRequest $request,
        PackageBookingService $service
    ): RedirectResponse {
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
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Lỗi khi tạo gói đặt sân.', [
                'user_id' => $request->user()->id,
                'package_id' => $validated['package_id'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Không thể tạo gói đặt sân. Vui lòng thử lại.');
        }

        $this->sendPackagePendingNotifications($bookingPackage);

        return redirect()
            ->route('package-bookings.show', $bookingPackage)
            ->with(
                'success',
                'Đã tạo yêu cầu đăng ký gói. Vui lòng thanh toán để hệ thống xác nhận và sinh lịch đặt sân.'
            );
    }

    public function show(Request $request, BookingPackage $bookingPackage): View
    {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        $bookingPackage->load([
            'user',
            'venue.owner',
            'venue.legalDocument',
            'package',
            'sessions.court',
            'sessions.timeSlot',
            'sessions.slots.timeSlot',
            'transactions' => function ($query) {
                $query->latest();
            },
            'bookings.court',
            'bookings' => function ($query) {
                $query->orderBy('slot_date')
                    ->orderBy('start_time');
            },
        ]);

        return view('package-bookings.show', compact('bookingPackage'));
    }

    public function showVnpay(Request $request, BookingPackage $bookingPackage): RedirectResponse|View
    {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        if ($bookingPackage->status !== 'pending_payment') {
            return redirect()->route('package-bookings.show', $bookingPackage)
                ->with('error', 'Chỉ những gói đang chờ thanh toán mới có thể thanh toán VNPay.');
        }

        $service = app(\App\Services\PackageBookingService::class);
        $packageHoldExpiresAt = $service->paymentHoldExpiresAt($bookingPackage);
        $packageHoldExpired = $service->paymentHoldExpired($bookingPackage);

        return view('package-bookings.payment.vnpay', compact('bookingPackage', 'packageHoldExpiresAt', 'packageHoldExpired'));
    }

    public function startVnpay(Request $request, BookingPackage $bookingPackage): RedirectResponse
    {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        $service = app(\App\Services\PackageBookingService::class);

        if ($bookingPackage->status !== 'pending_payment') {
            return redirect()->route('package-bookings.show', $bookingPackage)
                ->with('error', 'Chỉ những gói đang chờ thanh toán mới có thể thanh toán VNPay.');
        }

        if ($service->paymentHoldExpired($bookingPackage)) {
            return redirect()->route('package-bookings.show', $bookingPackage)
                ->with('error', 'Thời gian giữ chỗ thanh toán đã hết. Vui lòng tạo lại gói mới.');
        }

        try {
            $paymentUrl = $this->createPackagePaymentUrl($request, $bookingPackage);
        } catch (\Throwable $exception) {
            Log::error('Lỗi khi tạo đường dẫn VNPay cho gói đặt sân.', [
                'booking_package_id' => $bookingPackage->id,
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('package-bookings.show', $bookingPackage)
                ->with('error', 'Không thể khởi tạo thanh toán VNPay. Vui lòng thử lại.');
        }

        return redirect()->away($paymentUrl);
    }

    private function createPackagePaymentUrl(Request $request, BookingPackage $bookingPackage): string
    {
        $vnp_TmnCode = config('vnpay.vnp_TmnCode');
        $vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $vnp_Url = config('vnpay.vnp_Url');
        $vnp_Returnurl = route('vnpay.callback');

        if (! $vnp_Url || ! $vnp_TmnCode || ! $vnp_HashSecret) {
            throw new RuntimeException('Thiếu cấu hình VNPay. Vui lòng kiểm tra cấu hình VNPAY.');
        }

        $txnRef = 'PKG-' . $bookingPackage->id . '-' . time();
        $amount = (int) round($bookingPackage->final_amount * 100);

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_Amount' => $amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $request->ip() ?: '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toan goi SportHub ' . $bookingPackage->id,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_TxnRef' => $txnRef,
        ];

        ksort($inputData);

        $hashData = '';
        $query = '';
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $paymentUrl = $vnp_Url . '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'booking_package_id')) {
            DB::table('transactions')
                ->where('booking_package_id', $bookingPackage->id)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_method' => 'vnpay',
                    'payment_gateway' => 'VNPay',
                    'note' => 'Khách hàng đang chuyển sang cổng thanh toán VNPay cho gói đặt sân.',
                    'transaction_time' => now(),
                ]);
        }

        return $paymentUrl;
    }

    /**
     * Dùng cho đồ án/demo thanh toán.
     *
     * Khi thanh toán thành công:
     * pending_payment
     * -> sinh toàn bộ bookings con
     * -> active
     */
    public function paymentSuccess(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): RedirectResponse {
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
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Lỗi khi kích hoạt gói sau thanh toán.', [
                'booking_package_id' => $bookingPackage->id,
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Thanh toán đã ghi nhận nhưng chưa thể sinh lịch. Vui lòng liên hệ chủ sân để kiểm tra.'
            );
        }

        $this->sendPackageConfirmedNotifications($bookingPackage);

        return redirect()
            ->route('package-bookings.show', $bookingPackage)
            ->with('success', 'Thanh toán thành công. Hệ thống đã sinh toàn bộ lịch đặt sân trong gói.');
    }

    public function cancel(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): RedirectResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        $data = $request->validate([
            'mode' => ['nullable', 'in:all,future'],
        ]);

        try {
            $service->cancelPackage(
                bookingPackage: $bookingPackage,
                mode: $data['mode'] ?? 'future'
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Lỗi khi hủy gói đặt sân.', [
                'booking_package_id' => $bookingPackage->id,
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Không thể hủy gói đặt sân. Vui lòng thử lại.');
        }

        return redirect()
            ->route('package-bookings.show', $bookingPackage)
            ->with('success', 'Đã hủy gói đặt sân.');
    }

    public function pause(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): RedirectResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        try {
            $service->pausePackage($bookingPackage);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Đã tạm dừng gói đặt sân.');
    }

    public function resume(
        Request $request,
        BookingPackage $bookingPackage,
        PackageBookingService $service
    ): RedirectResponse {
        abort_unless(
            (int) $bookingPackage->user_id === (int) $request->user()->id,
            403
        );

        try {
            $service->resumePackage($bookingPackage);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Đã kích hoạt lại gói đặt sân.');
    }
    private function sendPackagePendingNotifications(BookingPackage $bookingPackage): void
    {
        try {
            $bookingPackage->loadMissing('venue');
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyPackageBookingPending($bookingPackage);

            if ($bookingPackage->venue?->owner_id) {
                $notificationService->notifyOwnerPackageBookingPending(
                    $bookingPackage->venue->owner_id,
                    $bookingPackage
                );
            }
        } catch (Throwable $exception) {
            Log::warning('Khong the tao thong bao goi dang cho thanh toan.', [
                'booking_package_id' => $bookingPackage->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendPackageConfirmedNotifications(BookingPackage $bookingPackage): void
    {
        try {
            $bookingPackage->loadMissing('venue');
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyPackageBookingConfirmed($bookingPackage);

            if ($bookingPackage->venue?->owner_id) {
                $notificationService->notifyOwnerNewPackageBooking(
                    $bookingPackage->venue->owner_id,
                    $bookingPackage
                );
            }
        } catch (Throwable $exception) {
            Log::warning('Khong the tao thong bao goi da thanh toan.', [
                'booking_package_id' => $bookingPackage->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
