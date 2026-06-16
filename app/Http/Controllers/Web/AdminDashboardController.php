<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard tổng quan với dữ liệu thật
     */
    public function index(): View
    {
        $today = Carbon::today();
        $currentYear = date('Y');

        // 1. Thống kê cơ bản (Row 1 & 2)
        $totalUsers = User::count();
        $totalVenues = Venue::count();
        $totalBookings = Booking::count();
        
        // Doanh thu (Chỉ tính các booking có trạng thái hoàn thành hoặc đã thanh toán)
        $totalRevenue = Booking::whereIn('status', ['completed', 'confirmed'])->sum('total_price') ?? 0;
        
        $bookingsToday = Booking::whereDate('created_at', $today)->count();
        $usersToday = User::whereDate('created_at', $today)->count();
        $venuesToday = Venue::whereDate('created_at', $today)->count();
        
        // Tính rating trung bình
        $avgRating = Review::avg('rating') ?? 0;

        // 2. Biểu đồ Đặt sân theo tháng (12 tháng của năm hiện tại)
        $monthlyBookings = Booking::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('count', 'month')->toArray();
        
        $chartBookingsMonthly = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartBookingsMonthly[] = $monthlyBookings[$i] ?? 0;
        }
        
        // Biểu đồ: Thống kê môn thể thao
        $sportsStats = DB::table('bookings')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->join('sports', 'venues.sport_id', '=', 'sports.id')
            ->select('sports.name', DB::raw('COUNT(bookings.id) as count'))
            ->groupBy('sports.name')
            ->pluck('count', 'name')->toArray();

        // Đảm bảo có dữ liệu mẫu nếu DB trống
        if (empty($sportsStats)) {
            $chartSports = ['Bóng đá' => 0, 'Cầu lông' => 0, 'Tennis' => 0, 'Bóng rổ' => 0];
        } else {
            // Tính phần trăm nếu cần, ở đây trả về số lượng, js tự tính tỉ lệ
            $chartSports = $sportsStats;
        }

        // Biểu đồ: Xu hướng doanh thu theo tháng
        $monthlyRevenue = Booking::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereIn('status', ['completed', 'confirmed'])
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('total', 'month')->toArray();

        $chartRevenueTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            // Chuyển đổi sang triệu VNĐ (giả định) để biểu đồ không quá to, hoặc giữ nguyên
            $chartRevenueTrend[] = isset($monthlyRevenue[$i]) ? $monthlyRevenue[$i] : 0;
        }

        // 3. Top Sân Thể Thao (Tính bằng số lượt đặt)
        $topVenuesRaw = DB::table('venues')
            ->join('courts', 'venues.id', '=', 'courts.venue_id')
            ->join('bookings', 'courts.id', '=', 'bookings.court_id')
            ->join('sports', 'venues.sport_id', '=', 'sports.id')
            ->select(
                'venues.id',
                'venues.name',
                'sports.name as sport_name',
                DB::raw('COUNT(bookings.id) as booking_count'),
                DB::raw('SUM(bookings.total_price) as total_revenue')
            )
            ->groupBy('venues.id', 'venues.name', 'sports.name')
            ->orderByDesc('booking_count')
            ->take(5)
            ->get();

        $topVenues = [];
        $rank = 1;
        foreach ($topVenuesRaw as $v) {
            // Tính rating cho từng sân
            $rating = Review::whereHas('court', function($q) use ($v) {
                $q->where('venue_id', $v->id);
            })->avg('rating') ?? 0;

            $topVenues[] = (object)[
                'rank' => $rank++,
                'name' => $v->name,
                'type' => $v->sport_name,
                'bookings' => $v->booking_count,
                'revenue' => number_format($v->total_revenue) . 'đ',
                'rating' => number_format($rating, 1)
            ];
        }

        // Danh sách Top Chủ sân tiêu biểu
        $topOwnersRaw = DB::table('users')
            ->join('venues', 'users.id', '=', 'venues.owner_id')
            ->leftJoin('courts', 'venues.id', '=', 'courts.venue_id')
            ->leftJoin('bookings', 'courts.id', '=', 'bookings.court_id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT venues.id) as venue_count'),
                DB::raw('COUNT(bookings.id) as booking_count'),
                DB::raw('SUM(bookings.total_price) as total_revenue')
            )
            ->where('users.role', 'owner')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $topOwners = [];
        foreach ($topOwnersRaw as $o) {
            $topOwners[] = (object)[
                'name' => $o->name,
                'avatar' => 'https://ui-avatars.com/api/?name='.urlencode($o->name).'&background=random',
                'stats' => $o->venue_count . ' sân • ' . $o->booking_count . ' booking',
                'revenue' => number_format($o->total_revenue ?? 0) . 'đ'
            ];
        }

        // 4. Danh sách Booking gần đây
        $recentBookings = Booking::with(['user', 'court.venue'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Mật độ sân khu vực
        $hanoi = Venue::where('address', 'like', '%Hà Nội%')->count();
        $hcm = Venue::where('address', 'like', '%Hồ Chí Minh%')->orWhere('address', 'like', '%HCM%')->count();
        $danang = Venue::where('address', 'like', '%Đà Nẵng%')->count();
        $haiphong = Venue::where('address', 'like', '%Hải Phòng%')->count();
        $cantho = Venue::where('address', 'like', '%Cần Thơ%')->count();

        $regionDensity = [
            'Hà Nội' => $hanoi,
            'TP HCM' => $hcm,
            'Đà Nẵng' => $danang,
            'Hải Phòng' => $haiphong,
            'Cần Thơ' => $cantho,
        ];
        
        // Sắp xếp giảm dần
        arsort($regionDensity);

        return view('admin.dashboard', compact(
            'totalUsers', 'totalVenues', 'totalBookings', 'totalRevenue',
            'bookingsToday', 'usersToday', 'venuesToday', 'avgRating',
            'chartBookingsMonthly', 'chartSports', 'chartRevenueTrend',
            'topVenues', 'topOwners', 'recentBookings', 'regionDensity'
        ));
    }
}
