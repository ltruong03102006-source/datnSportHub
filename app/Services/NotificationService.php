<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Booking;
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
        $link = route('owner.web.reschedule.show', $rescheduleRequest);
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
}
