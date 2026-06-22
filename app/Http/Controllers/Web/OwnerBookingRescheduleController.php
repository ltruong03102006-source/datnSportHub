<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRescheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\BookingRescheduleNotification;

class OwnerBookingRescheduleController extends Controller
{
    public function index(Request $request)
    {
        $requests = BookingRescheduleRequest::with(['booking.court.venue','user','newTimeSlot','slots.booking','slots.newTimeSlot'])->whereHas('booking.court.venue', fn($q) => $q->where('owner_id',$request->user()->id))->latest()->paginate(15);
        return view('owner.reschedules.index', compact('requests'));
    }
    public function show(Request $request, BookingRescheduleRequest $rescheduleRequest)
    {
        $this->ensureOwner($request, $rescheduleRequest);
        return view('owner.reschedules.show', ['rescheduleRequest'=>$rescheduleRequest->load(['booking.court.venue','user','newTimeSlot','slots.booking','slots.newTimeSlot'])]);
    }
    public function approve(Request $request, BookingRescheduleRequest $rescheduleRequest): RedirectResponse
    {
        try { DB::transaction(function () use ($request,$rescheduleRequest) {
            $item = BookingRescheduleRequest::lockForUpdate()->with(['booking.court.venue','slots.booking','slots.newTimeSlot'])->findOrFail($rescheduleRequest->id); $this->ensureOwner($request,$item);
            abort_unless($item->status === 'pending' && $item->slots->isNotEmpty(),409,'Yêu cầu đã được xử lý hoặc không hợp lệ.'); $booking = Booking::lockForUpdate()->findOrFail($item->booking_id);
            foreach($item->slots as $change){$slot=$change->newTimeSlot; abort_unless($change->booking->status==='confirmed' && $slot->court_id===$booking->court_id,422,'Booking hoặc khung giờ không hợp lệ.'); abort_if(Carbon::parse($change->new_slot_date->toDateString().' '.$slot->start_time,'Asia/Ho_Chi_Minh')->lte(now('Asia/Ho_Chi_Minh')),422,'Khung giờ mới đã qua.'); $taken=Booking::where('court_id',$booking->court_id)->where('slot_date',$change->new_slot_date)->whereIn('status',['pending','confirmed','completed'])->whereNotIn('id',$item->slots->pluck('booking_id'))->where('start_time','<',$slot->end_time)->where('end_time','>',$slot->start_time)->exists(); abort_if($taken,409,'Khung giờ mới đã có người đặt.');}
            foreach($item->slots as $change){$slot=$change->newTimeSlot; Booking::whereKey($change->booking_id)->update(['slot_date'=>$change->new_slot_date,'time_slot_id'=>$slot->id,'start_time'=>$slot->start_time,'end_time'=>$slot->end_time]);}
            $item->update(['status'=>'approved','reviewed_by'=>$request->user()->id,'reviewed_at'=>now()]);
            $booking->user->notify(new BookingRescheduleNotification($item, 'Yêu cầu đổi lịch của bạn đã được duyệt.'));
        }); } catch (\Throwable $e) { return back()->with('error',$e->getMessage() ?: 'Không thể duyệt yêu cầu.'); }
        return redirect()->route('owner.web.reschedule.index')->with('success','Đã duyệt yêu cầu đổi lịch.');
    }
    public function reject(Request $request, BookingRescheduleRequest $rescheduleRequest): RedirectResponse
    {
        $data=$request->validate(['owner_note'=>['required','string','max:1000']]); $this->ensureOwner($request,$rescheduleRequest); abort_unless($rescheduleRequest->status==='pending',409);
        $rescheduleRequest->update(['status'=>'rejected','owner_note'=>$data['owner_note'],'reviewed_by'=>$request->user()->id,'reviewed_at'=>now()]); $rescheduleRequest->user->notify(new BookingRescheduleNotification($rescheduleRequest, 'Yêu cầu đổi lịch của bạn đã bị từ chối.')); return redirect()->route('owner.web.reschedule.index')->with('success','Đã từ chối yêu cầu đổi lịch.');
    }
    private function ensureOwner(Request $request, BookingRescheduleRequest $item): void { abort_unless($item->booking()->whereHas('court.venue',fn($q)=>$q->where('owner_id',$request->user()->id))->exists(),403); }
}
