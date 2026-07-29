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

    /** Khung giờ mặc định hiển thị trên ma trận nhiệt */
    private const HEATMAP_DEFAULT_FROM = 6;
    private const HEATMAP_DEFAULT_TO = 22;

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
     * Lọc ra các lịch đặt được tính doanh thu: đã hoàn tất và đã thu tiền.
     * Giữ cùng quy tắc với ô "Tổng doanh thu" của dashboard để hai số liệu khớp nhau.
     */
    public function payableBookings(Collection $bookings): Collection
    {
        return $bookings
            ->filter(fn (Booking $booking) => $booking->status === 'completed')
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
     *
     * @param  Collection  $grouped  Lịch đặt hợp lệ đã gom theo sân con
     */
    public function bookingCountByCourt(Collection $grouped): Collection
    {
        return $grouped->map(fn (Collection $courtBookings) => $courtBookings->count());
    }

    /**
     * Số khách hàng khác nhau đã đặt từng sân con.
     */
    public function customerCountByCourt(Collection $grouped): Collection
    {
        return $grouped->map(fn (Collection $courtBookings) => $courtBookings->pluck('user_id')->unique()->count());
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
    public function bookedHoursByCourt(Collection $grouped): Collection
    {
        return $grouped->map(fn (Collection $courtBookings) => $courtBookings->sum(fn (Booking $b) => $this->bookedHoursOf($b)));
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
    public function occupancyRateByCourt(Collection $grouped, $courtIds, int $daysInPeriod): Collection
    {
        $bookedHours = $this->bookedHoursByCourt($grouped);
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

    /**
     * Khung giờ được đặt nhiều nhất của từng sân con.
     * Trả về map [court_id => ['08:00' => 5, ...]] đã sắp giảm dần.
     */
    public function peakHoursByCourt(Collection $grouped, int $limit = 3): Collection
    {
        return $grouped->map(function (Collection $courtBookings) use ($limit) {
            return $courtBookings
                ->groupBy(fn (Booking $b) => Carbon::parse($b->start_time)->format('H:i'))
                ->map(fn (Collection $slot) => $slot->count())
                ->sortDesc()
                ->take($limit);
        });
    }

    /**
     * Ma trận nhiệt: mỗi hàng là một sân con, mỗi cột là một khung giờ trong ngày,
     * giá trị là số lượt đặt. Dùng để tô màu đậm nhạt trên giao diện.
     *
     * @return array{hours: array<int>, rows: array<array{name: string, cells: array<int>}>, max: int}
     */
    public function bookingHeatmap(Collection $courts, Collection $bookings): array
    {
        $valid = $this->validBookingsByCourt($bookings);

        // Khung giờ hiển thị: lấy theo dữ liệu thực tế, tối thiểu là khung 6h-22h
        $bookedHours = $bookings
            ->map(fn (Booking $b) => (int) Carbon::parse($b->start_time)->format('H'));

        $from = min(self::HEATMAP_DEFAULT_FROM, $bookedHours->min() ?? self::HEATMAP_DEFAULT_FROM);
        $to = max(self::HEATMAP_DEFAULT_TO, $bookedHours->max() ?? self::HEATMAP_DEFAULT_TO);
        $hours = range($from, $to);

        $max = 0;
        $rows = $courts->map(function ($court) use ($valid, $hours, &$max) {
            $countsByHour = ($valid[$court->id] ?? collect())
                ->groupBy(fn (Booking $b) => (int) Carbon::parse($b->start_time)->format('H'))
                ->map(fn (Collection $slot) => $slot->count());

            $cells = [];
            foreach ($hours as $hour) {
                $count = (int) ($countsByHour[$hour] ?? 0);
                $cells[] = ['hour' => $hour, 'count' => $count];
                $max = max($max, $count);
            }

            return ['id' => $court->id, 'name' => $court->name, 'cells' => $cells];
        })->values()->all();

        // Quy đổi số lượt đặt sang 4 mức đậm nhạt để giao diện chỉ việc tô màu
        foreach ($rows as &$row) {
            foreach ($row['cells'] as &$cell) {
                $cell['level'] = $this->heatLevel($cell['count'], $max);
            }
        }

        return ['hours' => $hours, 'rows' => $rows, 'max' => $max];
    }

    /**
     * Mức đậm nhạt của một ô nhiệt: 0 = không có lượt đặt, 3 = đông nhất.
     */
    private function heatLevel(int $count, int $max): int
    {
        if ($count <= 0) {
            return 0;
        }

        $ratio = $count / max(1, $max);

        return match (true) {
            $ratio <= 0.34 => 1,
            $ratio <= 0.67 => 2,
            default => 3,
        };
    }

    /**
     * Bảng thống kê đầy đủ của từng sân con, sắp xếp theo doanh thu giảm dần.
     * Sân chưa có lượt đặt nào vẫn hiển thị với số 0.
     *
     * @param  Collection  $courts  Danh sách sân con (model Court)
     * @param  Collection  $bookings  Lịch đặt đã lọc theo kỳ của các sân đó
     */
    public function statsByCourt(Collection $courts, Collection $bookings, int $daysInPeriod): Collection
    {
        $courtIds = $courts->pluck('id');

        // Gom lịch đặt hợp lệ theo sân một lần rồi dùng lại cho mọi chỉ số
        $grouped = $this->validBookingsByCourt($bookings);

        $revenue = $this->revenueByCourt($bookings);
        $bookingCount = $this->bookingCountByCourt($grouped);
        $customerCount = $this->customerCountByCourt($grouped);
        $hours = $this->bookedHoursByCourt($grouped);
        $occupancy = $this->occupancyRateByCourt($grouped, $courtIds, $daysInPeriod);
        $peakHours = $this->peakHoursByCourt($grouped);

        return $courts->map(function ($court) use ($revenue, $bookingCount, $customerCount, $hours, $occupancy, $peakHours) {
            return [
                'id' => $court->id,
                'name' => $court->name,
                'status' => $court->status,
                'revenue' => (float) ($revenue[$court->id] ?? 0),
                'bookings_count' => (int) ($bookingCount[$court->id] ?? 0),
                'customers_count' => (int) ($customerCount[$court->id] ?? 0),
                'hours' => round((float) ($hours[$court->id] ?? 0), 1),
                'occupancy_rate' => round((float) ($occupancy[$court->id] ?? 0), 1),
                'peak_hours' => ($peakHours[$court->id] ?? collect())->keys()->all(),
            ];
        })->sortByDesc('revenue')->values();
    }
}
