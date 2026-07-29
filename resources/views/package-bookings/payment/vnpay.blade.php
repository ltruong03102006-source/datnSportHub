@extends('layouts.app')

@section('title', 'Thanh toán VNPay - Gói đặt sân | SportHub')

@section('content')
<div class="min-h-[calc(100vh-120px)] bg-slate-50">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('package-bookings.show', $bookingPackage) }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Quay lại chi tiết gói
            </a>
        </div>

        <div class="mb-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-emerald-700">SportHub Checkout</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950 sm:text-4xl">Thanh toán VNPay cho gói đặt sân</h1>
            <p class="mt-3 max-w-2xl text-base font-medium leading-7 text-slate-600">
                Gói của bạn đang chờ thanh toán. Nhấn nút để tiếp tục tới VNPay và hoàn tất đặt lịch.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Thông tin gói</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-950">Gói #{{ $bookingPackage->id }}</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700 ring-1 ring-amber-200">
                        {{ ucfirst(str_replace('_', ' ', $bookingPackage->status)) }}
                    </span>
                </div>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Cơ sở</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $bookingPackage->venue->name }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Bắt đầu</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $bookingPackage->start_date?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Kết thúc</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $bookingPackage->end_date?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Số buổi</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $bookingPackage->total_sessions }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="text-center">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Thanh toán gói</p>
                    <h2 class="mt-2 text-xl font-black text-slate-950">Số tiền cần thanh toán</h2>
                    <p class="mt-3 text-4xl font-black text-emerald-700">{{ number_format((float) $bookingPackage->final_amount, 0, ',', '.') }}đ</p>
                </div>

                <div class="mt-6 space-y-4">
                    @if($packageHoldExpired)
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                            Thời gian giữ chỗ đã hết. Vui lòng tạo lại gói nếu bạn vẫn muốn đặt lịch.
                        </div>
                    @else
                        @if($packageHoldExpiresAt)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                                Mã giữ chỗ còn hiệu lực đến {{ $packageHoldExpiresAt->format('H:i d/m/Y') }}.
                            </div>
                        @endif

                        <a href="{{ route('package-bookings.payment.vnpay_start', $bookingPackage) }}" class="inline-flex w-full items-center justify-center gap-3 rounded-3xl bg-blue-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 active:scale-[0.98]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C7.58172 2 4 5.58172 4 10V14C4 18.4183 7.58172 22 12 22C16.4183 22 20 18.4183 20 14V10C20 5.58172 16.4183 2 12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12 7V12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Mở cổng VNPay
                        </a>

                        <p class="text-sm text-slate-500">Sau khi thanh toán thành công, hệ thống sẽ kích hoạt gói và sinh lịch đặt sân.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
