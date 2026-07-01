@extends('layouts.app')

@section('title', 'Lịch sử đặt sân | SportHub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Tài khoản</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">Lịch sử đặt sân</h1>
            <p class="mt-2 text-sm text-zinc-500">Theo dõi toàn bộ lịch đặt, trạng thái xác nhận và thao tác hủy khi còn đủ điều kiện.</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 transition hover:bg-stone-50">
            Đặt sân mới
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if(($bookingPackages ?? collect())->isNotEmpty())
        <div class="mb-8">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Đặt sân theo gói</p>
                    <h2 class="mt-1 text-xl font-extrabold text-zinc-900">Gói tuần / gói tháng</h2>
                </div>
            </div>

            <div class="hidden overflow-hidden rounded-lg border border-emerald-100 bg-white shadow-sm md:block">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-emerald-50/70">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-emerald-800">Mã gói</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-emerald-800">Gói</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-emerald-800">Thời gian</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-emerald-800">Số ca</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-emerald-800">Thanh toán</th>
                            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-emerald-800">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @foreach($bookingPackages as $bookingPackage)
                            @php
                                $transaction = $bookingPackage->transactions->first();
                                $isPaidPackage = $transaction?->payment_status === 'success';
                                $packageTypeLabel = $bookingPackage->package?->type === 'month' ? 'Gói tháng' : 'Gói tuần';
                                $statusLabel = $isPaidPackage ? 'Đã xác nhận' : 'Chưa thanh toán';
                                $statusClass = $isPaidPackage
                                    ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/20'
                                    : 'bg-amber-100 text-amber-700 ring-amber-600/20';
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-zinc-900">#PKG{{ $bookingPackage->id }}</td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-zinc-900">{{ $bookingPackage->package?->name ?? 'Gói đặt sân' }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $packageTypeLabel }} · {{ $bookingPackage->venue?->name ?? 'Chưa cập nhật cơ sở' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-zinc-700">
                                    {{ $bookingPackage->start_date?->format('d/m/Y') }} - {{ $bookingPackage->end_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-zinc-800">{{ $bookingPackage->bookings->count() }} ca</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    <p class="mt-1 text-xs font-bold text-emerald-700">{{ number_format((float) $bookingPackage->total_price, 0, ',', '.') }}đ</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('package-bookings.show', $bookingPackage) }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-4 md:hidden">
                @foreach($bookingPackages as $bookingPackage)
                    @php
                        $transaction = $bookingPackage->transactions->first();
                        $isPaidPackage = $transaction?->payment_status === 'success';
                        $packageTypeLabel = $bookingPackage->package?->type === 'month' ? 'Gói tháng' : 'Gói tuần';
                    @endphp
                    <div class="rounded-lg border border-emerald-100 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">#PKG{{ $bookingPackage->id }} · {{ $packageTypeLabel }}</p>
                                <h2 class="mt-1 text-base font-extrabold text-zinc-900">{{ $bookingPackage->package?->name ?? 'Gói đặt sân' }}</h2>
                                <p class="mt-1 text-sm text-zinc-500">{{ $bookingPackage->venue?->name ?? 'Chưa cập nhật cơ sở' }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $isPaidPackage ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $isPaidPackage ? 'Đã xác nhận' : 'Chưa thanh toán' }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-stone-100 pt-3 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Thời gian</p>
                                <p class="mt-1 font-bold text-zinc-800">{{ $bookingPackage->start_date?->format('d/m/Y') }} - {{ $bookingPackage->end_date?->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Tổng tiền</p>
                                <p class="mt-1 font-black text-emerald-700">{{ number_format((float) $bookingPackage->total_price, 0, ',', '.') }}đ</p>
                            </div>
                        </div>
                        <a href="{{ route('package-bookings.show', $bookingPackage) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-3 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700">
                            Xem chi tiết {{ $bookingPackage->bookings->count() }} ca
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($bookings->isEmpty() && ($bookingPackages ?? collect())->isEmpty())
        <div class="rounded-lg border border-stone-200 bg-white px-6 py-16 text-center shadow-sm">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-stone-100 text-stone-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                </svg>
            </div>
            <h2 class="text-lg font-extrabold text-zinc-900">Bạn chưa có lịch đặt nào</h2>
            <p class="mt-2 text-sm text-zinc-500">Khi đặt sân thành công, các đơn sẽ xuất hiện tại đây.</p>
        </div>
    @else
        @if($bookings->isNotEmpty())
        <div class="hidden overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm md:block">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Mã đơn</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Sân</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Thời gian</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Tổng tiền</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-stone-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @foreach($bookings as $booking)
                        @php
                            $statusMeta = $statusMap[$booking->status] ?? [
                                'label' => ucfirst($booking->status),
                                'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
                            ];
                            $slotDate = $booking->slot_date_label;
                            $mergedTimeStrings = $booking->merged_time_strings ?? [];
                            $ownerPhone = $booking->owner_phone;
                            $isEligibleStatus = (bool) $booking->is_eligible_status;
                            $isPastStartTime = (bool) $booking->is_past_start_time;
                        @endphp
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-zinc-900">#{{ $booking->id }}</td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-bold text-zinc-900">{{ $booking->court?->name ?? 'Chưa cập nhật' }}</p>
                                <div class="mt-1 text-xs text-zinc-500">
                                    {{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}
                                    @if($ownerPhone)
                                        <div class="mt-1">
                                            <a href="tel:{{ $ownerPhone }}" class="inline-flex items-center gap-1 font-bold text-emerald-600 transition hover:text-emerald-800">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.48-4.18-7.076-7.076l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                                {{ $ownerPhone }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-zinc-700">
                                <p class="font-bold text-zinc-900 mb-1">{{ $slotDate }}</p>
                                <div class="text-zinc-600 font-normal space-y-1">
                                    @foreach($mergedTimeStrings as $timeStr)
                                        <p>{{ $timeStr }}</p>
                                    @endforeach
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                                @if($booking->status === 'cancelled' && $booking->cancel_reason)
                                    <p class="mt-1.5 max-w-[200px] text-xs text-red-600"><span class="font-semibold">Lý do hủy:</span> {{ $booking->cancel_reason }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
    <div class="flex items-center justify-end gap-2">
        <a href="{{ route('web.bookings.success', $booking->id) }}" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-stone-50">Chi tiết</a>
        @if($booking->status === 'confirmed' && !$isPastStartTime)
            <a href="{{ route('customer.booking.reschedule.create', $booking->id) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">Đổi lịch</a>
        @endif
        
        @if($booking->status === 'completed')
            @if(in_array($booking->id, $reviewedBookingIds))
                <button disabled class="cursor-not-allowed rounded-lg border border-stone-200 bg-stone-100 px-3 py-2 text-xs font-bold text-stone-400">Đã đánh giá</button>
            @else
                <button onclick="openReviewModal({{ $booking->id }}, {{ $booking->court_id }}, '{{ addslashes($booking->court?->venue?->name ?? 'Cơ sở này') }}')" class="rounded-lg bg-amber-400 px-3 py-2 text-xs font-bold text-zinc-900 transition hover:bg-amber-500 shadow-sm">Đánh giá</button>
            @endif
        @endif

        @if($isEligibleStatus)
            @if($isPastStartTime)
                <button type="button" onclick="document.getElementById('lateCancelModal').classList.remove('hidden')" class="rounded-lg border border-stone-200 bg-stone-100 px-3 py-2 text-xs font-bold text-stone-500 transition hover:bg-stone-200 shadow-sm w-full sm:w-auto">
                    Hủy sân
                </button>
            @else
                <button type="button" onclick="openCancelModal({{ $booking->id }}, '{{ $ownerPhone ?? '' }}')" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 shadow-sm w-full sm:w-auto">
                    Hủy sân
                </button>
            @endif
        @endif
    </div>
</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 md:hidden">
            @foreach($bookings as $booking)
                @php
                    $statusMeta = $statusMap[$booking->status] ?? [
                        'label' => ucfirst($booking->status),
                        'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
                    ];
                    $slotDate = $booking->slot_date_label;
                    $mergedTimeStrings = $booking->merged_time_strings ?? [];
                    $ownerPhone = $booking->owner_phone;
                    $isEligibleStatus = (bool) $booking->is_eligible_status;
                    $isPastStartTime = (bool) $booking->is_past_start_time;
                @endphp
                <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Mã đơn #{{ $booking->id }}</p>
                            <h2 class="mt-1 text-base font-extrabold text-zinc-900">{{ $booking->court?->name ?? 'Chưa cập nhật' }}</h2>
                            <div class="mt-1 text-sm text-zinc-500 d-flex flex-wrap items-center gap-2">
                                <span>{{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}</span>
                                
                                @php
                                    $ownerPhone = $booking->court?->venue?->ownerRegistration?->phone 
                                               ?? $booking->court?->venue?->owner?->phone;
                                @endphp
                                
                                @if($ownerPhone)
                                    <span class="text-stone-300">•</span>
                                    <a href="tel:{{ $ownerPhone }}" class="inline-flex items-center gap-1 font-bold text-emerald-600 transition hover:text-emerald-800">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.48-4.18-7.076-7.076l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                        {{ $ownerPhone }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusMeta['class'] }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 border-t border-stone-100 pt-3 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Thời gian</p>
                            <p class="mt-1 font-bold text-zinc-800 mb-1">{{ $slotDate }}</p>
                            <div class="text-zinc-600 font-normal space-y-1">
                                @foreach($mergedTimeStrings as $timeStr)
                                    <p>{{ $timeStr }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Tổng tiền</p>
                            <p class="mt-1 font-black text-emerald-700">{{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    @if($booking->status === 'cancelled' && $booking->cancel_reason)
                        <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
                            <span class="font-semibold">Lý do hủy:</span> {{ $booking->cancel_reason }}
                        </div>
                    @endif

                    @if($isEligibleStatus)
                        @if($isPastStartTime)
                            <button type="button" onclick="document.getElementById('lateCancelModal').classList.remove('hidden')" class="mt-4 w-full rounded-lg border border-stone-200 bg-stone-100 px-3 py-2.5 text-sm font-bold text-stone-500 transition hover:bg-stone-200 shadow-sm">
                                Hủy sân
                            </button>
                        @else
                            <button type="button" onclick="openCancelModal({{ $booking->id }}, '{{ $ownerPhone ?? '' }}')" class="mt-4 w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-100 shadow-sm">
                                Hủy sân
                            </button>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
        @endif
    @endif
</div>
<!-- MODAL HỦY SÂN -->
<div id="lateCancelModal" class="fixed inset-0 z-[70] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-sm rounded-3xl bg-white shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900 mb-2">Không thể tự hủy ca này</h3>
            <p class="text-sm text-stone-600 mb-6 leading-relaxed">Đã đến giờ bắt đầu của ca sân đầu tiên trong đơn. Hệ thống đã tự động khóa chức năng tự hủy.<br><br>Nếu gặp sự cố bất khả kháng (mưa bão, ngập sân...), vui lòng <b>liên hệ trực tiếp với Chủ sân</b> qua số điện thoại trên đơn để được hỗ trợ hủy và hoàn tiền.</p>
            <button onclick="document.getElementById('lateCancelModal').classList.add('hidden')" class="w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-amber-600">Tôi đã hiểu</button>
        </div>
    </div>
</div>
<div id="cancelModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl overflow-hidden transform transition-all">
        <div class="border-b border-rose-100 bg-rose-50 p-5 flex items-center justify-between">
            <h3 class="text-lg font-bold text-rose-700 flex items-center gap-2">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Xác nhận Hủy Đặt Sân
            </h3>
            <button onclick="closeCancelModal()" class="text-rose-400 hover:text-rose-600 transition bg-white rounded-full p-1.5 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Loading State -->
            <div id="cancelFeeLoading" class="flex flex-col items-center justify-center py-6">
                <svg class="mb-3 h-8 w-8 animate-spin text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" /></svg>
                <p class="text-sm font-medium text-stone-500 animate-pulse">Đang tính toán chính sách hủy...</p>
            </div>

            <!-- Content State -->
            <div id="cancelFeeContent" class="hidden">
                <p class="text-sm text-stone-600 mb-4">Theo chính sách của hệ thống, khi hủy sân tại thời điểm này bạn sẽ chịu mức phí như sau:</p>
                
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 mb-5 text-sm space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-stone-500 font-semibold uppercase tracking-wider text-xs">Phí phạt hủy sân</span>
                        <strong id="feePercent" class="text-amber-700 bg-amber-100 px-2 py-0.5 rounded font-bold">0%</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-stone-500 font-semibold uppercase tracking-wider text-xs">Số tiền bị trừ</span>
                        <strong id="feeAmount" class="text-rose-600 font-black">0đ</strong>
                    </div>
                    <div class="flex justify-between items-center border-t border-amber-200/60 pt-3 mt-1">
                        <span class="text-stone-500 font-semibold uppercase tracking-wider text-xs">Tiền hoàn lại</span>
                        <strong id="refundAmount" class="text-emerald-600 font-black text-lg">0đ</strong>
                    </div>
                </div>
                <div id="cancelOwnerContact" class="mb-4 hidden rounded-xl border border-emerald-200 bg-emerald-50/50 p-3 text-sm shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-emerald-100 p-2 text-emerald-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.48-4.18-7.076-7.076l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-emerald-800">Liên hệ Chủ sân hỗ trợ:</p>
                            <a id="cancelOwnerPhone" href="#" class="text-base font-black text-emerald-600 transition hover:text-emerald-700 hover:underline"></a>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Lý do hủy (Không bắt buộc)</label>
                    <input type="text" id="cancelReasonInput" class="w-full rounded-xl border border-stone-300 bg-stone-50 p-3 text-sm outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10" placeholder="Ví dụ: Đội có việc bận đột xuất, trời mưa lớn...">
                </div>
            </div>
            
            <div id="cancelError" class="mt-4 hidden rounded-lg bg-rose-50 p-3 text-center text-sm font-semibold text-rose-600 border border-rose-100"></div>
        </div>
        
        <div class="border-t border-stone-100 bg-stone-50 p-4 flex justify-end gap-3 rounded-b-3xl">
            <button onclick="closeCancelModal()" class="rounded-xl px-5 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-200 transition">Không hủy nữa</button>
            <button id="btnConfirmCancel" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-rose-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">Xác nhận Hủy</button>
        </div>
    </div>
</div>
<div id="reviewModal" class="fixed inset-0 z-[80] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl overflow-hidden transform transition-all">
        <div class="border-b border-stone-100 bg-stone-50 p-5 flex items-center justify-between">
            <h3 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                <span class="text-2xl">⭐</span> Đánh giá cơ sở: <span id="modalCourtName" class="text-emerald-600"></span>
            </h3>
            <button onclick="closeReviewModal()" class="text-stone-400 hover:text-stone-600 transition bg-white rounded-full p-1.5 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div class="p-6">
            <input type="hidden" id="revBookingId">
            <input type="hidden" id="revCourtId">
            <input type="hidden" id="revRating" value="5">

            <div class="mb-5 text-center">
                <p class="text-sm font-medium text-stone-500 mb-2">Trải nghiệm của bạn như thế nào?</p>
                <div class="flex justify-center gap-2 mb-2">
                    <button type="button" class="star-btn transition hover:scale-110" onmouseover="hoverStar(1)" onmouseout="resetStar()" onclick="setRating(1)">
                        <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </button>
                    <button type="button" class="star-btn transition hover:scale-110" onmouseover="hoverStar(2)" onmouseout="resetStar()" onclick="setRating(2)">
                        <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </button>
                    <button type="button" class="star-btn transition hover:scale-110" onmouseover="hoverStar(3)" onmouseout="resetStar()" onclick="setRating(3)">
                        <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </button>
                    <button type="button" class="star-btn transition hover:scale-110" onmouseover="hoverStar(4)" onmouseout="resetStar()" onclick="setRating(4)">
                        <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </button>
                    <button type="button" class="star-btn transition hover:scale-110" onmouseover="hoverStar(5)" onmouseout="resetStar()" onclick="setRating(5)">
                        <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </button>
                </div>
                <span id="starLabel" class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Tuyệt vời</span>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Nhận xét chi tiết (Tùy chọn)</label>
                <textarea id="revContent" rows="3" class="w-full rounded-xl border border-stone-300 bg-stone-50 p-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" placeholder="Chia sẻ thêm về trải nghiệm của bạn (mặt sân, ánh sáng, dịch vụ)..."></textarea>
            </div>
            
            <div id="revError" class="mt-4 hidden rounded-lg bg-rose-50 p-3 text-center text-sm font-semibold text-rose-600 border border-rose-100"></div>
        </div>
        
        <div class="border-t border-stone-100 bg-stone-50 p-4 flex justify-end gap-3 rounded-b-3xl">
            <button onclick="closeReviewModal()" class="rounded-xl px-5 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-200 transition">Hủy</button>
            <button id="btnSubmitReview" onclick="submitReview()" class="rounded-xl bg-amber-400 px-6 py-2.5 text-sm font-bold text-zinc-900 shadow-md transition hover:bg-amber-500 active:scale-95 disabled:opacity-50">Gửi đánh giá</button>
        </div>
    </div>
</div>
<script>
    const labels = ["Tệ", "Không hài lòng", "Bình thường", "Tốt", "Tuyệt vời"];
    let currentRating = 5;

    function openReviewModal(bookingId, courtId, courtName) {
        document.getElementById('revBookingId').value = bookingId;
        document.getElementById('revCourtId').value = courtId;
        document.getElementById('modalCourtName').innerText = courtName;
        document.getElementById('revContent').value = '';
        document.getElementById('revError').classList.add('hidden');
        setRating(5);
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function hoverStar(rating) { renderStars(rating, true); }
    function resetStar() { renderStars(currentRating, false); }
    
    function setRating(rating) {
        currentRating = rating;
        document.getElementById('revRating').value = rating;
        renderStars(rating, false);
    }

    function renderStars(rating, isHover) {
        const stars = document.querySelectorAll('.star-btn svg');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-stone-300');
                star.classList.add('text-amber-400');
            } else {
                star.classList.remove('text-amber-400');
                star.classList.add('text-stone-300');
            }
        });
        document.getElementById('starLabel').innerText = labels[rating - 1];
    }

    async function submitReview() {
        const btn = document.getElementById('btnSubmitReview');
        const bookingId = document.getElementById('revBookingId').value;
        const courtId = document.getElementById('revCourtId').value;
        const rating = document.getElementById('revRating').value;
        const content = document.getElementById('revContent').value;
        const errorDiv = document.getElementById('revError');
        const token = localStorage.getItem('sporthub_token');

        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
        errorDiv.classList.add('hidden');

        try {
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (token) headers.Authorization = `Bearer ${token}`;

            const response = await fetch(`/api/courts/${courtId}/reviews`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ booking_id: bookingId, rating, content })
            });

            const data = await response.json();

            if (!response.ok) {
                errorDiv.textContent = data.message || 'Có lỗi xảy ra.';
                errorDiv.classList.remove('hidden');
            } else {
                alert('Đánh giá thành công! Cảm ơn bạn.');
                window.location.reload(); // Tải lại trang để cập nhật nút thành "Đã đánh giá"
            }
        } catch (error) {
            errorDiv.textContent = 'Lỗi kết nối máy chủ.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Gửi đánh giá';
        }
    }
    // === LOGIC XỬ LÝ HỦY SÂN ===
    let currentCancelBookingId = null;

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }

    async function openCancelModal(bookingId, ownerPhone = '') {
        currentCancelBookingId = bookingId;
        const modal = document.getElementById('cancelModal');
        const loading = document.getElementById('cancelFeeLoading');
        const content = document.getElementById('cancelFeeContent');
        const btnConfirm = document.getElementById('btnConfirmCancel');
        const errorDiv = document.getElementById('cancelError');
        
        // CODE AN TOÀN CHỐNG SẬP JS
        const contactDiv = document.getElementById('cancelOwnerContact');
        const phoneLink = document.getElementById('cancelOwnerPhone');
        
        if (contactDiv && phoneLink) {
            if (ownerPhone && ownerPhone.trim() !== '') {
                phoneLink.textContent = ownerPhone;
                phoneLink.href = `tel:${ownerPhone}`;
                contactDiv.classList.remove('hidden');
            } else {
                contactDiv.classList.add('hidden');
            }
        }

        errorDiv.classList.add('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        document.getElementById('cancelReasonInput').value = '';
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`/account/bookings/${bookingId}/cancel-fee`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!res.ok) throw new Error(data.message || 'Không thể lấy thông tin phí hủy.');

            // Format tiền
            const formatVND = (num) => new Intl.NumberFormat('vi-VN').format(num) + 'đ';

            document.getElementById('feePercent').textContent = data.fee_percent + '%';
            document.getElementById('feeAmount').textContent = formatVND(data.cancellation_fee);
            document.getElementById('refundAmount').textContent = formatVND(data.refund_amount);

            loading.classList.add('hidden');
            content.classList.remove('hidden');
            btnConfirm.disabled = false;
        } catch (error) {
            loading.classList.add('hidden');
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('hidden');
            btnConfirm.disabled = true;
        }
    }

    document.getElementById('btnConfirmCancel').addEventListener('click', async function() {
        const btn = this;
        const errorDiv = document.getElementById('cancelError');
        const reason = document.getElementById('cancelReasonInput').value.trim();
        
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';
        errorDiv.classList.add('hidden');
        
        try {
            const res = await fetch(`/account/bookings/${currentCancelBookingId}/cancel`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason || 'Khách hàng tự hủy trên web' })
            });
            
            const data = await res.json();
            
            if (res.ok) {
                alert("Hủy sân thành công! Số tiền hoàn lại sẽ được xử lý tự động.");
                window.location.reload(); 
            } else {
                throw new Error(data.message || 'Hủy sân thất bại.');
            }
        } catch (error) {
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Xác nhận Hủy';
        }
    });
</script>
@endsection
