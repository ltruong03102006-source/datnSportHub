<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\Review;
use Illuminate\Support\Carbon;

class NotificationService
{
    public function create(int $userId, string $title, string $content, ?string $link = null, ?string $type = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'link' => $link,
        ]);
    }

    public function notifyBookingConfirmed(Booking $booking)
    {
        $userId = $booking->user_id;
        $title = 'Booking đã được xác nhận';
        $content = "Booking #{$booking->id} đã được chủ sân xác nhận.";
        $link = route('account.bookings.index');
        return $this->create($userId, $title, $content, $link, 'booking_confirmed');
    }

    public function notifyBookingRejected(Booking $booking)
    {
        $userId = $booking->user_id;
        $title = 'Booking bị từ chối';
        $content = "Booking #{$booking->id} đã bị từ chối.";
        $link = route('account.bookings.index');
        return $this->create($userId, $title, $content, $link, 'booking_rejected');
    }

    public function notifyBookingCancelled(Booking $booking)
    {
        $userId = $booking->user_id;
        $title = 'Booking đã bị hủy';
        $content = "Booking #{$booking->id} đã bị hủy.";
        $link = route('account.bookings.index');
        return $this->create($userId, $title, $content, $link, 'booking_cancelled');
    }

    public function notifyBookingPlaced(Booking $booking)
    {
        $userId = $booking->user_id;
        $title = 'Booking đã được tạo';
        $content = "Booking #{$booking->id} đã được tạo và đang chờ xử lý.";
        $link = route('account.bookings.index');
        return $this->create($userId, $title, $content, $link, 'booking_placed');
    }

    public function notifyOwnerNewBooking($ownerUserId, Booking $booking)
    {
        $title = 'Có booking mới';
        $content = 'Khách hàng vừa đặt sân.';
        $link = route('owner.web.calendar.index');
        return $this->create($ownerUserId, $title, $content, $link, 'owner_new_booking');
    }

    public function notifyPackageBookingConfirmed(BookingPackage $bookingPackage)
    {
        $title = 'Gói đặt sân đã được xác nhận';
        $content = "Gói đặt sân #{$bookingPackage->id} đã thanh toán và được xác nhận tự động.";
        $link = route('package-bookings.show', $bookingPackage);

        return $this->create($bookingPackage->user_id, $title, $content, $link, 'package_booking_confirmed');
    }

    public function notifyOwnerNewPackageBooking($ownerUserId, BookingPackage $bookingPackage)
    {
        $title = 'Có gói đặt sân mới';
        $content = "Khách hàng vừa thanh toán gói đặt sân #{$bookingPackage->id}.";
        $link = route('owner.web.calendar.index');

        return $this->create($ownerUserId, $title, $content, $link, 'owner_new_package_booking');
    }

    public function notifyPackageBookingPending(BookingPackage $bookingPackage)
    {
        $title = 'Yeu cau dat goi da duoc tao';
        $content = "Goi dat san #{$bookingPackage->id} dang cho thanh toan.";
        $link = route('package-bookings.show', $bookingPackage);

        return $this->create($bookingPackage->user_id, $title, $content, $link, 'package_booking_pending');
    }

    public function notifyOwnerPackageBookingPending($ownerUserId, BookingPackage $bookingPackage)
    {
        $title = 'Co yeu cau dang ky goi';
        $content = "Khach hang vua tao yeu cau dang ky goi #{$bookingPackage->id}, dang cho thanh toan.";
        $link = route('owner.web.calendar.index');

        return $this->create($ownerUserId, $title, $content, $link, 'owner_package_booking_pending');
    }

    public function notifyOwnerNewReview($ownerUserId, Review $review)
    {
        $title = 'Có đánh giá mới';
        $content = 'Bạn vừa nhận được đánh giá mới.';
        $link = route('owner.web.reviews.index');
        return $this->create($ownerUserId, $title, $content, $link, 'owner_new_review');
    }

    public function notifyOwnerRescheduleRequest($ownerUserId, $rescheduleRequest)
    {
        $title = 'Có yêu cầu đổi lịch';
        $content = 'Khách hàng muốn đổi lịch booking.';
        if ($rescheduleRequest instanceof \Illuminate\Support\Collection) {
            $rescheduleRequest = $rescheduleRequest->first();
        }

        $requestCode = $rescheduleRequest?->request_code ?: $rescheduleRequest?->id;
        $link = $requestCode
            ? route('owner.web.reschedule.show', $requestCode)
            : route('owner.web.reschedule.index');

        return $this->create($ownerUserId, $title, $content, $link, 'owner_reschedule_request');
    }

    public function notifyCustomerRescheduleApproved(Booking $booking)
    {
        $userId = $booking->user_id;
        $title = 'Yêu cầu đổi lịch được chấp nhận';
        $content = "Yêu cầu đổi lịch cho Booking #{$booking->id} đã được chấp nhận.";
        $link = route('account.bookings.index');
        return $this->create($userId, $title, $content, $link, 'reschedule_approved');
    }

    public function notifyCustomerRescheduleRejected(Booking $booking)
    {
        $userId = $booking->user_id;
        $title = 'Yêu cầu đổi lịch bị từ chối';
        $content = "Yêu cầu đổi lịch cho Booking #{$booking->id} đã bị từ chối.";
        $link = route('account.bookings.index');
        return $this->create($userId, $title, $content, $link, 'reschedule_rejected');
    }

    public function notifyOwnerDebtWarning(int $ownerId, array $summary): Notification
    {
        $usagePercent = round((float) ($summary['usage_percent'] ?? 0), 2);
        $debtAmount = number_format((float) ($summary['debt_amount'] ?? 0), 0, ',', '.');
        $debtLimit = number_format((float) ($summary['debt_limit'] ?? 0), 0, ',', '.');

        $title = 'Cảnh báo công nợ';
        $content = "Công nợ của bạn đã đạt {$usagePercent}% hạn mức ({$debtAmount}đ / {$debtLimit}đ). Vui lòng nạp tiền để tránh bị tạm khóa cơ sở.";

        if ($usagePercent >= 100) {
            $content = "Công nợ của bạn đã vượt hạn mức cho phép ({$debtAmount}đ / {$debtLimit}đ). Cơ sở có thể bị tạm khóa cho đến khi bạn nạp tiền trả nợ.";
        }

        return $this->create($ownerId, $title, $content, route('owner.web.wallet.topup.create'), 'debt_warning');
    }
}
