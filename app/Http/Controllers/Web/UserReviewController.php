<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Services\BookingCompletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserReviewController extends Controller
{
    public function index(BookingCompletionService $completionService): View
    {
        $completionService->completeExpiredBookings(userId: Auth::id());

        $reviewedBookingIds = Review::query()
            ->where('user_id', Auth::id())
            ->pluck('booking_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $pendingReviews = Booking::query()
            ->select(
                'court_id',
                'slot_date',
                'created_at',
                DB::raw('MIN(id) as id'),
                DB::raw('MIN(start_time) as start_time'),
                DB::raw('MAX(end_time) as end_time'),
                DB::raw('SUM(total_price) as total_price')
            )
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->groupBy('court_id', 'slot_date', 'created_at')
            ->when(
                $reviewedBookingIds,
                fn ($query) => $query->havingRaw(
                    'MIN(id) NOT IN ('.implode(',', $reviewedBookingIds).')'
                )
            )
            ->orderByDesc('slot_date')
            ->orderByDesc('end_time')
            ->paginate(12)
            ->withQueryString();

        $pendingReviews->load('court.venue');

        return view('reviews.pending', compact('pendingReviews'));
    }
}
