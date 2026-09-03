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

<div class="min-h-screen bg-stone-50 py-10">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header Nav Bar -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('account.bookings.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 shadow-sm border border-stone-200 hover:bg-stone-100 hover:text-emerald-700 transition-all">
                <i class="fa-solid fa-arrow-left text-xs"></i> Lịch sử đặt sân
            </a>
            <span class="text-sm font-semibold text-zinc-500 bg-white px-3 py-1.5 rounded-lg border border-stone-200 shadow-sm">
                Mã đơn: <strong class="text-emerald-700">#{{ $booking->id }}</strong>
            </span>
        </div>

        <!-- Main Ticket Card -->
        <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-200/50">
            
            <!-- Card Top Banner -->
            <div class="p-6 sm:p-8 text-center bg-gradient-to-b from-emerald-50/50 to-white border-b border-stone-100 relative">
                <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 tracking-tight">
                    {{ $isCancelled ? 'Đơn đặt sân đã hủy' : ($isPaid ? 'Đặt sân thành công!' : 'Chi tiết đơn đặt lịch') }}
                </h1>
                
                <p class="mt-2 text-sm text-zinc-500 font-medium">
                    Tạo lúc: {{ $booking->created_at?->format('H:i d/m/Y') }}
                </p>

                <div class="mt-4 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold shadow-sm 
                    {{ $isCancelled ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($isPaid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                    <span class="relative flex h-2.5 w-2.5">
                        @if(!$isCancelled && !$isPaid)
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        @endif
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $isCancelled ? 'bg-rose-500' : ($isPaid ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
                    </span>
                    {{ $isCancelled ? 'Đã hủy đơn' : ($isPaid ? 'Đã thanh toán VNPay' : $statusLabel) }}
                </div>
            </div>

            <!-- Details Section -->
            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Customer Info -->
                <div class="flex items-center justify-between rounded-2xl bg-stone-50 p-4 border border-stone-200">
                    <div class="flex items-center gap-4">
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-zinc-900 font-bold text-white text-lg shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="font-bold text-zinc-900 block text-base">{{ Auth::user()->name }}</span>
                            <span class="text-zinc-500 text-sm block mt-0.5"><i class="fa-solid fa-phone text-xs mr-1"></i> {{ $userPhone }}</span>
                        </div>
                    </div>
                    <span class="rounded-lg bg-emerald-100/80 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200 shadow-sm">
                        Sân {{ $sportName }}
                    </span>
                </div>

                <!-- Venue Info -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-zinc-400 font-bold uppercase tracking-wider text-xs">
                        <span><i class="fa-solid fa-location-dot text-emerald-600 mr-1.5"></i> Cơ sở thi đấu</span>
                        @if($venuePhone !== 'Chưa cập nhật')
                        <a href="tel:{{ $venuePhone }}" class="text-emerald-600 hover:text-emerald-700 hover:underline transition">
                            <i class="fa-solid fa-phone text-xs"></i> {{ $venuePhone }}
                        </a>
                        @endif
                    </div>
                    <h2 class="text-lg font-black text-zinc-900 leading-snug">
                        {{ $booking->court?->venue?->name ?? 'Chưa cập nhật' }}
                    </h2>
                    <p class="text-zinc-500 leading-relaxed text-sm">
                        {{ $booking->court?->venue?->address ?? 'Chưa cập nhật' }}
                    </p>
                </div>

                <div class="border-t-2 border-dashed border-stone-200"></div>

                <!-- Schedule Timeslots -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-zinc-400 font-bold uppercase tracking-wider text-xs mb-1">
                        <span><i class="fa-regular fa-calendar-check text-emerald-600 mr-1.5"></i> Lịch chơi & Ca đá</span>
                        <span class="text-zinc-600 font-bold bg-stone-100 px-2 py-1 rounded-md">Tổng: {{ $totalDurationStr }}</span>
                    </div>

                    @php
                        $scheduleGroups = collect($bookingGroup)
                            ->sortBy(fn ($slot) => ($slot->slot_date?->format('Y-m-d') ?? '') . ' ' . $slot->start_time)
                            ->groupBy(fn ($slot) => $slot->slot_date?->format('Y-m-d') ?? $booking->slot_date?->format('Y-m-d'));
                    @endphp

                    @foreach($scheduleGroups as $dateValue => $slots)
                        <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm hover:border-emerald-200 transition-colors">
                            <div class="flex items-center justify-between mb-3 border-b border-stone-100 pb-3">
                                <span class="font-black text-zinc-900 text-sm">
                                    <i class="fa-regular fa-calendar text-emerald-600 mr-1.5"></i> 
                                    {{ \Carbon\Carbon::parse($dateValue)->format('d/m/Y') }}
                                </span>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">
                                    {{ $booking->court?->name ?? 'Sân thi đấu' }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($slots->sortBy('start_time') as $slot)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-stone-50 px-3 py-1.5 text-sm font-bold text-zinc-800">
                                        <i class="fa-regular fa-clock text-emerald-500 text-xs"></i>
                                        {{ substr((string) $slot->start_time, 0, 5) }} – {{ substr((string) $slot->end_time, 0, 5) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t-2 border-dashed border-stone-200"></div>

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

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-5 space-y-3">
                    <div class="flex justify-between text-zinc-600 text-sm font-medium">
                        <span>Tiền thuê sân:</span>
                        <strong class="text-zinc-900 font-bold">{{ number_format($courtPriceOriginal, 0, ',', '.') }} ₫</strong>
                    </div>

                    @if($servicesTotal > 0)
                    <div class="flex justify-between text-zinc-600 text-sm font-medium">
                        <span>Dịch vụ đi kèm:</span>
                        <strong class="text-zinc-900 font-bold">+{{ number_format($servicesTotal, 0, ',', '.') }} ₫</strong>
                    </div>
                    @endif

                    @if($discountAmount > 0)
                    <div class="flex justify-between text-emerald-600 text-sm font-medium">
                        <span>Giảm giá ({{ $appliedVoucher->code }}):</span>
                        <strong class="font-bold">-{{ number_format($discountAmount, 0, ',', '.') }} ₫</strong>
                    </div>
                    @endif

                    <div class="border-t border-emerald-200/60 pt-3 mt-2 flex justify-between items-center">
                        <span class="font-black text-zinc-900 text-base uppercase">Tổng thanh toán:</span>
                        <span class="font-black text-emerald-600 text-2xl">{{ number_format($totalFinal, 0, ',', '.') }} ₫</span>
                    </div>
                </div>

                <!-- Purchased Services -->
                @if($purchasedServices->count() > 0)
                <div class="space-y-2">
                    <span class="text-zinc-400 font-bold uppercase tracking-wider text-xs block mb-2">Dịch vụ đã chọn</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                            <div class="flex justify-between items-center rounded-xl border border-stone-200 bg-white p-3 text-sm shadow-sm hover:shadow-md transition-shadow">
                                <div>
                                    <span class="font-bold text-zinc-800 block">{{ $service->name }}</span>
                                    <span class="text-zinc-500 text-xs font-medium">Số lượng: {{ $qty }} {{ $displayUnit }}</span>
                                </div>
                                <span class="font-black text-emerald-600">{{ number_format($lineTotal, 0, ',', '.') }} ₫</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Payment Block if unpaid -->
                @if(!$isPaid && !$isCancelled && $booking->status !== 'rejected')
                    <div id="payment-section" class="rounded-2xl border-2 border-amber-200 bg-amber-50 p-6 text-center space-y-4 shadow-sm">
                        <div class="space-y-1">
                            <span class="text-sm font-black text-amber-900 block uppercase tracking-widest">Thanh toán đơn hàng</span>
                            <p class="text-amber-700 text-xs font-medium">Vui lòng hoàn tất thanh toán để giữ sân</p>
                        </div>
                        
                        @if($isPending)
                            <div class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-2xl font-black text-amber-600 border-2 border-amber-200 tabular-nums shadow-inner" id="payment-countdown">
                                <i class="fa-regular fa-clock"></i> --:--
                            </div>
                        @endif

                        <a href="{{ route('bookings.payment.vnpay_qr', $booking->id) }}" class="flex items-center justify-center gap-2 w-full rounded-xl bg-[#005BAA] hover:bg-[#004a8c] px-4 py-3.5 text-sm font-black text-white shadow-lg shadow-blue-900/20 transition-all active:scale-95">
                            Thanh toán qua VNPay <i class="fa-solid fa-chevron-right text-xs ml-1"></i>
                        </a>
                    </div>
                @endif

                <!-- Cancellation Info -->
                @if($isCancelled)
                    @php
                        $totalCancelFee = $bookingGroup->sum('cancellation_fee');
                        $totalRefund = $bookingGroup->sum('refund_amount');
                    @endphp
                    <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 p-5 space-y-3 text-sm">
                        <span class="font-black text-rose-800 uppercase tracking-widest block text-xs mb-2">Thông Tin Hủy & Hoàn Tiền</span>
                        <div class="flex justify-between items-center"><span class="text-zinc-600 font-medium">Lý do hủy:</span><strong class="text-rose-700 bg-white px-2 py-1 rounded border border-rose-100">{{ $booking->cancel_reason ?? 'Không có' }}</strong></div>
                        <div class="flex justify-between items-center"><span class="text-zinc-600 font-medium">Phí phạt:</span><strong class="text-zinc-800">{{ number_format($totalCancelFee, 0, ',', '.') }} ₫</strong></div>
                        <div class="flex justify-between items-center border-t border-rose-200/60 pt-3 mt-1">
                            <span class="font-black text-zinc-900">Tiền hoàn về ví:</span>
                            <strong class="text-emerald-600 font-black text-lg">{{ number_format($totalRefund, 0, ',', '.') }} ₫</strong>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Footer Guarantee -->
            <div class="bg-stone-50 px-6 py-4 border-t border-stone-200 flex justify-between items-center text-xs text-zinc-500">
                <span class="font-medium"><i class="fa-solid fa-shield-check text-emerald-600 mr-1.5 text-sm"></i> Đảm bảo giữ sân 100%</span>
                <span class="font-black tracking-wider text-zinc-400">SportHub</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('account.bookings.index') }}" class="flex-1 inline-flex justify-center items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3.5 text-sm font-black text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 hover:shadow-emerald-700/30 transition-all active:scale-95">
                <i class="fa-solid fa-list-check"></i> Quản lý lịch đặt
            </a>
            <a href="{{ route('home') }}" class="inline-flex justify-center items-center gap-2 rounded-2xl bg-white px-8 py-3.5 text-sm font-bold text-zinc-700 border-2 border-stone-200 shadow-sm hover:bg-stone-50 hover:border-stone-300 transition-all active:scale-95">
                <i class="fa-solid fa-house text-zinc-400"></i> Trang chủ
            </a>
        </div>

    </div>
</div>
@endsection <!-- ĐÂY CHÍNH LÀ THẺ ĐÓNG BỊ THIẾU -->

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
                    countdownEl.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Đã hết hạn giữ chỗ';
                    countdownEl.className = "inline-flex items-center justify-center gap-2 w-full rounded-xl bg-rose-50 px-4 py-3 text-sm font-black text-rose-600 border border-rose-200 mt-2";
                    
                    const paymentSection = document.getElementById('payment-section');
                    // Bạn có thể xử lý ẩn/hiện nút VNPay ở đây nếu muốn
                } else {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    countdownEl.innerHTML = '<i class="fa-regular fa-clock"></i> ' + minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
                }
            }, 1000);
        }
    });
</script>
@endsection