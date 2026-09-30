@extends('layouts.app')

@section('title', 'Chi tiết đặt lịch #' . $booking->id . ' | SportHub')

@section('content')
@php
    $slotDate = $booking->slot_date?->format('d/m/Y') ?? '';
    
    $finalPrice = $booking->items->isNotEmpty()
        ? $booking->total_price
        : collect($bookingGroup)->sum('total_price');

    $totalPriceStr = number_format((float) $finalPrice, 0, ',', '.') . ' ₫';
    $isPaid = ($booking->payment_status ?? 'unpaid') === 'paid';
    $isCancelled = $booking->status === 'cancelled';
    $isPending = $booking->status === 'pending';
    
    $statusLabel = $isPending ? 'Chờ chủ sân xác nhận' : ($statusMeta['label'] ?? 'Đã xác nhận');
    
    // Lấy thông tin môn thể thao và SĐT
    $sportName = $booking->court?->venue?->sport?->name ?? 'Thể thao';
    $venuePhone = $booking->court?->venue?->ownerRegistration?->phone ?? $booking->court?->venue?->phone ?? 'Chưa cập nhật';
    $userPhone = Auth::user()->phone ?? 'Chưa cập nhật';
@endphp

<div class="min-h-screen bg-slate-50/70 py-8 pb-24">
    <div class="mx-auto max-w-md px-4 sm:px-0">
        

        <!-- Header Nav Bar -->
        <div class="mb-4 flex items-center justify-between text-xs">
            <a href="{{ route('account.bookings.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 font-bold text-slate-700 shadow-2xs border border-slate-200 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Lịch sử đặt sân
            </a>
            <span class="font-bold text-slate-400">Mã đơn: <strong class="text-slate-800">#{{ $booking->id }}</strong></span>
        </div>

        <!-- Compact Ticket Card -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            
            <!-- Card Top Banner -->
            <div class="p-5 text-center bg-gradient-to-b from-slate-50 to-white border-b border-slate-100">
                <h1 class="text-lg font-black text-slate-900 tracking-tight">
                    {{ $isCancelled ? 'Đơn đặt sân đã hủy' : ($isPaid ? 'Đặt sân thành công!' : 'Chi tiết đơn đặt lịch') }}
                </h1>
                
                <p class="mt-0.5 text-xs text-slate-500">
                    Mã đơn <strong class="text-slate-800">#{{ $booking->id }}</strong> · {{ $booking->created_at?->format('H:i d/m/Y') }}
                </p>

                <div class="mt-3 inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-xs font-bold {{ $isCancelled ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($isPaid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isCancelled ? 'bg-rose-500' : ($isPaid ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse') }}"></span>
                    {{ $isCancelled ? 'Đã hủy đơn' : ($isPaid ? 'Đã thanh toán VNPay' : $statusLabel) }}
                </div>
            </div>

            <!-- Details Section -->
            <div class="p-4 sm:p-5 space-y-4 text-xs">
                
                <!-- Customer Info -->
                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 border border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-slate-800 font-bold text-white text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="font-bold text-slate-900 block text-xs">{{ Auth::user()->name }}</span>
                            <span class="text-slate-500 text-[11px] block">{{ $userPhone }}</span>
                        </div>
                    </div>
                    <span class="rounded-md bg-slate-200/70 px-2 py-0.5 text-[11px] font-bold text-slate-700">
                        Sân {{ $sportName }}
                    </span>
                </div>

                <!-- Venue Info -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <span><i class="fa-solid fa-location-dot text-emerald-600 mr-1"></i> Cơ sở thi đấu</span>
                        @if($venuePhone !== 'Chưa cập nhật')
                        <a href="tel:{{ $venuePhone }}" class="text-slate-600 hover:text-emerald-600 transition">
                            <i class="fa-solid fa-phone text-[10px]"></i> {{ $venuePhone }}
                        </a>
                        @endif
                    </div>
                    <h2 class="text-sm font-black text-slate-900 leading-snug">
                        {{ $booking->court?->venue?->name ?? 'Chưa cập nhật' }}
                    </h2>
                    <p class="text-slate-500 leading-relaxed text-[11px]">
                        {{ $booking->court?->venue?->address ?? 'Chưa cập nhật' }}
                    </p>
                </div>

                <div class="border-t border-dashed border-slate-200"></div>

                <!-- Schedule Timeslots -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <span><i class="fa-regular fa-calendar-check text-emerald-600 mr-1"></i> Lịch chơi & Ca đá</span>
                        <span class="text-slate-600 font-bold">Tổng: {{ $totalDurationStr }}</span>
                    </div>

                    @php
                        $scheduleGroups = collect($bookingGroup)
                            ->sortBy(fn ($slot) => ($slot->slot_date?->format('Y-m-d') ?? '') . ' ' . $slot->start_time)
                            ->groupBy(fn ($slot) => $slot->slot_date?->format('Y-m-d') ?? $booking->slot_date?->format('Y-m-d'));
                    @endphp

                    @foreach($scheduleGroups as $dateValue => $slots)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-black text-slate-900 text-xs">
                                    {{ \Carbon\Carbon::parse($dateValue)->format('d/m/Y') }}
                                </span>
                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                    {{ $booking->court?->name ?? 'Sân thi đấu' }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($slots->sortBy('start_time') as $slot)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-800">
                                        <i class="fa-regular fa-clock text-slate-400 text-[10px]"></i>
                                        {{ substr((string) $slot->start_time, 0, 5) }} – {{ substr((string) $slot->end_time, 0, 5) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-dashed border-slate-200"></div>

                <!-- Invoice Breakdown -->
                @php
                    $groupBookings = collect($bookingGroup ?? [$booking]);
                    $totalFinal = $finalPrice ?? $booking->total_price ?? 0;
                    $purchasedServices = $booking->services ?? collect();

                    $totalBookingMinutes = 0;
                    foreach ($groupBookings as $bookingItem) {
                        if (!empty($bookingItem->start_time) && !empty($bookingItem->end_time)) {
                            $totalBookingMinutes += \Carbon\Carbon::parse($bookingItem->start_time)->diffInMinutes(\Carbon\Carbon::parse($bookingItem->end_time));
                        }
                    }

                    $serviceDurationHours = $totalBookingMinutes > 0 ? ($totalBookingMinutes / 60) : 1;
                    $servicesTotal = $purchasedServices->sum(function ($service) use ($serviceDurationHours) {
                        $unitPrice = (float) ($service->pivot->price ?? 0);
                        $quantity = (int) ($service->pivot->quantity ?? 1);
                        $pricingType = $service->pricing_type ?? 'retail';
                        $lineTotal = $unitPrice * $quantity;

                        if ($pricingType === 'rental' && $serviceDurationHours > 0) {
                            $lineTotal *= $serviceDurationHours;
                        }

                        return $lineTotal > 0 ? $lineTotal : $unitPrice;
                    });

                    $appliedVoucher = null;
                    $discountAmount = 0;
                    if (method_exists($booking, 'vouchers') && $booking->vouchers->isNotEmpty()) {
                        $appliedVoucher = $booking->vouchers->first();
                        $discountAmount = (float) ($appliedVoucher->pivot->discount_amount ?? 0);
                    }

                    $courtPriceOriginal = max(0, (float) ($totalGroupPrice ?? $booking->items->sum('price') ?? 0));
                @endphp

                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Tiền thuê sân:</span>
                        <strong class="text-slate-900 font-bold">{{ number_format($courtPriceOriginal, 0, ',', '.') }} ₫</strong>
                    </div>
                    
                    @if($discountAmount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Giảm giá ({{ $appliedVoucher->code }}):</span>
                        <strong class="font-bold">-{{ number_format($discountAmount, 0, ',', '.') }} ₫</strong>
                    </div>
                    @endif
                    
                    @if($servicesTotal > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Dịch vụ đi kèm:</span>
                        <strong class="text-slate-900 font-bold">+{{ number_format($servicesTotal, 0, ',', '.') }} ₫</strong>
                    </div>
                    @endif


                    <div class="border-t border-slate-200 pt-2 flex justify-between items-center text-sm">
                        <span class="font-black text-slate-900">Tổng thanh toán:</span>
                        <span class="font-black text-emerald-600 text-lg">{{ number_format($totalFinal, 0, ',', '.') }} ₫</span>
                    </div>
                </div>

                <!-- Purchased Services -->
                @if($purchasedServices->count() > 0)
                <div class="space-y-1.5">
                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[11px] block">Dịch vụ đã chọn</span>
                    @foreach($purchasedServices as $service)
                        @php
                            $qty = (int) ($service->pivot->quantity ?? 1);
                            $unitPrice = (float) ($service->pivot->price ?? 0);
                            $lineTotal = $unitPrice * $qty;
                            if (($service->pricing_type ?? 'retail') === 'rental' && $serviceDurationHours > 0) {
                                $lineTotal *= $serviceDurationHours;
                            }
                            if ($lineTotal <= 0 && $unitPrice > 0) {
                                $lineTotal = $unitPrice;
                            }
                            $unitStr = trim((string) ($service->unit ?? ''));
                            $displayUnit = ($unitStr === '1' || empty($unitStr)) ? '' : $unitStr;
                        @endphp
                        <div class="flex justify-between items-center rounded-lg border border-slate-100 bg-white p-2 text-xs">
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $service->name }}</span>
                                <span class="text-slate-400 text-[11px]">SL: {{ $qty }} {{ $displayUnit }}</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ number_format($lineTotal, 0, ',', '.') }} ₫</span>
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Payment Block if unpaid -->
                @if(!$isPaid && !$isCancelled && $booking->status !== 'rejected')
                    <div id="payment-section" class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-center space-y-2.5">
                        <span class="text-xs font-bold text-amber-900 block uppercase tracking-wider">Thanh toán đơn hàng</span>
                        
                        @if($isPending)
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1 text-lg font-black text-amber-600 border border-amber-200 tabular-nums" id="payment-countdown">
                                --:--
                            </div>
                        @endif

                        <a href="{{ route('bookings.payment.vnpay_qr', $booking->id) }}" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-blue-600 px-4 py-3 text-xs font-black text-white shadow-md hover:bg-blue-700 transition">
                            <i class="fa-solid fa-qrcode"></i> Thanh toán qua VNPay
                        </a>
                    </div>
                @endif

                <!-- Cancellation Info -->
                @if($isCancelled)
                    @php
                        $totalCancelFee = $bookingGroup->sum('cancellation_fee');
                        $totalRefund = $bookingGroup->sum('refund_amount');
                    @endphp
                    <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 space-y-1.5 text-xs">
                        <span class="font-bold text-rose-800 uppercase tracking-wider block text-[11px]">Thông Tin Hủy & Hoàn Tiền</span>
                        <div class="flex justify-between"><span class="text-slate-500">Lý do:</span><strong class="text-rose-700">{{ $booking->cancel_reason ?? 'Không có' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Phí phạt:</span><strong class="text-slate-800">{{ number_format($totalCancelFee, 0, ',', '.') }} ₫</strong></div>
                        <div class="flex justify-between border-t border-rose-200 pt-1.5"><span class="font-bold text-slate-900">Hoàn ví:</span><strong class="text-emerald-600 font-bold">{{ number_format($totalRefund, 0, ',', '.') }} ₫</strong></div>
                    </div>
                @endif

            </div>

            <!-- Footer Guarantee -->
            <div class="bg-slate-50 px-4 py-2.5 border-t border-slate-100 flex justify-between text-[11px] text-slate-400">
                <span><i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i> Giữ sân 100%</span>
                <span class="font-semibold text-slate-600">SportHub</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex gap-2.5">
            <a href="{{ route('account.bookings.index') }}" class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-3 text-xs font-black text-white shadow-md hover:bg-emerald-700 transition">
                <i class="fa-solid fa-list-check"></i> Quản lý lịch đặt
            </a>
            <a href="{{ route('home') }}" class="inline-flex justify-center items-center gap-1.5 rounded-xl bg-white px-4 py-3 text-xs font-bold text-slate-700 border border-slate-200 shadow-2xs hover:bg-slate-50 transition">
                <i class="fa-solid fa-house text-slate-400"></i> Trang chủ
            </a>
        </div>

    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countdownEl = document.getElementById('payment-countdown');
        if (countdownEl) {
            const createdAtStr = "{{ $booking->created_at->toISOString() }}";
            const holdTimeMinutes = {{ $bookingHoldTime ?? 15 }};
            
            const createdAt = new Date(createdAtStr).getTime();
            const holdTimeMs = holdTimeMinutes * 60 * 1000;
            const expireAt = createdAt + holdTimeMs;

            const timer = setInterval(() => {
                const now = new Date().getTime();
                const distance = expireAt - now;

                if (distance <= 0) {
                    clearInterval(timer);
                    countdownEl.innerHTML = "Đã hết hạn giữ chỗ";
                    countdownEl.className = "inline-flex items-center justify-center rounded-xl bg-white px-3 py-1 text-xs font-black text-rose-600 border border-rose-200";
                    
                    const paymentSection = document.getElementById('payment-section');
                } else {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    countdownEl.innerHTML = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
                }
            }, 1000);
        }
    });
</script>
@endsection
