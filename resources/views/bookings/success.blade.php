@extends('layouts.app')

@section('title', 'Chi tiết đặt lịch | SportHub')

@section('content')
@php
    $slotDate = $booking->slot_date?->format('d/m/Y') ?? '';
    $totalPriceStr = number_format((float) $totalGroupPrice, 0, ',', '.') . ' ₫';
    $statusLabel = $booking->status === 'pending' ? 'Chờ chủ sân xác nhận' : $statusMeta['label'];
    
    // Lấy thông tin môn thể thao và SĐT
    $sportName = $booking->court?->venue?->sport?->name ?? 'Thể thao';
    // Ưu tiên lấy SĐT từ bảng OwnerRegistration, nếu không có mới tìm trong bảng Venue
    $venuePhone = $booking->court?->venue?->ownerRegistration?->phone ?? $booking->court?->venue?->phone ?? 'Chưa cập nhật';
    $userPhone = Auth::user()->phone ?? 'Chưa cập nhật';
@endphp

<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 pb-24">
    
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('account.bookings.index') }}" class="grid h-10 w-10 place-items-center rounded-full bg-stone-200/50 text-zinc-600 transition hover:bg-stone-200">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </a>
        <h1 class="text-xl font-extrabold text-zinc-900">Chi tiết đặt lịch</h1>
    </div>

    <div class="mb-5 overflow-hidden rounded-2xl bg-emerald-800 text-white shadow-md">
        <div class="flex items-center gap-4 p-5 sm:p-6">
            <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-emerald-100 text-2xl font-black text-emerald-800 ring-4 ring-emerald-700/50">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 space-y-1.5">
                <p class="text-sm font-medium text-emerald-100/80">KH: <span class="font-bold text-white text-base">{{ Auth::user()->name }}</span></p>
                <p class="text-sm font-medium text-emerald-100/80">Đối tượng: <span class="font-bold text-white">Sân {{ $sportName }}</span></p>
                <p class="text-sm font-medium text-emerald-100/80">Số điện thoại: <span class="font-bold text-white">{{ $userPhone }}</span></p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        
        <div class="border-b border-stone-100 bg-stone-50/50 px-5 py-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 15.75h3.75M18 19.5V5.25c0-.414-.336-.75-.75-.75H6.75c-.414 0-.75.336-.75.75v14.25c0 .414.336.75.75.75h10.5c.414 0 .75-.336.75-.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 3.75V5.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75V5.25" /></svg>
            <h2 class="text-base font-extrabold text-zinc-900">Thông tin</h2>
        </div>

        <div class="p-5 sm:p-6 space-y-5">
            <div class="space-y-3">
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Mã lịch đặt:</p>
                    <p class="text-sm font-black text-zinc-900">#{{ $booking->id }}</p>
                </div>
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Trạng thái:</p>
                    <p class="text-sm font-bold {{ $booking->status === 'pending' ? 'text-amber-600' : 'text-emerald-600' }}">{{ $statusLabel }}</p>
                </div>
            </div>

            <div class="border-t border-stone-100 border-dashed"></div>

            <div class="space-y-3">
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Tên CLB:</p>
                    <p class="text-sm font-bold text-zinc-900 leading-relaxed">{{ $booking->court?->venue?->name ?? 'Chưa cập nhật' }}</p>
                </div>
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Địa chỉ:</p>
                    <p class="text-sm font-semibold text-zinc-700 leading-relaxed">{{ $booking->court?->venue?->address ?? 'Chưa cập nhật' }}</p>
                </div>
                <div class="flex items-center gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Số điện thoại:</p>
                    <div class="flex items-center justify-between flex-1">
                        <p class="text-sm font-bold text-zinc-900">{{ $venuePhone }}</p>
                       
                    </div>
                </div>
            </div>

            <div class="border-t border-stone-100 border-dashed"></div>

            <div class="space-y-3">
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Ngày đá:</p>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-zinc-900 mb-1.5">{{ $slotDate }}</p>
                        @foreach($bookingGroup as $b)
                            <p class="text-sm font-semibold text-zinc-700">- Sân {{ $b->court->name }}: {{ substr((string) $b->start_time, 0, 5) }} - {{ substr((string) $b->end_time, 0, 5) }}</p>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Tổng giờ:</p>
                    <p class="text-sm font-bold text-zinc-900">{{ $totalDurationStr }}</p>
                </div>
                
                <div class="flex items-start gap-x-4 mt-2">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Tổng tiền:</p>
                    <p class="text-base font-black text-emerald-600">{{ $totalPriceStr }}</p>
                </div>

                <div class="flex items-start gap-x-4 pt-2">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Thanh toán:</p>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-zinc-900 mb-1">Thanh toán trực tuyến</p>
                        <span class="inline-flex items-center gap-1.5 rounded bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600 ring-1 ring-inset ring-amber-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Chờ thanh toán
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <a href="{{ route('account.bookings.index') }}" class="flex-1 flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-3.5 text-sm font-black uppercase tracking-widest text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-700 active:scale-[0.98]">
            Quản lý lịch đặt
        </a>
    </div>

</div>
@endsection