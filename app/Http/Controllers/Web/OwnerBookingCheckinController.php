<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerBookingCheckinController extends Controller
{
    public function index(Request $request): View
    {
        $ownerId = Auth::id();
        $today = Carbon::today('Asia/Ho_Chi_Minh')->toDateString();

        $bookings = Booking::query()
            ->with(['court.venue', 'user', 'checkedInBy', 'noShowBy'])
            ->whereDate('slot_date', $today)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $ownerId))
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $stats = [
            'total' => $bookings->count(),
            'waiting' => $bookings
                ->whereNull('checked_in_at')
                ->whereNull('no_show_at')
                ->count(),
            'checked_in' => $bookings->whereNotNull('checked_in_at')->count(),
            'no_show' => $bookings->whereNotNull('no_show_at')->count(),
        ];

        return view('owner.checkins.index', [
            'bookings' => $bookings,
            'stats' => $stats,
            'today' => $today,
        ]);
    }
}
