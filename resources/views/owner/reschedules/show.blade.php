@extends('owner.layoutOwner.app')

@section('title', 'Chi tiết yêu cầu đổi lịch | Chủ sân')

@section('content')
@php($code = $rescheduleRequest->request_code ?: (string) $rescheduleRequest->id)
@php($status = $rescheduleRequest->status)
@php($statusLabel = [
    'pending' => 'Chờ duyệt',
    'approved' => 'Đã duyệt',
    'rejected' => 'Đã từ chối',
    'cancelled' => 'Đã hủy',
][$status] ?? ucfirst($status))

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Back button -->
    <a href="{{ route('owner.web.reschedule.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-700 hover:text-emerald-800 transition-colors">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Quay lại danh sách yêu cầu
    </a>

    <!-- Header info -->
    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between border-b border-slate-200/80 pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700 font-mono border border-slate-200/80">
                    #{{ $code }}
                </span>
                <span class="text-xs font-bold text-slate-400">· Đơn đặt sân #{{ $rescheduleRequest->booking_id }}</span>
            </div>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-900">Chi tiết yêu cầu đổi lịch</h1>
            <p class="mt-1 text-sm font-semibold text-slate-500">
                {{ $rescheduleRequest->booking?->court?->venue?->name }} · {{ $rescheduleRequest->booking?->court?->name }}
            </p>
        </div>

        <div>
            @if($status === 'pending')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-4 py-2 text-xs font-black text-amber-800 border border-amber-200/80 shadow-xs whitespace-nowrap">
                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Chờ duyệt
                </span>
            @elseif($status === 'approved')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-800 border border-emerald-200/80 shadow-xs whitespace-nowrap">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Đã duyệt
                </span>
            @elseif($status === 'rejected')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-4 py-2 text-xs font-black text-rose-800 border border-rose-200/80 shadow-xs whitespace-nowrap">
                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Đã từ chối
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 border border-slate-200/80 shadow-xs whitespace-nowrap">
                    {{ $statusLabel }}
                </span>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="mt-5 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 font-bold text-red-800 shadow-xs">
            <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <!-- Customer & Booking Info Sidebar -->
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <h2 class="text-base font-black text-zinc-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                Thông tin khách hàng
            </h2>

            <div class="mt-4 flex items-center gap-3.5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-emerald-800 uppercase shadow-2xs">
                    {{ mb_substr($rescheduleRequest->user?->name ?? 'K', 0, 1) }}
                </div>
                <div>
                    <h3 class="font-black text-zinc-900 text-base leading-tight">{{ $rescheduleRequest->user?->name ?? 'Khách hàng' }}</h3>
                    <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $rescheduleRequest->user?->email ?? 'Chưa có email' }}</p>
                </div>
            </div>

            @php($phoneStr = $rescheduleRequest->user?->phone)
            @if($phoneStr)
                <div class="mt-4 flex items-center gap-2">
                    <a href="tel:{{ $phoneStr }}" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 transition hover:bg-emerald-100">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.826-1.47-5.114-3.758-6.584-6.584l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        Gọi {{ $phoneStr }}
                    </a>

                    <a href="https://zalo.me/{{ $phoneStr }}" target="_blank" class="inline-flex items-center justify-center gap-1 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-black text-sky-700 transition hover:bg-sky-100">
                        Zalo
                    </a>
                </div>
            @endif

            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-xs">
                <div class="flex justify-between">
                    <dt class="font-bold text-slate-400">Mã đơn gốc:</dt>
                    <dd class="font-black text-zinc-900">#{{ $rescheduleRequest->booking_id }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="font-bold text-slate-400">Ngày yêu cầu:</dt>
                    <dd class="font-black text-zinc-900">{{ $rescheduleRequest->created_at?->format('d/m/Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="font-bold text-slate-400 mb-1">Lý do từ khách hàng:</dt>
                    <dd class="rounded-xl bg-slate-50 p-3 font-semibold text-slate-700 border border-slate-200/60 italic">
                        "{{ $rescheduleRequest->reason ?: 'Không nhập lý do' }}"
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Slot Change Comparison -->
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2">
            <h2 class="text-base font-black text-zinc-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Chi tiết ca muốn đổi ({{ $requests->count() }} ca)
            </h2>

            <div class="mt-4 space-y-3">
                @foreach($requests as $item)
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 transition hover:bg-slate-50">
                        <div class="grid gap-4 sm:grid-cols-11 sm:items-center">
                            <!-- Old Slot -->
                            <div class="sm:col-span-5 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <span class="inline-flex items-center gap-1 text-[11px] font-black uppercase tracking-wider text-slate-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Ca cũ đã đặt
                                </span>
                                <p class="mt-1.5 text-base font-black text-zinc-900">
                                    {{ $item->old_slot_date?->format('d/m/Y') }}
                                </p>
                                <p class="mt-0.5 text-xs font-bold text-slate-600">
                                    {{ substr($item->old_start_time, 0, 5) }} - {{ substr($item->old_end_time, 0, 5) }}
                                </p>
                            </div>

                            <!-- Arrow Indicator -->
                            <div class="flex items-center justify-center sm:col-span-1">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 shadow-2xs">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </span>
                            </div>

                            <!-- New Slot -->
                            <div class="sm:col-span-5 rounded-xl border border-emerald-200/80 bg-emerald-50/60 p-3.5 shadow-2xs">
                                <span class="inline-flex items-center gap-1 text-[11px] font-black uppercase tracking-wider text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Ca mới xin đổi sang
                                </span>
                                <p class="mt-1.5 text-base font-black text-emerald-950">
                                    {{ $item->new_slot_date?->format('d/m/Y') }}
                                </p>
                                <p class="mt-0.5 text-xs font-bold text-emerald-800">
                                    {{ substr($item->new_start_time ?? $item->newTimeSlot?->start_time, 0, 5) }}
                                    -
                                    {{ substr($item->new_end_time ?? $item->newTimeSlot?->end_time, 0, 5) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Action Section -->
    @if($status === 'pending')
        <section class="mt-6 rounded-2xl border border-amber-200/80 bg-amber-50/30 p-6 shadow-xs">
            <div class="border-b border-amber-200/60 pb-4">
                <h2 class="text-lg font-black text-amber-950 flex items-center gap-2">
                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25h3c.877 0 1.637.5 2.01 1.228m-5.8 0a2.25 2.25 0 00-1.88 1.139m5.68 0a2.25 2.25 0 011.88 1.139m-5.8 0L9.011 8.528A2.25 2.25 0 008.25 9.75v8.25a2.25 2.25 0 002.25 2.25h6a2.25 2.25 0 002.25-2.25v-8.25a2.25 2.25 0 00-.761-1.222l-1.539-1.912" />
                    </svg>
                    Xử lý phê duyệt yêu cầu đổi lịch
                </h2>
                <p class="mt-1 text-xs font-semibold text-amber-800">
                    Bấm "Duyệt yêu cầu" để cập nhật lịch mới cho khách, hoặc điền ghi chú rồi bấm "Từ chối".
                </p>
            </div>

            <div class="mt-5">
                <form method="POST" action="{{ route('owner.web.reschedule.reject', $code) }}" id="reject-reschedule-form">
                    @csrf
                    <label class="mb-2 block text-xs font-black text-slate-700">
                        Lý do từ chối
                        <span class="font-semibold text-slate-400">(nhập khi muốn từ chối yêu cầu)</span>
                    </label>
                    <textarea name="owner_note"
                              rows="3"
                              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-xs outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-500/10"
                              placeholder="Ví dụ: Khung giờ mới đã có lịch bảo trì, sân đã được đặt từ trước..."></textarea>
                </form>

                <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button form="reject-reschedule-form"
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-xs font-black text-red-700 shadow-2xs transition hover:border-red-300 hover:bg-red-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Từ chối yêu cầu
                    </button>

                    <form method="POST" action="{{ route('owner.web.reschedule.approve', $code) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-xs font-black text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-700 sm:w-auto">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Duyệt yêu cầu đổi lịch
                        </button>
                    </form>
                </div>
            </div>
        </section>
    @elseif($rescheduleRequest->owner_note || $rescheduleRequest->rejected_reason)
        <section class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400">Ghi chú phản hồi của chủ sân</h2>
            <p class="mt-2 text-sm font-bold text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                "{{ $rescheduleRequest->owner_note ?: $rescheduleRequest->rejected_reason }}"
            </p>
        </section>
    @endif
</div>
@endsection
