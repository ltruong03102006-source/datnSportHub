<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Collection;

/**
 * Tính các chỉ số thống kê cho từng sân con (court) trong một cơ sở.
 * Dữ liệu đầu vào là danh sách sân và danh sách lịch đặt đã lọc sẵn theo kỳ.
 */
class CourtStatisticsService
{
    /**
     * Doanh thu chủ sân nhận được từ một lịch đặt.
     * Booking từ gói chỉ tính phần owner_earnings, còn lại ưu tiên owner_earnings
     * và lùi về tổng tiền khi chưa chia hoa hồng.
     */
    public function revenueOf(Booking $booking): float
    {
        $ownerEarnings = (float) ($booking->owner_earnings ?? 0);

        if ($booking->booking_package_id && strtolower((string) $booking->payment_method) === 'package') {
            return $ownerEarnings;
        }

        return $ownerEarnings > 0 ? $ownerEarnings : (float) $booking->total_price;
    }

    /**
     * Lọc ra các lịch đặt được tính doanh thu: hợp lệ + đã thu tiền.
     */
    public function payableBookings(Collection $bookings): Collection
    {
        return $bookings
            ->filter(fn (Booking $booking) => in_array($booking->status, Booking::VALID_STATUSES, true))
            ->filter(fn (Booking $booking) => $booking->isPaid());
    }

    /**
     * Doanh thu của từng sân con, trả về map [court_id => doanh thu].
     */
    public function revenueByCourt(Collection $bookings): Collection
    {
        return $this->payableBookings($bookings)
            ->groupBy('court_id')
            ->map(fn (Collection $courtBookings) => $courtBookings->sum(fn (Booking $b) => $this->revenueOf($b)));
    }

    /**
     * Các lịch đặt hợp lệ (đã xác nhận/hoàn tất) gom theo sân con.
     */
    public function validBookingsByCourt(Collection $bookings): Collection
    {
        return $bookings
            ->filter(fn (Booking $booking) => in_array($booking->status, Booking::VALID_STATUSES, true))
            ->groupBy('court_id');
    }

    /**
     * Số lượt đặt của từng sân con.
     */
    public function bookingCountByCourt(Collection $bookings): Collection
    {
        return $this->validBookingsByCourt($bookings)->map(fn (Collection $courtBookings) => $courtBookings->count());
    }

    /**
     * Số khách hàng khác nhau đã đặt từng sân con.
     */
    public function customerCountByCourt(Collection $bookings): Collection
    {
        return $this->validBookingsByCourt($bookings)
            ->map(fn (Collection $courtBookings) => $courtBookings->pluck('user_id')->unique()->count());
    }
}
