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
            $settledBookingQuery->whereIn('status', ['completed', 'confirmed']);

            if (Schema::hasColumn('bookings', 'payment_status')) {
                $settledBookingQuery->where('payment_status', 'paid');
            }
        }

        $gmvColumn = Schema::hasColumn('bookings', 'gross_amount')
            ? 'gross_amount'
            : 'total_price';

        $platformFeeColumn = Schema::hasColumn('bookings', 'platform_fee')
            ? 'platform_fee'
            : (Schema::hasColumn('bookings', 'commission_amount') ? 'commission_amount' : null);

        $totalRevenue = $platformFeeColumn
            ? (float) (clone $settledBookingQuery)->sum($platformFeeColumn)
            : 0;

        // Tính toán GMV đồng bộ với Tổng Quan Tài Chính (đơn lẻ + doanh thu bán gói)
        $singleBookingQuery = (clone $settledBookingQuery)->where(function ($q) {
            $q->whereNull('booking_package_id');
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $q->where('payment_method', '!=', 'package');
            }
        });

        $packageBookingQuery = (clone $settledBookingQuery)->where(function ($q) {
            $q->whereNotNull('booking_package_id');
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $q->orWhere('payment_method', 'package');
            }
        });

        $packageModelQuery = Schema::hasTable('booking_packages') ? \App\Models\BookingPackage::query() : null;
        $totalPackageSalesAmount = $packageModelQuery ? (float) (clone $packageModelQuery)->whereIn('status', ['active', 'completed', 'paused'])->sum('final_amount') : 0;

        $singleGmv = Schema::hasColumn('bookings', $gmvColumn) ? (float) (clone $singleBookingQuery)->sum($gmvColumn) : 0;
        $rawPackageGmv = Schema::hasColumn('bookings', $gmvColumn) ? (float) (clone $packageBookingQuery)->sum($gmvColumn) : 0;
        $packageGmv = $totalPackageSalesAmount > 0 ? $totalPackageSalesAmount : $rawPackageGmv;

        $gmv = $singleGmv + $packageGmv;

        $bookingsToday = Booking::query()
            ->whereIn('status', $validBookingStatuses)
            ->whereDate('created_at', $today)
            ->count();

        $usersToday = User::whereDate('created_at', $today)->count();
        $venuesToday = Venue::whereDate('created_at', $today)->count();
        $avgRating = Review::avg('rating') ?? 0;

        $monthlySingleBookings = Booking::query()
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', $currentYear)
            ->whereIn('status', $validBookingStatuses)
            ->where(function ($q) {
                $q->whereNull('booking_package_id');
                if (Schema::hasColumn('bookings', 'payment_method')) {
                    $q->where('payment_method', '!=', 'package');
                }
            })
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthlyPackagesCount = [];
        if ($packageModelQuery) {
            $monthlyPackagesCount = (clone $packageModelQuery)
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereIn('status', ['active', 'completed', 'paused'])
                ->whereYear('created_at', $currentYear)
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();
        }

        $chartBookingsMonthly = [];
        for ($month = 1; $month <= 12; $month++) {
            $chartBookingsMonthly[] = ($monthlySingleBookings[$month] ?? 0) + ($monthlyPackagesCount[$month] ?? 0);
        }

        $singleSportsStats = DB::table('bookings')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->join('sports', 'venues.sport_id', '=', 'sports.id')
            ->select('sports.name', DB::raw('COUNT(bookings.id) as count'))
            ->whereIn('bookings.status', $validBookingStatuses)
            ->where(function ($q) {
                $q->whereNull('bookings.booking_package_id');
                if (Schema::hasColumn('bookings', 'payment_method')) {
                    $q->where('bookings.payment_method', '!=', 'package');
                }
            })
            ->groupBy('sports.name')
            ->pluck('count', 'name')
            ->toArray();

        $packageSportsStats = [];
        if (Schema::hasTable('booking_packages')) {
            $packageSportsStats = DB::table('booking_packages')
                ->join('venues', 'booking_packages.venue_id', '=', 'venues.id')
                ->join('sports', 'venues.sport_id', '=', 'sports.id')
                ->select('sports.name', DB::raw('COUNT(booking_packages.id) as count'))
                ->whereIn('booking_packages.status', ['active', 'completed', 'paused'])
                ->groupBy('sports.name')
                ->pluck('count', 'name')
                ->toArray();
        }

        $sportsStats = [];
        $allSportsNames = array_unique(array_merge(array_keys($singleSportsStats), array_keys($packageSportsStats)));
        foreach ($allSportsNames as $sName) {
            $sportsStats[$sName] = ($singleSportsStats[$sName] ?? 0) + ($packageSportsStats[$sName] ?? 0);
        }

        $chartSports = empty($sportsStats)
            ? ['Bóng đá' => 0, 'Cầu lông' => 0, 'Tennis' => 0, 'Bóng rổ' => 0]
            : $sportsStats;

        if ($platformFeeColumn) {
            $monthlyRevenueQuery = (clone $settledBookingQuery)
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw("SUM({$platformFeeColumn}) as total")
                )
                ->whereYear('created_at', $currentYear);

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

        $monthlySingleGmv = (clone $singleBookingQuery)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw("SUM({$gmvColumn}) as total")
            )
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyPackageSales = [];
        if ($packageModelQuery) {
            $monthlyPackageSales = (clone $packageModelQuery)
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw("SUM(final_amount) as total")
                )
                ->whereIn('status', ['active', 'completed', 'paused'])
                ->whereYear('created_at', $currentYear)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();
        }

        $chartGmvTrend = [];
        for ($month = 1; $month <= 12; $month++) {
            $sVal = (float) ($monthlySingleGmv[$month] ?? 0);
            $pVal = (float) ($monthlyPackageSales[$month] ?? 0);
            $chartGmvTrend[] = $sVal + $pVal;
        }

        $thisMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;
        $thisMonthYear = Carbon::now()->year;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        $thisMonthBookings = Booking::whereIn('status', $validBookingStatuses)
            ->whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisMonthYear)
            ->count();

        $lastMonthBookings = Booking::whereIn('status', $validBookingStatuses)
            ->whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->count();

        $bookingGrowth = $lastMonthBookings > 0
            ? round((($thisMonthBookings - $lastMonthBookings) / $lastMonthBookings) * 100, 1)
            : ($thisMonthBookings > 0 ? 100 : 0);

        $pendingVenuesCount = Schema::hasColumn('venues', 'status')
            ? Venue::where('status', 'pending')->count()
            : 0;

        $pendingBookingsCount = Booking::where('status', 'pending')->count();

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

            $singleCount = Booking::whereHas('court', function ($q) use ($venue) {
                    $q->where('venue_id', $venue->id);
                })
                ->whereIn('status', $validBookingStatuses)
                ->where(function ($q) {
                    $q->whereNull('booking_package_id');
                    if (Schema::hasColumn('bookings', 'payment_method')) {
                        $q->where('payment_method', '!=', 'package');
                    }
                })
                ->count();

            $pkgCount = Schema::hasTable('booking_packages')
                ? \App\Models\BookingPackage::where('venue_id', $venue->id)
                    ->whereIn('status', ['active', 'completed', 'paused'])
                    ->count()
                : 0;

            $pkgRevenue = Schema::hasTable('booking_packages')
                ? (float) \App\Models\BookingPackage::where('venue_id', $venue->id)
                    ->whereIn('status', ['active', 'completed', 'paused'])
                    ->sum('final_amount')
                : 0;

            $totalVenRevenue = ($venue->total_revenue ?? 0) + $pkgRevenue;

            $topVenues[] = (object) [
                'rank' => $rank++,
                'name' => $venue->name,
                'type' => $venue->sport_name,
                'bookings' => $singleCount . ' ca lẻ • ' . $pkgCount . ' gói',
                'revenue' => number_format($totalVenRevenue) . 'đ',
                'rating' => number_format($rating, 1),
            ];
        }

        $topOwnersRaw = User::where('role', 'owner')
            ->withCount('venues')
            ->get();

        $topOwnersList = [];
        foreach ($topOwnersRaw as $owner) {
            $ownerVenueIds = Venue::where('owner_id', $owner->id)->pluck('id')->toArray();

            if (empty($ownerVenueIds)) {
                $topOwnersList[] = (object) [
                    'name' => $owner->name,
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($owner->name) . '&background=random',
                    'venue_count' => $owner->venues_count,
                    'single_count' => 0,
                    'pkg_count' => 0,
                    'total_revenue' => 0,
                    'stats' => $owner->venues_count . ' sân • 0 ca lẻ • 0 gói',
                    'revenue' => '0đ',
                ];
                continue;
            }

            $singleCount = Booking::whereHas('court', function ($q) use ($ownerVenueIds) {
                    $q->whereIn('venue_id', $ownerVenueIds);
                })
                ->whereIn('status', $validBookingStatuses)
                ->where(function ($q) {
                    $q->whereNull('booking_package_id');
                    if (Schema::hasColumn('bookings', 'payment_method')) {
                        $q->where('payment_method', '!=', 'package');
                    }
                })
                ->count();

            $singleRevenue = (float) Booking::whereHas('court', function ($q) use ($ownerVenueIds) {
                    $q->whereIn('venue_id', $ownerVenueIds);
                })
                ->whereIn('status', $validBookingStatuses)
                ->sum('total_price');

            $pkgCount = Schema::hasTable('booking_packages')
                ? \App\Models\BookingPackage::whereIn('venue_id', $ownerVenueIds)
                    ->whereIn('status', ['active', 'completed', 'paused'])
                    ->count()
                : 0;

            $pkgRevenue = Schema::hasTable('booking_packages')
                ? (float) \App\Models\BookingPackage::whereIn('venue_id', $ownerVenueIds)
                    ->whereIn('status', ['active', 'completed', 'paused'])
                    ->sum('final_amount')
                : 0;

            $totalOwnerRevenue = $singleRevenue + $pkgRevenue;

            $topOwnersList[] = (object) [
                'name' => $owner->name,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($owner->name) . '&background=random',
                'venue_count' => $owner->venues_count,
                'single_count' => $singleCount,
                'pkg_count' => $pkgCount,
                'total_revenue' => $totalOwnerRevenue,
                'stats' => $owner->venues_count . ' sân • ' . $singleCount . ' ca lẻ • ' . $pkgCount . ' gói',
                'revenue' => number_format($totalOwnerRevenue) . 'đ',
            ];
        }

        usort($topOwnersList, fn($a, $b) => $b->total_revenue <=> $a->total_revenue);
        $topOwners = array_slice($topOwnersList, 0, 5);

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

        $singleBookingsCount = Booking::whereIn('status', $validBookingStatuses)
            ->whereNull('booking_package_id')
            ->count();

        $packageBookingsCount = $packageModelQuery
            ? (clone $packageModelQuery)->whereIn('status', ['active', 'completed', 'paused'])->count()
            : 0;

        $totalPackagesCount = $packageBookingsCount;

        return view('admin.dashboard', compact(
            'currentYear',
            'totalUsers',
            'totalVenues',
            'totalBookings',
            'singleBookingsCount',
            'packageBookingsCount',
            'totalPackagesCount',
            'totalRevenue',
            'gmv',
            'bookingsToday',
            'usersToday',
            'venuesToday',
            'avgRating',
            'chartBookingsMonthly',
            'chartSports',
            'chartRevenueTrend',
            'chartGmvTrend',
            'bookingGrowth',
            'pendingVenuesCount',
            'pendingBookingsCount',
            'topVenues',
            'topOwners',
            'allBookings',
            'regionDensity'
        ));
    }
}
