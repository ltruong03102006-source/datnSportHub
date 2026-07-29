@extends('layouts.app')

@section('title', 'Khởi tạo VNPay - Gói đặt sân | SportHub')

@section('content')
<div class="min-h-[calc(100vh-120px)] bg-slate-50">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-3xl font-black text-slate-950">Chuẩn bị chuyển đến VNPay</h1>
            <p class="mt-4 text-base leading-7 text-slate-600">
                Đang chuẩn bị thông tin thanh toán cho gói đặt sân #{{ $bookingPackage->id }}.
            </p>
            <p class="mt-2 text-sm text-slate-500">Màn hình sẽ tự chuyển tiếp tới cổng thanh toán VNPay trong giây lát.</p>

            <div class="mx-auto mt-8 flex max-w-md items-center justify-center gap-3">
                <svg class="h-10 w-10 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                    <path d="M22 12a10 10 0 0 1-10 10" stroke-linecap="round" />
                </svg>
                <span class="text-sm font-black uppercase tracking-[0.2em] text-slate-600">ĐANG CHUYỂN</span>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('package-bookings.show', $bookingPackage) }}" class="inline-flex items-center justify-center rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-100">
                    Quay lại chi tiết gói
                </a>
                <a href="javascript:window.location.reload()" class="inline-flex items-center justify-center rounded-3xl bg-blue-600 px-5 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                    Tải lại nếu không tự chuyển
                </a>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            window.location.href = '{{ route('package-bookings.payment.vnpay_start', $bookingPackage) }}';
        }, 500);
    });
</script>
@endsection
