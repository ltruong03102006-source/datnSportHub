<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $currentYear = (int) date('Y');
        $validBookingStatuses = ['pending', 'confirmed', 'completed'];

        $totalUsers = User::count();
        $totalVenues = Venue::count();
        $totalBookings = Booking::query()
            ->whereIn('status', $validBookingStatuses)
            ->count();

        $settledBookingQuery = Booking::query();

        if (Schema::hasColumn('bookings', 'settlement_status')) {
            $settledBookingQuery->where('settlement_status', 'settled');
        } else {
            $settledBookingQuery->where('status', 'completed');
        }

        $platformFeeColumn = Schema::hasColumn('bookings', 'platform_fee')
            ? 'platform_fee'
            : (Schema::hasColumn('bookings', 'commission_amount') ? 'commission_amount' : null);

        $totalRevenue = $platformFeeColumn
            ? (clone $settledBookingQuery)->sum($platformFeeColumn)
            : 0;

        $gmv = (clone $settledBookingQuery)->sum('total_price') ?? 0;

        $bookingsToday = Booking::query()
            ->whereIn('status', $validBookingStatuses)
            ->whereDate('created_at', $today)
            ->count();

        $usersToday = User::whereDate('created_at', $today)->count();
        $venuesToday = Venue::whereDate('created_at', $today)->count();
        $avgRating = Review::avg('rating') ?? 0;

        $monthlyBookings = Booking::query()
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', $currentYear)
            ->whereIn('status', $validBookingStatuses)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $chartBookingsMonthly = [];
        for ($month = 1; $month <= 12; $month++) {
            $chartBookingsMonthly[] = $monthlyBookings[$month] ?? 0;
        }

        $sportsStats = DB::table('bookings')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->join('sports', 'venues.sport_id', '=', 'sports.id')
            ->select('sports.name', DB::raw('COUNT(bookings.id) as count'))
            ->whereIn('bookings.status', $validBookingStatuses)
            ->groupBy('sports.name')
            ->pluck('count', 'name')
            ->toArray();

        $chartSports = empty($sportsStats)
            ? ['Bóng đá' => 0, 'Cầu lông' => 0, 'Tennis' => 0, 'Bóng rổ' => 0]
            : $sportsStats;

        if ($platformFeeColumn) {
            $monthlyRevenueQuery = Booking::query()
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw("SUM({$platformFeeColumn}) as total")
                )
                ->whereYear('created_at', $currentYear);

            if (Schema::hasColumn('bookings', 'settlement_status')) {
                $monthlyRevenueQuery->where('settlement_status', 'settled');
            } else {
                $monthlyRevenueQuery->where('status', 'completed');
            }

            $monthlyRevenue = $monthlyRevenueQuery
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();
        } else {
            $monthlyRevenue = [];
        }

        $chartRevenueTrend = [];
        for ($month = 1; $month <= 12; $month++) {
            $chartRevenueTrend[] = $monthlyRevenue[$month] ?? 0;
        }

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
            ->whereIn('bookings.status', $validBookingStatuses)
            ->groupBy('venues.id', 'venues.name', 'sports.name')
            ->orderByDesc('booking_count')
            ->take(5)
            ->get();

        $topVenues = [];
        $rank = 1;

        foreach ($topVenuesRaw as $venue) {
            $rating = Review::whereHas('court', function ($query) use ($venue) {
                $query->where('venue_id', $venue->id);
            })->avg('rating') ?? 0;

            $topVenues[] = (object) [
                'rank' => $rank++,
                'name' => $venue->name,
                'type' => $venue->sport_name,
                'bookings' => $venue->booking_count,
                'revenue' => number_format($venue->total_revenue ?? 0) . 'đ',
                'rating' => number_format($rating, 1),
            ];
        }

        $topOwnersRaw = DB::table('users')
            ->join('venues', 'users.id', '=', 'venues.owner_id')
            ->leftJoin('courts', 'venues.id', '=', 'courts.venue_id')
            ->leftJoin('bookings', function ($join) use ($validBookingStatuses) {
                $join->on('courts.id', '=', 'bookings.court_id')
                    ->whereIn('bookings.status', $validBookingStatuses);
            })
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

        foreach ($topOwnersRaw as $owner) {
            $topOwners[] = (object) [
                'name' => $owner->name,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($owner->name) . '&background=random',
                'stats' => $owner->venue_count . ' sân • ' . $owner->booking_count . ' booking hợp lệ',
                'revenue' => number_format($owner->total_revenue ?? 0) . 'đ',
            ];
        }

        $allBookings = Booking::with(['user', 'court.venue'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'bookings_page')
            ->withQueryString();

        $regionDensity = [
            'Hà Nội' => Venue::where('address', 'like', '%Hà Nội%')
                ->orWhere('address', 'like', '%Ha Noi%')
                ->count(),
            'TP HCM' => Venue::where('address', 'like', '%Hồ Chí Minh%')
                ->orWhere('address', 'like', '%TP.HCM%')
                ->orWhere('address', 'like', '%HCM%')
                ->count(),
            'Đà Nẵng' => Venue::where('address', 'like', '%Đà Nẵng%')
                ->orWhere('address', 'like', '%Da Nang%')
                ->count(),
            'Hải Phòng' => Venue::where('address', 'like', '%Hải Phòng%')
                ->orWhere('address', 'like', '%Hai Phong%')
                ->count(),
            'Cần Thơ' => Venue::where('address', 'like', '%Cần Thơ%')
                ->orWhere('address', 'like', '%Can Tho%')
                ->count(),
        ];

        arsort($regionDensity);

        return view('admin.dashboard', compact(
            'currentYear',
            'totalUsers',
            'totalVenues',
            'totalBookings',
            'totalRevenue',
            'gmv',
            'bookingsToday',
            'usersToday',
            'venuesToday',
            'avgRating',
            'chartBookingsMonthly',
            'chartSports',
            'chartRevenueTrend',
            'topVenues',
            'topOwners',
            'allBookings',
            'regionDensity'
        ));
    }
}
