@extends('layouts.app')

@section('title', 'Thanh toán VNPay | SportHub')

@section('content')
@php
    $slotDate = $booking->slot_date?->format('d/m/Y') ?? '';
    $courtName = $booking->court?->name ?? 'Sân chưa cập nhật';
    $venueName = $booking->court?->venue?->name ?? 'Cơ sở chưa cập nhật';
    $timeRanges = collect($bookingGroup ?? [$booking])
        ->sortBy('start_time')
        ->map(fn ($item) => substr((string) $item->start_time, 0, 5) . ' - ' . substr((string) $item->end_time, 0, 5))
        ->values();
    $paymentStatusLabel = match ($booking->payment_status) {
        'paid' => 'Đã thanh toán',
        'pending' => 'Chờ thanh toán',
        default => 'Chưa thanh toán',
    };
@endphp

<div class="min-h-[calc(100vh-120px)] bg-slate-50">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('web.bookings.success', $booking) }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Quay lại chi tiết booking
            </a>
        </div>

        <div class="mb-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-emerald-700">SportHub Checkout</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950 sm:text-4xl">Thanh toán VNPay</h1>
            <p class="mt-3 max-w-2xl text-base font-medium leading-7 text-slate-600">
                Quét mã QR hoặc bấm nút thanh toán để hoàn tất đặt sân.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Thông tin booking</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-950">Booking #{{ $booking->id }}</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700 ring-1 ring-amber-200">
                        {{ $paymentStatusLabel }}
                    </span>
                </div>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Cơ sở</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $venueName }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Sân</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $courtName }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Ngày đặt</dt>
                        <dd class="mt-1 text-sm font-black text-slate-900">{{ $slotDate }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-slate-400">Khung giờ</dt>
                        <dd class="mt-1 space-y-1 text-sm font-black text-slate-900">
                            @foreach($timeRanges as $timeRange)
                                <p>{{ $timeRange }}</p>
                            @endforeach
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-2xl bg-emerald-50 p-5 ring-1 ring-emerald-100">
                    <p class="text-sm font-black uppercase tracking-wider text-emerald-700">Số tiền cần thanh toán</p>
                    <p class="mt-2 text-3xl font-black text-emerald-700">{{ number_format((float) $totalAmount, 0, ',', '.') }}đ</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="text-center">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">QR VNPay động</p>
                    <h2 class="mt-2 text-xl font-black text-slate-950">Quét mã để thanh toán</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">
                        Dùng ứng dụng ngân hàng hoặc ví hỗ trợ VNPay để quét mã.
                    </p>
                </div>

                <div class="mt-5 flex justify-center rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-100" id="vnpayQrBox" data-payment-url="{{ $paymentUrl }}">
                    <img
                        id="vnpayQrImage"
                        src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=12&data={{ urlencode($paymentUrl) }}"
                        alt="QR thanh toán VNPay cho booking #{{ $booking->id }}"
                        class="h-[260px] w-[260px] rounded-xl bg-white p-2 shadow-sm"
                    >
                    <canvas id="vnpayQrCanvas" class="hidden rounded-xl bg-white"></canvas>
                </div>

                <div class="mt-5 space-y-3">
                    <a href="{{ route('bookings.payment.vnpay_start', $booking) }}" class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3.5 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 active:scale-[0.98]">
                        Thanh toán bằng VNPay
                    </a>
                    <a href="{{ $paymentUrl }}" class="flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        Mở cổng thanh toán VNPay
                    </a>
                    <a href="{{ route('web.bookings.success', $booking) }}" class="flex w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-100">
                        Quay lại chi tiết booking
                    </a>
                </div>

                <p class="mt-4 text-center text-xs font-semibold leading-5 text-slate-400">
                    Nếu bạn đang dùng điện thoại, hãy bấm nút thanh toán để mở cổng VNPay trực tiếp.
                </p>
            </section>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const box = document.getElementById('vnpayQrBox');
        const canvas = document.getElementById('vnpayQrCanvas');
        const image = document.getElementById('vnpayQrImage');

        if (!box || !canvas || typeof QRCode === 'undefined') {
            return;
        }

        const paymentUrl = box.dataset.paymentUrl;

        if (!paymentUrl) {
            return;
        }

        QRCode.toCanvas(canvas, paymentUrl, {
            width: 260,
            margin: 2
        }, function (error) {
            if (error) {
                console.error(error);
                return;
            }

            canvas.classList.remove('hidden');
            image?.classList.add('hidden');
        });
    });
</script>
@endsection
