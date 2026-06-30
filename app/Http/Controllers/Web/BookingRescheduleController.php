<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRescheduleRequest;
use App\Models\TimeSlot;
use App\Models\BookingRescheduleRequestSlot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingRescheduleController extends Controller
{
    public function create(Booking $booking)
    {
        if ($message = $this->eligibilityError($booking)) {
            return redirect()->route('account.bookings.index')->with('error', $message);
        }
        $sourceBookings = $this->sourceBookings($booking);

        return view('bookings.reschedule-create', [
            'booking' => $booking->load('court.venue'),
            'sourceBookings' => $sourceBookings,
            'slotCount' => $sourceBookings->count(),
        ]);
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        if ($message = $this->eligibilityError($booking)) {
            return redirect()->route('account.bookings.index')->with('error', $message);
        }
        $data = $request->validate(['new_slot_date' => ['required','date','after_or_equal:today'], 'new_time_slot_ids' => ['required','array','min:1'], 'new_time_slot_ids.*'=>['integer','distinct'], 'reason' => ['nullable','string','max:1000']]);
        $sourceBookings=$this->sourceBookings($booking);
        if (count($data['new_time_slot_ids']) !== $sourceBookings->count()) return back()->withInput()->with('error', 'Số ca mới phải đúng bằng số ca đã đặt ('.$sourceBookings->count().' ca).');
        $slots=TimeSlot::whereIn('id',$data['new_time_slot_ids'])->where('court_id',$booking->court_id)->orderBy('start_time')->get();
        if($slots->count()!==$sourceBookings->count()) return back()->withInput()->with('error','Khung giờ mới không hợp lệ.');
        foreach($slots as $slot){ if(Carbon::parse($data['new_slot_date'].' '.$slot->start_time,'Asia/Ho_Chi_Minh')->lte(now('Asia/Ho_Chi_Minh')) || $this->slotTaken($booking,$data['new_slot_date'],$slot)) return back()->withInput()->with('error','Có khung giờ mới không còn trống hoặc đã qua giờ.'); }
        $reschedule = DB::transaction(function() use($booking,$request,$data,$slots,$sourceBookings){ $item=BookingRescheduleRequest::create(['booking_id'=>$booking->id,'user_id'=>$request->user()->id,'old_slot_date'=>$booking->slot_date,'old_start_time'=>$booking->start_time,'old_end_time'=>$booking->end_time,'old_time_slot_id'=>$booking->time_slot_id,'new_slot_date'=>$data['new_slot_date'],'new_time_slot_id'=>$slots->first()->id,'reason'=>$data['reason']]); foreach($sourceBookings->sortBy('start_time')->values() as $i=>$old) BookingRescheduleRequestSlot::create(['booking_reschedule_request_id'=>$item->id,'booking_id'=>$old->id,'old_slot_date'=>$old->slot_date,'old_start_time'=>$old->start_time,'old_end_time'=>$old->end_time,'new_slot_date'=>$data['new_slot_date'],'new_time_slot_id'=>$slots[$i]->id]); return $item; });
        // Notify owner about reschedule request (best-effort)
        try {
            $booking->load('court.venue.owner');
            $ownerId = $booking->court->venue->owner?->id ?? null;
            if ($ownerId) {
                app(\App\Services\NotificationService::class)->notifyOwnerRescheduleRequest($ownerId, $reschedule);
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return redirect()->route('account.bookings.index')->with('success', 'Yêu cầu đổi lịch đã được gửi tới chủ sân.');
    }

    private function eligibilityError(Booking $booking): ?string
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'confirmed' || ! Carbon::parse($booking->slot_date->toDateString().' '.$booking->start_time, 'Asia/Ho_Chi_Minh')->isFuture()) {
            return 'Chỉ booking đã xác nhận và chưa tới giờ mới có thể yêu cầu đổi lịch.';
        }

        if ($booking->rescheduleRequests()->where('status', 'pending')->exists()) {
            return 'Booking này đã có yêu cầu đổi lịch đang chờ chủ sân xử lý.';
        }

        return null;
    }
    private function slotTaken(Booking $booking, string $date, TimeSlot $slot): bool
    {
        return Booking::where('court_id',$booking->court_id)->where('slot_date',$date)->whereIn('status',['pending','confirmed','completed'])->whereKeyNot($booking->id)->where('start_time','<',$slot->end_time)->where('end_time','>',$slot->start_time)->exists();
    }
    private function sourceBookings(Booking $booking)
    {
        return Booking::where('user_id',$booking->user_id)->where('court_id',$booking->court_id)->where('slot_date',$booking->slot_date)->where('created_at',$booking->created_at)->where('status','confirmed')->orderBy('start_time')->get();
    }
}
