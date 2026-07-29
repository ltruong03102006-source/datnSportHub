<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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

    public function checkIn(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureOwnerBooking($booking);

        if (! $this->isTodayBooking($booking)) {
            return back()->with('error', 'Chỉ có thể check-in booking trong ngày hôm nay.');
        }

        if (! $this->hasCheckinStatus($booking)) {
            return back()->with('error', 'Chỉ có thể check-in booking đã xác nhận hoặc đã hoàn thành.');
        }

        if ($booking->no_show_at !== null) {
            return back()->with('error', 'Booking này đã được đánh dấu khách không đến.');
        }

        if ($booking->checked_in_at !== null) {
            return back()->with('success', 'Booking này đã được check-in trước đó.');
        }

        $validated = $this->validateCheckinNote($request);

        $booking->update([
            'checked_in_at' => now('Asia/Ho_Chi_Minh'),
            'checked_in_by' => $request->user()->id,
            'checkin_note' => $validated['checkin_note'] ?? null,
        ]);

        $booking->recordStatusChange(
            $request->user()->id,
            $booking->status,
            $booking->status,
            'Chủ sân check-in khách đến sân'.($booking->checkin_note ? ': '.$booking->checkin_note : null),
            now('Asia/Ho_Chi_Minh')
        );

        return back()->with('success', "Đã check-in booking #{$booking->id}.");
    }

    public function markNoShow(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureOwnerBooking($booking);

        if (! $this->isTodayBooking($booking)) {
            return back()->with('error', 'Chỉ có thể đánh dấu không đến cho booking trong ngày hôm nay.');
        }

        if (! $this->hasCheckinStatus($booking)) {
            return back()->with('error', 'Chỉ có thể đánh dấu không đến cho booking đã xác nhận hoặc đã hoàn thành.');
        }

        if ($booking->checked_in_at !== null) {
            return back()->with('error', 'Booking này đã check-in nên không thể đánh dấu không đến.');
        }

        if ($booking->no_show_at !== null) {
            return back()->with('success', 'Booking này đã được đánh dấu khách không đến trước đó.');
        }

        $validated = $this->validateCheckinNote($request);

        $booking->update([
            'no_show_at' => now('Asia/Ho_Chi_Minh'),
            'no_show_by' => $request->user()->id,
            'checkin_note' => $validated['checkin_note'] ?? $booking->checkin_note,
        ]);

        $booking->recordStatusChange(
            $request->user()->id,
            $booking->status,
            $booking->status,
            'Chủ sân đánh dấu khách không đến'.($booking->checkin_note ? ': '.$booking->checkin_note : null),
            now('Asia/Ho_Chi_Minh')
        );

        return back()->with('success', "Đã đánh dấu khách không đến cho booking #{$booking->id}.");
    }

    private function ensureOwnerBooking(Booking $booking): void
    {
        abort_unless(
            $booking->court()
                ->whereHas('venue', fn ($query) => $query->where('owner_id', Auth::id()))
                ->exists(),
            403
        );
    }

    private function hasCheckinStatus(Booking $booking): bool
    {
        return in_array($booking->status, ['confirmed', 'completed'], true);
    }

    private function isTodayBooking(Booking $booking): bool
    {
        return Carbon::parse($booking->slot_date, 'Asia/Ho_Chi_Minh')
            ->isSameDay(Carbon::today('Asia/Ho_Chi_Minh'));
    }

    private function validateCheckinNote(Request $request): array
    {
        return $request->validate([
            'checkin_note' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
