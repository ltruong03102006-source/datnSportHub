@extends('layouts.app')

@section('title', 'Lịch sử đặt sân | SportHub')

@section('content')
@php
    $bookingPackages = $bookingPackages ?? collect();

    $bookingCount = method_exists($bookings, 'total')
        ? $bookings->total()
        : $bookings->count();

    $packageCount = $bookingPackages->count();

    $activeTab = request('tab');

    if (! in_array($activeTab, ['single', 'package'], true)) {
        $activeTab = $bookings->isNotEmpty()
            ? 'single'
            : ($bookingPackages->isNotEmpty() ? 'package' : 'single');
    }

    $totalHistoryCount = $bookingCount + $packageCount;

    $packageStatusLabels = [
        'pending_payment' => 'Chờ thanh toán',
        'active' => 'Đang hoạt động',
        'paused' => 'Tạm dừng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'expired' => 'Hết hạn',
    ];

    $packageStatusClasses = [
        'pending_payment' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'paused' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'completed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'expired' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
@endphp

<div class="min-h-[calc(100vh-80px)] bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- HEADER --}}
        <div class="mb-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="relative p-6 sm:p-7">
                <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-emerald-100/60 blur-3xl"></div>

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600">
                            Tài khoản SportHub
                        </p>

                        <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-900 sm:text-4xl">
                            Lịch sử đặt sân
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                            Quản lý toàn bộ đơn đặt sân lẻ và gói đặt sân của bạn. Bạn có thể xem chi tiết,
                            đổi lịch, hủy sân hoặc đánh giá sau khi hoàn thành.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                        <a href="{{ route('transactions.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                            Lịch sử giao dịch
                        </a>

                        <a href="{{ route('home') }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                            Đặt sân mới
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                            Đặt sân lẻ
                        </p>
                        <p class="mt-2 text-3xl font-black text-zinc-900">
                            {{ number_format($bookingCount) }}
                        </p>
                    </div>

                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                            Đặt theo gói
                        </p>
                        <p class="mt-2 text-3xl font-black text-emerald-700">
                            {{ number_format($packageCount) }}
                        </p>
                    </div>

                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                            Tổng lịch sử
                        </p>
                        <p class="mt-2 text-3xl font-black text-zinc-900">
                            {{ number_format($totalHistoryCount) }}
                        </p>
                    </div>

                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN PANEL --}}
        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">

            {{-- TAB HEADER --}}
            <div class="border-b border-slate-100 bg-white p-3">
                <div class="grid gap-2 md:grid-cols-2">
                    <button type="button"
                            data-history-tab-button="single"
                            onclick="switchHistoryTab('single')"
                            class="history-tab-button rounded-2xl px-4 py-4 text-left transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="tab-icon grid h-11 w-11 place-items-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black">Đặt sân lẻ</p>
                                    <p class="mt-1 text-xs font-semibold opacity-80">
                                        Đơn đặt theo ngày, theo ca
                                    </p>
                                </div>
                            </div>

                            <span class="tab-count rounded-full px-3 py-1 text-xs font-black">
                                {{ number_format($bookingCount) }}
                            </span>
                        </div>
                    </button>

                    <button type="button"
                            data-history-tab-button="package"
                            onclick="switchHistoryTab('package')"
                            class="history-tab-button rounded-2xl px-4 py-4 text-left transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="tab-icon grid h-11 w-11 place-items-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black">Đặt theo gói</p>
                                    <p class="mt-1 text-xs font-semibold opacity-80">
                                        Gói tuần, gói tháng, lịch cố định
                                    </p>
                                </div>
                            </div>

                            <span class="tab-count rounded-full px-3 py-1 text-xs font-black">
                                {{ number_format($packageCount) }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            {{-- TAB ĐẶT SÂN LẺ --}}
            <div id="history-tab-single" data-history-tab-panel="single" class="history-tab-panel">
                <div class="border-b border-slate-100 px-5 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-black text-zinc-900">
                                Danh sách đặt sân lẻ
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Quản lý các đơn đặt sân theo từng ngày và từng khung giờ.
                            </p>
                        </div>
                    </div>
                </div>

                @if($bookings->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                            </svg>
                        </div>

                        <h2 class="text-lg font-black text-zinc-900">
                            Bạn chưa có đơn đặt sân lẻ
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Khi bạn đặt sân theo từng ca, các đơn sẽ xuất hiện ở đây.
                        </p>

                        <a href="{{ route('home') }}"
                           class="mt-5 inline-flex rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                            Đặt sân ngay
                        </a>
                    </div>
                @else
                    {{-- DESKTOP TABLE --}}
                    <div class="hidden overflow-x-auto xl:block">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Đơn đặt</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Sân</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Thời gian</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Tổng tiền</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Trạng thái</th>
                                    <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Thao tác</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
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

                                    <tr class="align-top transition hover:bg-slate-50/80">
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="text-sm font-black text-zinc-900">#{{ $booking->id }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">Đặt sân lẻ</p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-zinc-900">
                                                {{ $booking->court?->name ?? 'Chưa cập nhật' }}
                                            </p>

                                            <p class="mt-1 max-w-[250px] text-xs font-semibold text-slate-500">
                                                {{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}
                                            </p>

                                            @if($ownerPhone)
                                                <a href="tel:{{ $ownerPhone }}"
                                                   class="mt-2 inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700 transition hover:bg-emerald-100">
                                                    {{ $ownerPhone }}
                                                </a>
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="text-sm font-black text-zinc-900">
                                                {{ $slotDate }}
                                            </p>

                                            <div class="mt-1 space-y-1 text-sm font-semibold text-slate-600">
                                                @forelse($mergedTimeStrings as $timeStr)
                                                    <p>{{ $timeStr }}</p>
                                                @empty
                                                    <p>—</p>
                                                @endforelse
                                            </div>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="text-sm font-black text-emerald-700">
                                                {{ number_format((float) $booking->total_price, 0, ',', '.') }}đ
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $statusMeta['class'] }}">
                                                {{ $statusMeta['label'] }}
                                            </span>

                                            @if($booking->status === 'cancelled' && $booking->cancel_reason)
                                                <p class="mt-2 max-w-[220px] text-xs leading-5 text-red-600">
                                                    <span class="font-bold">Lý do hủy:</span> {{ $booking->cancel_reason }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap items-center justify-end gap-2">
                                                <a href="{{ route('web.bookings.success', $booking->id) }}"
                                                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                                                    Chi tiết
                                                </a>

                                                @if($booking->status === 'confirmed' && !$isPastStartTime)
                                                    <a href="{{ route('customer.booking.reschedule.create', $booking->id) }}"
                                                       class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 transition hover:bg-amber-100">
                                                        Đổi lịch
                                                    </a>
                                                @endif

                                                @if($booking->status === 'completed')
                                                    @if(in_array($booking->id, $reviewedBookingIds))
                                                        <button disabled
                                                                class="cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-black text-slate-400">
                                                            Đã đánh giá
                                                        </button>
                                                    @else
                                                        <button onclick="openReviewModal({{ $booking->id }}, {{ $booking->court_id }}, '{{ addslashes($booking->court?->venue?->name ?? 'Cơ sở này') }}')"
                                                                class="rounded-xl bg-amber-400 px-3 py-2 text-xs font-black text-zinc-900 shadow-sm transition hover:bg-amber-500">
                                                            Đánh giá
                                                        </button>
                                                    @endif
                                                @endif

                                                @if($isEligibleStatus)
                                                    @if($isPastStartTime)
                                                        <button type="button"
                                                                onclick="document.getElementById('lateCancelModal').classList.remove('hidden')"
                                                                class="rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-black text-slate-500 transition hover:bg-slate-200">
                                                            Hủy sân
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                onclick="openCancelModal({{ $booking->id }}, '{{ $ownerPhone ?? '' }}')"
                                                                class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 transition hover:bg-red-100">
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

                    {{-- MOBILE / TABLET CARDS --}}
                    <div class="grid gap-4 p-4 xl:hidden">
                        @foreach($bookings as $booking)
                            @php
                                $statusMeta = $statusMap[$booking->status] ?? [
                                    'label' => ucfirst($booking->status),
                                    'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
                                ];

                                $slotDate = $booking->slot_date_label;
                                $mergedTimeStrings = $booking->merged_time_strings ?? [];
                                $ownerPhone = $booking->owner_phone;

                                if (! $ownerPhone) {
                                    $ownerPhone = $booking->court?->venue?->ownerRegistration?->phone
                                        ?? $booking->court?->venue?->owner?->phone;
                                }

                                $isEligibleStatus = (bool) $booking->is_eligible_status;
                                $isPastStartTime = (bool) $booking->is_past_start_time;
                            @endphp

                            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                            Mã đơn #{{ $booking->id }}
                                        </p>

                                        <h2 class="mt-1 text-base font-black text-zinc-900">
                                            {{ $booking->court?->name ?? 'Chưa cập nhật' }}
                                        </h2>

                                        <p class="mt-1 text-sm font-semibold text-slate-500">
                                            {{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}
                                        </p>
                                    </div>

                                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $statusMeta['class'] }}">
                                        {{ $statusMeta['label'] }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                            Thời gian
                                        </p>

                                        <p class="mt-1 font-black text-zinc-900">
                                            {{ $slotDate }}
                                        </p>

                                        <div class="mt-1 space-y-1 font-semibold text-slate-600">
                                            @forelse($mergedTimeStrings as $timeStr)
                                                <p>{{ $timeStr }}</p>
                                            @empty
                                                <p>—</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                            Tổng tiền
                                        </p>

                                        <p class="mt-1 font-black text-emerald-700">
                                            {{ number_format((float) $booking->total_price, 0, ',', '.') }}đ
                                        </p>

                                        @if($ownerPhone)
                                            <a href="tel:{{ $ownerPhone }}"
                                               class="mt-2 inline-flex justify-end rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">
                                                {{ $ownerPhone }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if($booking->status === 'cancelled' && $booking->cancel_reason)
                                    <div class="mt-3 rounded-2xl bg-red-50 px-3 py-2 text-xs leading-5 text-red-600">
                                        <span class="font-bold">Lý do hủy:</span> {{ $booking->cancel_reason }}
                                    </div>
                                @endif

                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                    <a href="{{ route('web.bookings.success', $booking->id) }}"
                                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                                        Chi tiết
                                    </a>

                                    @if($booking->status === 'confirmed' && !$isPastStartTime)
                                        <a href="{{ route('customer.booking.reschedule.create', $booking->id) }}"
                                           class="inline-flex items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm font-black text-amber-700 transition hover:bg-amber-100">
                                            Đổi lịch
                                        </a>
                                    @endif

                                    @if($booking->status === 'completed')
                                        @if(in_array($booking->id, $reviewedBookingIds))
                                            <button disabled
                                                    class="cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-black text-slate-400">
                                                Đã đánh giá
                                            </button>
                                        @else
                                            <button onclick="openReviewModal({{ $booking->id }}, {{ $booking->court_id }}, '{{ addslashes($booking->court?->venue?->name ?? 'Cơ sở này') }}')"
                                                    class="rounded-2xl bg-amber-400 px-3 py-2.5 text-sm font-black text-zinc-900 shadow-sm transition hover:bg-amber-500">
                                                Đánh giá
                                            </button>
                                        @endif
                                    @endif

                                    @if($isEligibleStatus)
                                        @if($isPastStartTime)
                                            <button type="button"
                                                    onclick="document.getElementById('lateCancelModal').classList.remove('hidden')"
                                                    class="rounded-2xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-black text-slate-500 transition hover:bg-slate-200">
                                                Hủy sân
                                            </button>
                                        @else
                                            <button type="button"
                                                    onclick="openCancelModal({{ $booking->id }}, '{{ $ownerPhone ?? '' }}')"
                                                    class="rounded-2xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-black text-red-700 transition hover:bg-red-100">
                                                Hủy sân
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>

            {{-- TAB ĐẶT THEO GÓI --}}
            <div id="history-tab-package" data-history-tab-panel="package" class="history-tab-panel hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-black text-zinc-900">
                                Danh sách gói đặt sân
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Theo dõi gói tuần, gói tháng, tiến độ sử dụng và trạng thái thanh toán.
                            </p>
                        </div>
                    </div>
                </div>

                @if($bookingPackages->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-500">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>

                        <h2 class="text-lg font-black text-zinc-900">
                            Bạn chưa có gói đặt sân
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Khi đăng ký gói tuần hoặc gói tháng, thông tin gói sẽ xuất hiện ở đây.
                        </p>
                    </div>
                @else
                    {{-- DESKTOP TABLE --}}
                    <div class="hidden overflow-x-auto xl:block">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-emerald-50/70">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-emerald-800">Gói</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-emerald-800">Cơ sở</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-emerald-800">Thời gian</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-emerald-800">Tiến độ</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-emerald-800">Thanh toán</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-emerald-800">Trạng thái</th>
                                    <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-emerald-800">Thao tác</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($bookingPackages as $bookingPackage)
                                    @php
                                        $transaction = $bookingPackage->transactions->first();

                                        $packageTypeLabel = $bookingPackage->package?->type === 'month'
                                            ? 'Gói tháng'
                                            : 'Gói tuần';

                                        $packageStatus = $bookingPackage->status ?? 'pending_payment';

                                        $packageStatusLabel = $packageStatusLabels[$packageStatus] ?? $packageStatus;

                                        $packageStatusClass = $packageStatusClasses[$packageStatus]
                                            ?? 'bg-stone-100 text-stone-600 ring-stone-200';

                                        $packageAmount = $bookingPackage->final_amount
                                            ?? $bookingPackage->total_amount
                                            ?? 0;

                                        $totalSessions = (int) ($bookingPackage->total_sessions ?: $bookingPackage->bookings->count());
                                        $usedSessions = (int) ($bookingPackage->used_sessions ?? 0);
                                        $progressPercent = $totalSessions > 0
                                            ? min(100, round(($usedSessions / $totalSessions) * 100))
                                            : 0;
                                    @endphp

                                    <tr class="align-top transition hover:bg-emerald-50/40">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-zinc-900">
                                                #PKG{{ $bookingPackage->id }}
                                            </p>

                                            <p class="mt-1 text-sm font-bold text-zinc-800">
                                                {{ $bookingPackage->package?->name ?? 'Gói đặt sân' }}
                                            </p>

                                            <p class="mt-1 text-xs font-semibold text-emerald-700">
                                                {{ $packageTypeLabel }} · {{ $bookingPackage->weekly_sessions }} buổi/tuần
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-zinc-900">
                                                {{ $bookingPackage->venue?->name ?? 'Chưa cập nhật cơ sở' }}
                                            </p>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-zinc-700">
                                            {{ $bookingPackage->start_date?->format('d/m/Y') }}
                                            -
                                            {{ $bookingPackage->end_date?->format('d/m/Y') }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-zinc-900">
                                                {{ $usedSessions }}/{{ $totalSessions }} buổi
                                            </p>

                                            <div class="mt-2 h-2 w-40 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-emerald-500"
                                                     style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="text-sm font-black text-emerald-700">
                                                {{ number_format((float) $packageAmount, 0, ',', '.') }}đ
                                            </p>

                                            @if($transaction)
                                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                                    {{ $transaction->transaction_code }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $packageStatusClass }}">
                                                {{ $packageStatusLabel }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ route('package-bookings.show', $bookingPackage) }}"
                                               class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 transition hover:bg-emerald-100">
                                                Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE / TABLET CARDS --}}
                    <div class="grid gap-4 p-4 xl:hidden">
                        @foreach($bookingPackages as $bookingPackage)
                            @php
                                $transaction = $bookingPackage->transactions->first();

                                $packageTypeLabel = $bookingPackage->package?->type === 'month'
                                    ? 'Gói tháng'
                                    : 'Gói tuần';

                                $packageStatus = $bookingPackage->status ?? 'pending_payment';

                                $packageStatusLabel = $packageStatusLabels[$packageStatus] ?? $packageStatus;

                                $packageStatusClass = $packageStatusClasses[$packageStatus]
                                    ?? 'bg-stone-100 text-stone-600 ring-stone-200';

                                $packageAmount = $bookingPackage->final_amount
                                    ?? $bookingPackage->total_amount
                                    ?? 0;

                                $totalSessions = (int) ($bookingPackage->total_sessions ?: $bookingPackage->bookings->count());
                                $usedSessions = (int) ($bookingPackage->used_sessions ?? 0);
                                $progressPercent = $totalSessions > 0
                                    ? min(100, round(($usedSessions / $totalSessions) * 100))
                                    : 0;
                            @endphp

                            <div class="rounded-3xl border border-emerald-100 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wider text-emerald-700">
                                            #PKG{{ $bookingPackage->id }} · {{ $packageTypeLabel }}
                                        </p>

                                        <h2 class="mt-1 text-base font-black text-zinc-900">
                                            {{ $bookingPackage->package?->name ?? 'Gói đặt sân' }}
                                        </h2>

                                        <p class="mt-1 text-sm font-semibold text-slate-500">
                                            {{ $bookingPackage->venue?->name ?? 'Chưa cập nhật cơ sở' }}
                                        </p>
                                    </div>

                                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $packageStatusClass }}">
                                        {{ $packageStatusLabel }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                            Thời gian
                                        </p>

                                        <p class="mt-1 font-black text-zinc-900">
                                            {{ $bookingPackage->start_date?->format('d/m/Y') }}
                                            -
                                            {{ $bookingPackage->end_date?->format('d/m/Y') }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                            Thanh toán
                                        </p>

                                        <p class="mt-1 font-black text-emerald-700">
                                            {{ number_format((float) $packageAmount, 0, ',', '.') }}đ
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-2xl bg-slate-50 p-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-bold text-slate-500">
                                            Tiến độ sử dụng
                                        </span>

                                        <span class="font-black text-zinc-900">
                                            {{ $usedSessions }}/{{ $totalSessions }} buổi
                                        </span>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-emerald-500"
                                             style="width: {{ $progressPercent }}%"></div>
                                    </div>

                                    <p class="mt-2 text-xs font-semibold text-slate-500">
                                        {{ $bookingPackage->weekly_sessions }} buổi/tuần
                                    </p>
                                </div>

                                <a href="{{ route('package-bookings.show', $bookingPackage) }}"
                                   class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-3 py-2.5 text-sm font-black text-white transition hover:bg-emerald-700">
                                    Xem chi tiết gói
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function switchHistoryTab(tabName) {
        document.querySelectorAll('[data-history-tab-panel]').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.dataset.historyTabPanel !== tabName);
        });

        document.querySelectorAll('[data-history-tab-button]').forEach(function (button) {
            const isActive = button.dataset.historyTabButton === tabName;

            button.classList.toggle('bg-emerald-600', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('shadow-sm', isActive);

            button.classList.toggle('bg-slate-50', ! isActive);
            button.classList.toggle('text-slate-700', ! isActive);
            button.classList.toggle('hover:bg-slate-100', ! isActive);

            const icon = button.querySelector('.tab-icon');
            const count = button.querySelector('.tab-count');

            if (icon) {
                icon.classList.toggle('bg-white/20', isActive);
                icon.classList.toggle('text-white', isActive);
                icon.classList.toggle('bg-slate-100', ! isActive);
                icon.classList.toggle('text-slate-500', ! isActive);
            }

            if (count) {
                count.classList.toggle('bg-white/20', isActive);
                count.classList.toggle('text-white', isActive);
                count.classList.toggle('bg-slate-100', ! isActive);
                count.classList.toggle('text-slate-600', ! isActive);
            }
        });

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function () {
        switchHistoryTab(@json($activeTab));
    });
</script>
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
