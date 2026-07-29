<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Tính các chỉ số thống kê cho từng sân con (court) trong một cơ sở.
 * Dữ liệu đầu vào là danh sách sân và danh sách lịch đặt đã lọc sẵn theo kỳ.
 */
class CourtStatisticsService
{
    /** Số giờ mở cửa mặc định mỗi ngày khi sân chưa cấu hình khung giờ */
    private const FALLBACK_DAILY_HOURS = 14;

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

    /**
     * Số giờ đã được đặt của một lịch đặt.
     */
    public function bookedHoursOf(Booking $booking): float
    {
        $start = Carbon::parse($booking->start_time);
        $end = Carbon::parse($booking->end_time);

        return abs($end->diffInMinutes($start)) / 60;
    }

    /**
     * Tổng số giờ đã đặt của từng sân con.
     */
    public function bookedHoursByCourt(Collection $bookings): Collection
    {
        return $this->validBookingsByCourt($bookings)
            ->map(fn (Collection $courtBookings) => $courtBookings->sum(fn (Booking $b) => $this->bookedHoursOf($b)));
    }

    /**
     * Số giờ có thể cho thuê mỗi ngày của từng sân con, lấy từ khung giờ đã cấu hình.
     * Sân chưa cấu hình khung giờ thì tạm tính 14 giờ/ngày.
     */
    public function dailyCapacityHoursByCourt($courtIds): Collection
    {
        $minutes = TimeSlot::forCourts($courtIds)
            ->selectRaw('court_id, SUM(duration_minutes) as total_minutes')
            ->groupBy('court_id')
            ->pluck('total_minutes', 'court_id');

        return collect($courtIds)->mapWithKeys(function ($courtId) use ($minutes) {
            $hours = ((float) ($minutes[$courtId] ?? 0)) / 60;

            return [$courtId => $hours > 0 ? $hours : self::FALLBACK_DAILY_HOURS];
        });
    }

    /**
     * Tỷ lệ lấp đầy (%) của từng sân con trong kỳ.
     */
    public function occupancyRateByCourt(Collection $bookings, $courtIds, int $daysInPeriod): Collection
    {
        $bookedHours = $this->bookedHoursByCourt($bookings);
        $capacity = $this->dailyCapacityHoursByCourt($courtIds);
        $days = max(1, $daysInPeriod);

        return $capacity->map(function (float $dailyHours, $courtId) use ($bookedHours, $days) {
            $available = $dailyHours * $days;

            if ($available <= 0) {
                return 0.0;
            }

            return min(100, ((float) ($bookedHours[$courtId] ?? 0) / $available) * 100);
        });
    }
}
