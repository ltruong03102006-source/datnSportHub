<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Venue;
use App\Models\Court;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Services\BookingCompletionService;
use App\Services\CourtStatisticsService;
use Carbon\Carbon;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function index(
        Request $request,
        BookingCompletionService $completionService,
        CourtStatisticsService $courtStatistics
    ): View {
        $ownerId = Auth::id();

        $completionService->completeExpiredBookings(ownerId: $ownerId);
        $completionService->settleCompletedBookings(ownerId: $ownerId);
        
        // Retrieve all venues owned by the user
        $allVenues = Venue::where('owner_id', $ownerId)->get();
        
        // Venue Filter
        $selectedVenueId = $request->query('venue_id', 'all');
        if ($selectedVenueId !== 'all' && $allVenues->contains('id', $selectedVenueId)) {
            $venueIds = collect([$selectedVenueId]);
        } else {
            $venueIds = $allVenues->pluck('id');
            $selectedVenueId = 'all';
        }
        
        // Court Filter: chỉ chọn được sân con khi đã chọn 1 cơ sở cụ thể
        $courtsOfVenue = $selectedVenueId !== 'all'
            ? Court::forVenues([$selectedVenueId])->orderedByName()->get()
            : collect();

        $selectedCourtId = $request->query('court_id', 'all');
        if ($selectedCourtId === 'all' || ! $courtsOfVenue->contains('id', (int) $selectedCourtId)) {
            $selectedCourtId = 'all';
        }

        // Date Period Filter
        $period = $request->query('period', 'month');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');
        
        if ($period === 'custom' && $customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
            
            $diffDays = $startDate->diffInDays($endDate);
            $prevStartDate = $startDate->copy()->subDays($diffDays + 1)->startOfDay();
            $prevEndDate = $startDate->copy()->subDay()->endOfDay();
        } else {
            $startDate = match($period) {
                'today' => Carbon::today(),
                'week' => Carbon::now()->startOfWeek(),
                'month' => Carbon::now()->startOfMonth(),
                'year' => Carbon::now()->startOfYear(),
                default => Carbon::now()->startOfMonth(),
            };
            $endDate = match($period) {
                'today' => Carbon::today()->endOfDay(),
                'week' => Carbon::now()->endOfWeek(),
                'month' => Carbon::now()->endOfMonth(),
                'year' => Carbon::now()->endOfYear(),
                default => Carbon::now()->endOfMonth(),
            };

            $prevStartDate = match($period) {
                'today' => Carbon::today()->subDay(),
                'week' => Carbon::now()->subWeek()->startOfWeek(),
                'month' => Carbon::now()->subMonth()->startOfMonth(),
                'year' => Carbon::now()->subYear()->startOfYear(),
                default => Carbon::now()->subMonth()->startOfMonth(),
            };
            $prevEndDate = match($period) {
                'today' => Carbon::today()->subDay()->endOfDay(),
                'week' => Carbon::now()->subWeek()->endOfWeek(),
                'month' => Carbon::now()->subMonth()->endOfMonth(),
                'year' => Carbon::now()->subYear()->endOfYear(),
                default => Carbon::now()->subMonth()->endOfMonth(),
            };
        }

        // 1. Fetch bookings with eager loading for venues and users
        $bookings = Booking::with(['court.venue', 'user'])
            ->whereHas('court', function ($query) use ($venueIds) {
                $query->whereIn('venue_id', $venueIds);
            })
            // Khi chọn 1 sân con cụ thể thì toàn bộ chỉ số chỉ tính trên sân đó
            ->when($selectedCourtId !== 'all', fn ($query) => $query->forCourts([$selectedCourtId]))
            ->whereBetween('slot_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $validBookings = $bookings->filter(fn (Booking $booking) => in_array($booking->status, ['confirmed', 'completed'], true));
        $paidBookings = $validBookings->filter(function (Booking $booking) {
            if (in_array((string) $booking->payment_status, ['paid', 'completed', 'success'], true)) {
                return true;
            }

            $method = strtolower((string) $booking->payment_method);
            // Bao gồm cả booking từ gói
            if (in_array($method, ['cod', 'cash', 'offline', 'package'], true)) {
                return true;
            }

            return false;
        });
        $completedPaidBookings = $paidBookings->where('status', 'completed');

        $packageBookingRevenue = $validBookings
            ->filter(fn (Booking $booking): bool => ! empty($booking->booking_package_id) && strtolower((string) $booking->payment_method) === 'package')
            ->sum(function (Booking $booking): float {
                $ownerEarnings = (float) ($booking->owner_earnings ?? 0);

                return $ownerEarnings > 0 ? $ownerEarnings : (float) $booking->total_price;
            });

        $bookingRevenue = function (Booking $booking): float {
            $ownerEarnings = (float) ($booking->owner_earnings ?? 0);

            if ($booking->booking_package_id && strtolower((string) $booking->payment_method) === 'package') {
                return $ownerEarnings;
            }

            return $ownerEarnings > 0 ? $ownerEarnings : (float) $booking->total_price;
        };

        // 2. Revenue calculation
        $completedBookings = $bookings->where('status', 'completed');
        $totalRevenue = $completedPaidBookings->sum($bookingRevenue);
        
        // 3. Booking Stats
        $totalBookings = $validBookings->count();
        $bookingStatuses = [
            'completed' => $completedBookings->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        // 4. Revenue by Day for Line Chart
        $revenueByDay = $completedPaidBookings->groupBy(function($b) {
            return Carbon::parse($b->slot_date)->format('Y-m-d');
        })->map(function ($row) use ($bookingRevenue) {
            return $row->sum($bookingRevenue);
        });
        $revenueByDay = $revenueByDay->sortKeys();

        // 5. Peak Hours (Khung giờ cao điểm)
        $peakHours = $validBookings->groupBy(function($b) {
            return Carbon::parse($b->start_time)->format('H:i');
        })->map(function ($row) {
            return $row->count();
        })->sortDesc()->take(7);

        // 6. Unique Customers
        $uniqueCustomers = $validBookings->pluck('user_id')->unique()->count();

        // 7. Total Booked Hours
        $totalHours = $validBookings->reduce(function($carry, $b) {
            $start = Carbon::parse($b->start_time);
            $end = Carbon::parse($b->end_time);
            return $carry + (abs($end->diffInMinutes($start)) / 60); 
        }, 0);

        // Calculate Occupancy Rate (Tỷ lệ lấp đầy)
        // Find total available hours in the period
        $daysInPeriod = max(1, $startDate->diffInDays($endDate) + 1); // at least 1 day
        $courtIds = Court::whereIn('venue_id', $venueIds)
            ->when($selectedCourtId !== 'all', fn ($query) => $query->where('id', $selectedCourtId))
            ->pluck('id');
        
        // Calculate daily capacity across all filtered courts based on time_slots
        $totalDailyMinutesCapacity = TimeSlot::whereIn('court_id', $courtIds)->sum('duration_minutes');
        
        if ($totalDailyMinutesCapacity == 0) {
            // Fallback: Assume 14 hours per court per day (e.g. 8AM to 10PM)
            $totalDailyMinutesCapacity = $courtIds->count() * 14 * 60;
        }
        
        $totalAvailableHours = ($totalDailyMinutesCapacity / 60) * $daysInPeriod;
        $occupancyRate = 0;
        if ($totalAvailableHours > 0) {
            $occupancyRate = min(100, ($totalHours / $totalAvailableHours) * 100);
        }

        // 8. Previous Period Comparison
        $prevRevenue = Booking::whereHas('court', function ($query) use ($venueIds) {
                $query->whereIn('venue_id', $venueIds);
            })
            ->when($selectedCourtId !== 'all', fn ($query) => $query->forCourts([$selectedCourtId]))
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->whereIn('payment_status', ['paid', 'completed', 'success'])
                    ->orWhereIn('payment_method', ['cod', 'cash', 'offline']);
            })
            ->whereBetween('slot_date', [$prevStartDate->format('Y-m-d'), $prevEndDate->format('Y-m-d')])
            ->get()
            ->sum($bookingRevenue);

        $revenueChange = 0;
        if ($prevRevenue > 0) {
            $revenueChange = (($totalRevenue - $prevRevenue) / $prevRevenue) * 100;
        } elseif ($totalRevenue > 0) {
            $revenueChange = 100;
        }

        // 9. Top Venues
        $topVenues = $completedPaidBookings->groupBy(function($b) {
            return $b->court->venue->id;
        })->map(function($venueBookings) use ($bookingRevenue) {
            $venue = $venueBookings->first()->court->venue;
            return [
                'name' => $venue->name,
                'revenue' => $venueBookings->sum($bookingRevenue),
                'bookings_count' => $venueBookings->count(),
            ];
        })->sortByDesc('revenue')->take(5)->values();

        // 10. Top Customers
        $topCustomers = $completedPaidBookings->groupBy('user_id')->map(function($userBookings) use ($bookingRevenue) {
            $user = $userBookings->first()->user;
            return [
                'name' => $user->name ?? 'Unknown',
                'email' => $user->email ?? 'N/A',
                'revenue' => $userBookings->sum($bookingRevenue),
                'bookings_count' => $userBookings->count(),
            ];
        })->sortByDesc('revenue')->take(5)->values();


        // 11. Thống kê chi tiết từng sân con của cơ sở đang chọn
        $courtStats = $courtsOfVenue->isNotEmpty()
            ? $courtStatistics->statsByCourt($courtsOfVenue, $bookings, $daysInPeriod)
            : collect();

        $chartData = [
            'revenueDates' => $revenueByDay->keys()->toArray(),
            'revenueValues' => $revenueByDay->values()->toArray(),
            'statusLabels' => ['Hoàn tất', 'Đã xác nhận', 'Chờ xử lý', 'Đã hủy'],
            'statusValues' => array_values($bookingStatuses),
            'peakHourLabels' => $peakHours->keys()->toArray(),
            'peakHourValues' => $peakHours->values()->toArray()
        ];

        return view('owner.dashboard', compact(
            'period',
            'customStart',
            'customEnd',
            'allVenues',
            'selectedVenueId',
            'courtsOfVenue',
            'selectedCourtId',
            'courtStats',
            'totalRevenue',
            'packageBookingRevenue',
            'totalBookings',
            'bookingStatuses',
            'uniqueCustomers',
            'totalHours',
            'occupancyRate',
            'revenueChange',
            'topVenues',
            'topCustomers',
            'chartData',
            'peakHours'
        ));
    }

}
