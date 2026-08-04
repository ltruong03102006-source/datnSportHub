<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingRescheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerBookingRescheduleController extends Controller
{
    public function index(Request $request)
    {
        $requests = BookingRescheduleRequest::query()
            ->with(['booking.court.venue', 'user', 'bookingItem', 'newTimeSlot'])
            ->whereHas('booking.court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
            ->latest()
            ->get()
            ->groupBy(fn (BookingRescheduleRequest $item) => $item->request_code ?: (string) $item->id)
            ->map(function ($group) {
                $first = $group->first();
                $first->setRelation('groupedRequests', $group->values());
                return $first;
            })
            ->values();

        $stats = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
        ];

        return view('owner.reschedules.index', compact('requests', 'stats'));
    }

    public function show(Request $request, string $requestCode)
    {
        $group = $this->requestGroup($requestCode);
        $this->ensureOwner($request, $group->first());

        return view('owner.reschedules.show', [
            'rescheduleRequest' => $group->first(),
            'requests' => $group,
        ]);
    }

    public function approve(Request $request, string $requestCode): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $requestCode): void {
                $group = $this->requestGroup($requestCode, lock: true)
                    ->where('status', 'pending')
                    ->values();

                abort_if($group->isEmpty(), 409, 'Yêu cầu đã được xử lý hoặc không hợp lệ.');

                $this->ensureOwner($request, $group->first());
                $booking = Booking::lockForUpdate()->with('items')->findOrFail($group->first()->booking_id);
                $changingItemIds = $group->pluck('booking_item_id')->filter()->all();

                foreach ($group as $item) {
                    $slot = $item->newTimeSlot;
                    abort_unless($slot && (int) $slot->court_id === (int) $booking->court_id, 422, 'Khung giờ mới không hợp lệ.');
                    abort_if(Carbon::parse($item->new_slot_date->toDateString().' '.$slot->start_time, 'Asia/Ho_Chi_Minh')->isPast(), 422, 'Khung giờ mới đã qua.');
                    abort_if($this->slotTaken($booking, $item->new_slot_date->toDateString(), $slot->start_time, $slot->end_time, $changingItemIds), 409, 'Khung giờ mới đã có người đặt.');
                }

                foreach ($group as $item) {
                    $slot = $item->newTimeSlot;
                    $bookingItem = BookingItem::lockForUpdate()->findOrFail($item->booking_item_id);
                    abort_unless($bookingItem->status === 'reschedule_pending', 409, 'Ca đổi lịch không còn ở trạng thái chờ.');

                    $bookingItem->update([
                        'time_slot_id' => $slot->id,
                        'slot_date' => $item->new_slot_date,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'status' => 'booked',
                    ]);

                    $item->update([
                        'status' => 'approved',
                        'approved_by' => $request->user()->id,
                        'approved_at' => now(),
                        'reviewed_by' => $request->user()->id,
                        'reviewed_at' => now(),
                    ]);
                }

                $this->syncBookingSummary($booking->fresh('items'));

                try {
                    app(\App\Services\NotificationService::class)->notifyCustomerRescheduleApproved($booking);
                } catch (\Throwable) {
                    // ignore notification errors
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Không thể duyệt yêu cầu.');
        }

        return redirect()->route('owner.web.reschedule.index')->with('success', 'Đã duyệt yêu cầu đổi lịch.');
    }

    public function reject(Request $request, string $requestCode): RedirectResponse
    {
        $data = $request->validate([
            'owner_note' => ['nullable', 'string', 'max:1000'],
            'rejected_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $group = $this->requestGroup($requestCode);
        $this->ensureOwner($request, $group->first());

        DB::transaction(function () use ($request, $group, $data): void {
            foreach ($group->where('status', 'pending') as $item) {
                if ($item->booking_item_id) {
                    BookingItem::whereKey($item->booking_item_id)
                        ->where('status', 'reschedule_pending')
                        ->update(['status' => 'booked']);
                }

                $reason = $data['rejected_reason'] ?? $data['owner_note'] ?? null;
                $item->update([
                    'status' => 'rejected',
                    'owner_note' => $reason,
                    'rejected_reason' => $reason,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            }

            try {
                app(\App\Services\NotificationService::class)->notifyCustomerRescheduleRejected($group->first()->booking);
            } catch (\Throwable) {
                // ignore notification errors
            }
        });

        return redirect()->route('owner.web.reschedule.index')->with('success', 'Đã từ chối yêu cầu đổi lịch.');
    }

    private function requestGroup(string $requestCode, bool $lock = false)
    {
        $query = BookingRescheduleRequest::query()
            ->with(['booking.court.venue', 'user', 'bookingItem', 'oldTimeSlot', 'newTimeSlot'])
            ->where(function ($query) use ($requestCode) {
                $query->where('request_code', $requestCode);

                if (ctype_digit($requestCode)) {
                    $query->orWhere('id', (int) $requestCode);
                }
            })
            ->orderBy('old_slot_date')
            ->orderBy('old_start_time');

        if ($lock) {
            $query->lockForUpdate();
        }

        $group = $query->get();
        abort_if($group->isEmpty(), 404);

        return $group;
    }

    private function ensureOwner(Request $request, BookingRescheduleRequest $item): void
    {
        abort_unless(
            $item->booking()->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))->exists(),
            403
        );
    }

    private function slotTaken(Booking $booking, string $date, string $startTime, string $endTime, array $exceptItemIds): bool
    {
        return BookingItem::whereDate('slot_date', $date)
            ->whereIn('status', ['booked', 'reschedule_pending'])
            ->whereNotIn('id', $exceptItemIds)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('booking', function ($query) use ($booking) {
                $query->where('court_id', $booking->court_id)
                    ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->exists();
    }

    private function syncBookingSummary(Booking $booking): void
    {
        $items = $booking->items()->orderBy('slot_date')->orderBy('start_time')->get();

        if ($items->isEmpty()) {
            return;
        }

        $booking->update([
            'slot_date' => $items->pluck('slot_date')->map(fn ($date) => $date->toDateString())->unique()->count() === 1
                ? $items->first()->slot_date
                : $booking->slot_date,
            'start_time' => $items->min('start_time'),
            'end_time' => $items->max('end_time'),
            'time_slot_id' => $items->first()->time_slot_id,
        ]);
    }
}
