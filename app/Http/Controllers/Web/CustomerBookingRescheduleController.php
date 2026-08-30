<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingRescheduleRequest;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerBookingRescheduleController extends Controller
{
    public function create(Booking $booking)
    {
        $this->ensureCustomer($booking);
        $this->ensureLegacyBookingItems($booking);

        if ($message = $this->eligibilityError($booking)) {
            return redirect()->route('account.bookings.index')->with('error', $message);
        }

        $booking->load(['court.venue', 'items.timeSlot.prices']);

        $bookingItems = $booking->items->where('status', 'booked')->sortBy('start_time')->values();

        foreach ($bookingItems as $item) {
            $dayOfWeek = $item->slot_date ? $item->slot_date->dayOfWeek : 0;
            $slotPrice = $item->timeSlot?->prices?->firstWhere('day_of_week', $dayOfWeek);
            $item->price_type = $slotPrice?->price_type ?? 'normal';
        }

        $userWallet = auth()->user()->getOrCreateWallet();

        return view('customer.bookings.reschedule', [
            'booking' => $booking,
            'bookingItems' => $bookingItems,
            'userWallet' => $userWallet,
        ]);
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureCustomer($booking);
        $this->ensureLegacyBookingItems($booking);

        if ($message = $this->eligibilityError($booking)) {
            return back()->withInput()->with('error', $message);
        }

        $data = $request->validate([
            'booking_item_ids' => ['required', 'array', 'min:1'],
            'booking_item_ids.*' => ['integer', 'exists:booking_items,id'],
            'new_slot_date' => ['required', 'date', 'after_or_equal:today'],
            'new_time_slot_ids' => ['required', 'array', 'min:1'],
            'new_time_slot_ids.*' => ['integer', 'exists:time_slots,id'],
            'payment_method' => ['nullable', 'string', 'in:wallet,vnpay'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $bookingItemIds = collect($data['booking_item_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $newTimeSlotIds = collect($data['new_time_slot_ids'])->map(fn ($id) => (int) $id)->unique()->values();

        if ($bookingItemIds->count() !== count($data['booking_item_ids']) || $newTimeSlotIds->count() !== count($data['new_time_slot_ids'])) {
            return back()->withInput()->with('error', 'Không được chọn trùng ca.');
        }

        if ($bookingItemIds->count() !== $newTimeSlotIds->count()) {
            return back()->withInput()->with('error', 'Số ca mới phải đúng bằng số ca cũ muốn đổi.');
        }

        $items = BookingItem::whereIn('id', $bookingItemIds)
            ->where('booking_id', $booking->id)
            ->where('status', 'booked')
            ->with(['timeSlot.prices'])
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        if ($items->count() !== $bookingItemIds->count()) {
            return back()->withInput()->with('error', 'Ca cũ được chọn không hợp lệ hoặc đang chờ đổi lịch.');
        }

        $now = now('Asia/Ho_Chi_Minh');
        foreach ($items as $item) {
            $oldStart = Carbon::parse($item->slot_date->toDateString().' '.$item->start_time, 'Asia/Ho_Chi_Minh');
            if ($oldStart->lte($now) || $oldStart->lt($now->copy()->addHours(2))) {
                return back()->withInput()->with('error', 'Chỉ được đổi lịch trước giờ chơi ít nhất 2 giờ.');
            }
        }

        $newSlots = TimeSlot::whereIn('id', $newTimeSlotIds)
            ->where('court_id', $booking->court_id)
            ->with(['prices'])
            ->orderBy('start_time')
            ->get();

        if ($newSlots->count() !== $newTimeSlotIds->count()) {
            return back()->withInput()->with('error', 'Khung giờ mới không thuộc sân này.');
        }

        if (! $this->slotsAreConsecutive($newSlots)) {
            return back()->withInput()->with('error', 'Các ca mới phải liền nhau, không được chọn các ca rời nhau.');
        }

        $newSlotDate = Carbon::parse($data['new_slot_date']);
        $newDayOfWeek = $newSlotDate->dayOfWeek;

        $orderedItems = $items->values();
        $orderedSlots = $newSlots->values();

        $totalOldPrice = 0.0;
        $totalNewPrice = 0.0;
        $itemPrices = [];

        foreach ($orderedItems as $index => $item) {
            $slot = $orderedSlots[$index];
            $oldPrice = (float) ($item->price ?? 0);

            $newSlotPriceObj = $slot->prices?->firstWhere('day_of_week', $newDayOfWeek);
            $newPrice = (float) ($newSlotPriceObj?->price ?? $oldPrice);

            $diff = $newPrice - $oldPrice;

            $totalOldPrice += $oldPrice;
            $totalNewPrice += $newPrice;

            $itemPrices[$index] = [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'price_difference' => $diff,
            ];
        }

        $oldServiceTotal = $this->calculateBookingServicesTotal($booking, $items->pluck('end_time')->all(), $items->pluck('start_time')->all());
        $newServiceTotal = $this->calculateBookingServicesTotal($booking, $newSlots->pluck('end_time')->all(), $newSlots->pluck('start_time')->all());
        $servicePriceDiff = $newServiceTotal - $oldServiceTotal;
        $totalPriceDiff = ($totalNewPrice - $totalOldPrice) + $servicePriceDiff;
        $paymentMethod = $data['payment_method'] ?? 'wallet';

        // Nếu tổng tiền ca mới cao hơn ca cũ và chọn thanh toán qua Ví, kiểm tra số dư ví
        if ($totalPriceDiff > 0 && $paymentMethod === 'wallet') {
            $userWallet = $request->user()->getOrCreateWallet();
            if ($userWallet->balance < $totalPriceDiff) {
                return back()->withInput()->with(
                    'error',
                    'Số dư ví của bạn (' . number_format($userWallet->balance, 0, ',', '.') . ' VNĐ) không đủ để thanh toán số tiền chênh lệch ' . number_format($totalPriceDiff, 0, ',', '.') . ' VNĐ. Vui lòng chọn thanh toán qua VNPay hoặc nạp thêm tiền vào ví.'
                );
            }
        }

        foreach ($newSlots as $slot) {
            $newStart = Carbon::parse($data['new_slot_date'].' '.$slot->start_time, 'Asia/Ho_Chi_Minh');
            if ($newStart->lte($now)) {
                return back()->withInput()->with('error', 'Có khung giờ mới đã qua giờ.');
            }

            if ($this->slotTaken($booking, $data['new_slot_date'], $slot, $bookingItemIds->all())) {
                return back()->withInput()->with('error', 'Có khung giờ mới không còn trống.');
            }
        }

        $firstRequest = DB::transaction(function () use ($booking, $items, $newSlots, $data, $request, $itemPrices, $totalPriceDiff, $paymentMethod) {
            $requestCode = 'RS-'.now('Asia/Ho_Chi_Minh')->format('YmdHis').'-'.$request->user()->id;

            // Nếu thanh toán qua Ví và có tiền chênh lệch
            if ($totalPriceDiff > 0 && $paymentMethod === 'wallet') {
                $userWallet = $request->user()->getOrCreateWallet();
                app(\App\Services\WalletService::class)->processTransaction(
                    wallet: $userWallet,
                    type: \App\Enums\TransactionType::PAYMENT,
                    amount: $totalPriceDiff,
                    description: 'Thanh toán tiền chênh lệch đổi ca đặt sân (Mã: ' . $requestCode . ')',
                    bookingId: $booking->id,
                    metadata: ['request_code' => $requestCode, 'total_price_diff' => $totalPriceDiff]
                );
            }

            $created = null;
            $orderedItems = $items->values();
            $orderedSlots = $newSlots->values();

            foreach ($orderedItems as $index => $item) {
                $slot = $orderedSlots[$index];
                $priceInfo = $itemPrices[$index];

                $paymentStatus = 'none';
                if ($totalPriceDiff > 0) {
                    $paymentStatus = ($paymentMethod === 'vnpay') ? 'pending_vnpay' : 'paid';
                } elseif ($totalPriceDiff < 0) {
                    $paymentStatus = 'pending_refund';
                }

                $rescheduleRequest = BookingRescheduleRequest::create([
                    'request_code' => $requestCode,
                    'booking_id' => $booking->id,
                    'user_id' => $request->user()->id,
                    'booking_item_id' => $item->id,
                    'old_slot_date' => $item->slot_date,
                    'old_time_slot_id' => $item->time_slot_id,
                    'old_start_time' => $item->start_time,
                    'old_end_time' => $item->end_time,
                    'new_slot_date' => $data['new_slot_date'],
                    'new_time_slot_id' => $slot->id,
                    'new_start_time' => $slot->start_time,
                    'new_end_time' => $slot->end_time,
                    'old_price' => $priceInfo['old_price'],
                    'new_price' => $priceInfo['new_price'],
                    'price_difference' => $priceInfo['price_difference'],
                    'payment_status' => $paymentStatus,
                    'reason' => $data['reason'] ?? null,
                    'status' => 'pending',
                ]);

                $item->update(['status' => 'reschedule_pending']);
                $created ??= $rescheduleRequest;
            }

            return $created;
        });

        if ($totalPriceDiff > 0 && $paymentMethod === 'vnpay' && $firstRequest) {
            $vnpayUrl = $this->buildRescheduleVnpayUrl($request, $firstRequest->request_code, $totalPriceDiff);
            return redirect()->away($vnpayUrl);
        }

        try {
            $ownerId = $booking->load('court.venue.owner')->court->venue->owner?->id ?? null;
            if ($ownerId && $firstRequest) {
                app(\App\Services\NotificationService::class)->notifyOwnerRescheduleRequest($ownerId, $firstRequest);
            }
        } catch (\Throwable) {
            // ignore notification errors
        }

        return redirect()->route('account.bookings.index')
            ->with('success', 'Đã gửi yêu cầu đổi lịch. Vui lòng chờ chủ sân duyệt.');
    }

    private function ensureCustomer(Booking $booking): void
    {
        abort_unless((int) $booking->user_id === (int) auth()->id(), 403);
    }

    private function ensureLegacyBookingItems(Booking $booking): void
    {
        if (! Schema::hasTable('booking_items') || $booking->items()->exists()) {
            return;
        }

        $timeSlotId = $booking->time_slot_id;

        if (! $timeSlotId) {
            $timeSlotId = TimeSlot::where('court_id', $booking->court_id)
                ->where('start_time', $this->normalizeTime($booking->start_time))
                ->where('end_time', $this->normalizeTime($booking->end_time))
                ->value('id');
        }

        $booking->items()->create([
            'time_slot_id' => $timeSlotId,
            'slot_date' => $booking->slot_date,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'price' => $booking->total_price ?? 0,
            'status' => in_array($booking->status, ['cancelled', 'rejected'], true) ? 'cancelled' : 'booked',
        ]);

        $booking->unsetRelation('items');
        $booking->load('items.timeSlot');
    }

    private function eligibilityError(Booking $booking): ?string
    {
        if ($booking->status !== 'confirmed') {
            return 'Chỉ booking đã xác nhận mới có thể yêu cầu đổi lịch.';
        }

        if ($booking->rescheduleRequests()->where('status', 'pending')->exists()) {
            return 'Booking này đã có yêu cầu đổi lịch đang chờ chủ sân xử lý.';
        }

        if (! $booking->items()->where('status', 'booked')->exists()) {
            return 'Booking này chưa có ca có thể đổi lịch.';
        }

        return null;
    }

    private function slotTaken(Booking $booking, string $date, TimeSlot $slot, array $exceptItemIds = []): bool
    {
        $itemConflict = BookingItem::whereDate('slot_date', $date)
            ->whereIn('status', ['booked', 'reschedule_pending'])
            ->whereNotIn('id', $exceptItemIds)
            ->where(function ($query) use ($slot) {
                $query->where('time_slot_id', $slot->id)
                    ->orWhere(function ($overlap) use ($slot) {
                        $overlap->where('start_time', '<', $slot->end_time)
                            ->where('end_time', '>', $slot->start_time);
                    });
            })
            ->whereHas('booking', function ($query) use ($booking) {
                $query->where('court_id', $booking->court_id)
                    ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->exists();

        if ($itemConflict) {
            return true;
        }

        return Booking::where('court_id', $booking->court_id)
            ->whereDate('slot_date', $date)
            ->whereDoesntHave('items')
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->whereKeyNot($booking->id)
            ->where('start_time', '<', $slot->end_time)
            ->where('end_time', '>', $slot->start_time)
            ->exists();
    }

    private function slotsAreConsecutive($slots): bool
    {
        $orderedSlots = $slots->sortBy('start_time')->values();

        if ($orderedSlots->count() <= 1) {
            return true;
        }

        for ($index = 1; $index < $orderedSlots->count(); $index++) {
            if ($this->normalizeTime($orderedSlots[$index - 1]->end_time) !== $this->normalizeTime($orderedSlots[$index]->start_time)) {
                return false;
            }
        }

        return true;
    }

    private function normalizeTime(?string $time): ?string
    {
        return $time ? date('H:i:s', strtotime($time)) : null;
    }

    private function calculateBookingServicesTotal(Booking $booking, array $endTimes, array $startTimes): float
    {
        $booking->loadMissing('services');
        if ($booking->services->isEmpty()) {
            return 0.0;
        }

        $totalMinutes = 0.0;
        foreach ($startTimes as $index => $startTime) {
            if (!isset($endTimes[$index]) || !$startTime || !$endTimes[$index]) {
                continue;
            }

            $totalMinutes += Carbon::parse($startTime, 'Asia/Ho_Chi_Minh')
                ->diffInMinutes(Carbon::parse($endTimes[$index], 'Asia/Ho_Chi_Minh'));
        }

        $hours = $totalMinutes > 0 ? ($totalMinutes / 60) : 1;

        return $booking->services->sum(function ($service) use ($hours) {
            $unitPrice = (float) ($service->pivot->price ?? 0);
            $quantity = (int) ($service->pivot->quantity ?? 1);
            $lineTotal = $unitPrice * $quantity;

            if (($service->pricing_type ?? 'retail') === 'rental' && $hours > 0) {
                $lineTotal *= $hours;
            }

            return $lineTotal > 0 ? $lineTotal : $unitPrice;
        });
    }

    private function buildRescheduleVnpayUrl(Request $request, string $requestCode, float $amount): string
    {
        $vnp_TmnCode = config('vnpay.vnp_TmnCode');
        $vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $vnp_Url = config('vnpay.vnp_Url');
        $vnp_Returnurl = route('vnpay.callback');

        $vnp_TxnRef = $requestCode . '_' . time();
        $vnp_OrderInfo = 'Thanh toan chenh lech doi lich ' . $requestCode;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = (int) round($amount * 100);
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }
}
