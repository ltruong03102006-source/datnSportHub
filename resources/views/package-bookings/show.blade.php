@extends('layouts.app')

@section('title', 'Chi tiết gói đặt sân | SportHub')

@section('content')
@php
    use Carbon\Carbon;

    $transaction = $bookingPackage->transactions->first();

    $transactionStatus = $transaction->payment_status ?? $transaction->status ?? null;

    $paidTransactionStatuses = ['success', 'paid', 'completed'];
    $paidPackageStatuses = ['active', 'paused', 'completed'];

    $isPaidTransaction = $transaction && in_array($transactionStatus, $paidTransactionStatuses, true);

    $isPaidPackage = in_array($bookingPackage->status, $paidPackageStatuses, true)
        || filled($bookingPackage->paid_at)
        || $isPaidTransaction;

    $owner = $bookingPackage->venue?->owner;
    $legalDoc = $bookingPackage->venue?->legalDocument;

    $bankName = $owner?->bank_name ?? $legalDoc?->bank_name;
    $bankAccountNo = $owner?->bank_account_no ?? $legalDoc?->bank_account_number;
    $bankAccountName = $owner?->bank_account_name ?? $legalDoc?->bank_account_holder ?? 'CHU SAN';

    $hasBankInfo = $bankName && $bankAccountNo;

    $finalAmount = (float) ($bookingPackage->final_amount ?? 0);
    $totalAmount = (float) ($bookingPackage->total_amount ?? 0);
    $discountAmount = (float) ($bookingPackage->discount_amount ?? 0);

    $qrUrl = null;

    if (! $isPaidPackage && $hasBankInfo && $finalAmount > 0) {
        $addInfo = 'THANH TOAN GOI PKG' . $bookingPackage->id;

        $qrUrl = 'https://img.vietqr.io/image/'
            . trim($bankName)
            . '-'
            . trim($bankAccountNo)
            . '-compact2.png?amount='
            . (int) $finalAmount
            . '&addInfo='
            . urlencode($addInfo)
            . '&accountName='
            . urlencode(trim($bankAccountName));
    }

    $statusLabels = [
        'pending_payment' => 'Chờ thanh toán',
        'active' => 'Đang hoạt động',
        'paused' => 'Tạm dừng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'expired' => 'Hết hạn',
    ];

    $statusClasses = [
        'pending_payment' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'paused' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'completed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'expired' => 'bg-stone-100 text-stone-600 ring-stone-200',
    ];

    $weekdayLabels = [
        0 => 'Chủ nhật',
        1 => 'Thứ 2',
        2 => 'Thứ 3',
        3 => 'Thứ 4',
        4 => 'Thứ 5',
        5 => 'Thứ 6',
        6 => 'Thứ 7',
    ];

    $bookingStatusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'completed' => 'Đã chơi',
        'cancelled' => 'Đã hủy',
    ];

    $bookingStatusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'completed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];

    $startDate = Carbon::parse($bookingPackage->start_date)->startOfDay();
    $endDate = Carbon::parse($bookingPackage->end_date)->startOfDay();

    $previewRows = collect();

    if ($bookingPackage->bookings->isEmpty()) {
        foreach ($bookingPackage->sessions as $session) {
            $weekday = (int) $session->weekday;

            $sessionSlotRows = method_exists($session, 'slots') && $session->slots->isNotEmpty()
                ? $session->slots->sortBy('slot_order')->values()
                : collect([(object) [
                    'timeSlot' => $session->timeSlot,
                    'price' => $session->price_per_session,
                ]]);

            $firstSessionSlot = $sessionSlotRows->first()?->timeSlot;
            $lastSessionSlot = $sessionSlotRows->last()?->timeSlot;

            $firstDate = $startDate->dayOfWeek === $weekday
                ? $startDate->copy()
                : $startDate->copy()->next($weekday);

            $cursor = $firstDate->copy();

            while ($cursor->lte($endDate)) {
                $previewRows->push([
                    'date' => $cursor->copy(),
                    'weekday' => $weekday,
                    'court_name' => $session->court?->name ?? '—',
                    'start_time' => $firstSessionSlot?->start_time,
                    'end_time' => $lastSessionSlot?->end_time,
                    'price' => (float) ($session->price_per_session ?? 0),
                    'status_label' => 'Chờ thanh toán',
                    'status_class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                ]);

                $cursor->addWeek();
            }
        }

        $previewRows = $previewRows->sortBy('date')->values();
    }

    $actualRows = $bookingPackage->bookings->map(function ($booking) use ($bookingStatusLabels, $bookingStatusClasses) {
        return [
            'date' => Carbon::parse($booking->slot_date),
            'weekday' => Carbon::parse($booking->slot_date)->dayOfWeek,
            'court_name' => $booking->court?->name ?? '—',
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'price' => (float) $booking->total_price,
            'status_label' => $bookingStatusLabels[$booking->status] ?? $booking->status,
            'status_class' => $bookingStatusClasses[$booking->status] ?? 'bg-stone-100 text-stone-600 ring-stone-200',
        ];
    })->sortBy('date')->values();

    $scheduleRows = $actualRows->isNotEmpty() ? $actualRows : $previewRows;

    $nextBooking = $bookingPackage->bookings
        ->filter(function ($booking) {
            $bookingDate = Carbon::parse($booking->slot_date)->toDateString();

            return ! in_array($booking->status, ['completed', 'cancelled'], true)
                && Carbon::parse($bookingDate . ' ' . $booking->start_time)->gte(now());
        })
        ->sortBy(function ($booking) {
            return Carbon::parse($booking->slot_date)->toDateString() . ' ' . $booking->start_time;
        })
        ->first();

    $nextScheduleRow = $scheduleRows
        ->filter(function (array $row) {
            if (empty($row['date']) || empty($row['start_time'])) {
                return false;
            }

            return ! in_array($row['status_label'] ?? null, ['Đã chơi', 'Đã hủy'], true)
                && Carbon::parse($row['date']->format('Y-m-d') . ' ' . $row['start_time'])->gte(now());
        })
        ->sortBy(function (array $row) {
            return $row['date']->format('Y-m-d') . ' ' . $row['start_time'];
        })
        ->first();

    $usedSessions = (int) ($bookingPackage->used_sessions ?? 0);
    $totalSessions = (int) ($bookingPackage->total_sessions ?: $scheduleRows->count());
    $remainingSessions = max(0, $totalSessions - $usedSessions);

    $progressPercent = $totalSessions > 0
        ? min(100, round(($usedSessions / $totalSessions) * 100))
        : 0;

    $packageTypeLabel = $bookingPackage->package?->type === 'month'
        ? 'Gói tháng'
        : 'Gói tuần';

    $durationLabel = trim(($bookingPackage->package?->duration ?? '—') . ' ' . ($bookingPackage->package?->type === 'month' ? 'tháng' : 'tuần'));

    $statusLabel = $statusLabels[$bookingPackage->status] ?? $bookingPackage->status;

    $statusClass = $statusClasses[$bookingPackage->status] ?? 'bg-stone-100 text-stone-600 ring-stone-200';

    $formatMoney = function ($value) {
        return number_format((float) $value, 0, ',', '.') . 'đ';
    };

    $formatTime = function ($value) {
        if (blank($value)) {
            return '—';
        }

        return substr((string) $value, 0, 5);
    };
@endphp

<div class="min-h-[calc(100vh-80px)] bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('account.bookings.index', ['tab' => 'package']) }}"
               class="inline-flex w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                ← Quay lại lịch sử
            </a>

            <span class="inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-black ring-1 ring-inset {{ $statusClass }}">
                {{ $statusLabel }}
            </span>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm">
            <div class="relative p-6 sm:p-7">
                <div class="absolute right-0 top-0 h-44 w-44 rounded-full bg-emerald-100/70 blur-3xl"></div>

                <div class="relative grid gap-6 lg:grid-cols-[1fr_320px] lg:items-center">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600">
                            Gói đặt sân #PKG{{ $bookingPackage->id }}
                        </p>

                        <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-900 sm:text-4xl">
                            {{ $bookingPackage->package?->name ?? 'Gói đặt sân' }}
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                            {{ $bookingPackage->venue?->name ?? 'Chưa cập nhật cơ sở' }}
                            ·
                            {{ $bookingPackage->start_date?->format('d/m/Y') }}
                            -
                            {{ $bookingPackage->end_date?->format('d/m/Y') }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                {{ $packageTypeLabel }}
                            </span>

                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                {{ $bookingPackage->weekly_sessions }} buổi/tuần
                            </span>

                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                {{ $durationLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                        <p class="text-xs font-black uppercase tracking-wider text-emerald-700">
                            {{ $isPaidPackage ? 'Đã thanh toán' : 'Cần thanh toán' }}
                        </p>

                        <p class="mt-2 text-3xl font-black text-emerald-900">
                            {{ $formatMoney($finalAmount) }}
                        </p>

                        <div class="mt-4">
                            <div class="mb-2 flex items-center justify-between text-xs font-black text-emerald-800">
                                <span>Tiến độ sử dụng</span>
                                <span>{{ $progressPercent }}%</span>
                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-emerald-100">
                                <div class="h-full rounded-full bg-emerald-600"
                                     style="width: {{ $progressPercent }}%"></div>
                            </div>

                            <p class="mt-2 text-xs font-bold text-emerald-800">
                                {{ $usedSessions }}/{{ $totalSessions }} buổi · còn {{ $remainingSessions }} buổi
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(! $isPaidPackage && $bookingPackage->status === 'pending_payment')
            <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-amber-900">
                            Gói đang chờ thanh toán
                        </h2>

                        <p class="mt-2 max-w-4xl text-sm font-semibold leading-6 text-amber-800">
                            Đây là lịch dự kiến của gói. Sau khi thanh toán được xác nhận, hệ thống mới sinh các booking con
                            và chuyển gói sang trạng thái hoạt động.
                        </p>

                        @if($transaction)
                            <p class="mt-3 text-sm text-amber-800">
                                Mã giao dịch:
                                <strong>{{ $transaction->transaction_code }}</strong>
                                ·
                                Trạng thái:
                                <strong>{{ $transactionStatus ?? '—' }}</strong>
                            </p>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-white px-5 py-4 lg:text-right">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                            Số tiền cần thanh toán
                        </p>

                        <p class="mt-1 text-2xl font-black text-zinc-900">
                            {{ $formatMoney($finalAmount) }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Giá gốc
                </p>

                <p class="mt-2 text-2xl font-black text-zinc-900">
                    {{ $formatMoney($totalAmount) }}
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Giảm giá
                </p>

                <p class="mt-2 text-2xl font-black text-emerald-600">
                    {{ $formatMoney($discountAmount) }}
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Thanh toán
                </p>

                <p class="mt-2 text-2xl font-black text-emerald-700">
                    {{ $formatMoney($finalAmount) }}
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Số buổi
                </p>

                <p class="mt-2 text-2xl font-black text-zinc-900">
                    {{ $usedSessions }}/{{ $totalSessions }}
                </p>

                <p class="mt-1 text-xs font-bold text-slate-500">
                    Còn lại {{ $remainingSessions }} buổi
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_360px]">
            <div class="space-y-5">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-zinc-900">
                                Thông tin gói
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Thông tin tổng quan về gói đặt sân đã đăng ký.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Loại gói
                            </p>

                            <p class="mt-1 font-black text-zinc-900">
                                {{ $packageTypeLabel }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Thời lượng
                            </p>

                            <p class="mt-1 font-black text-zinc-900">
                                {{ $durationLabel }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Số buổi mỗi tuần
                            </p>

                            <p class="mt-1 font-black text-zinc-900">
                                {{ $bookingPackage->weekly_sessions }} buổi/tuần
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Buổi sắp tới
                            </p>

                            @if($nextBooking)
                                <p class="mt-1 font-black text-zinc-900">
                                    {{ Carbon::parse($nextBooking->slot_date)->format('d/m/Y') }}
                                    ·
                                    {{ $formatTime($nextBooking->start_time) }}
                                </p>
                            @elseif($nextScheduleRow)
                                <p class="mt-1 font-black text-zinc-900">
                                    {{ $nextScheduleRow['date']->format('d/m/Y') }}
                                    ·
                                    {{ $formatTime($nextScheduleRow['start_time']) }}
                                    @if(! empty($nextScheduleRow['end_time']))
                                        - {{ $formatTime($nextScheduleRow['end_time']) }}
                                    @endif
                                </p>
                            @else
                                <p class="mt-1 font-black text-zinc-900">
                                    —
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-zinc-900">
                                Các buổi cố định trong tuần
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Lịch cố định mà bạn đã chọn khi đăng ký gói.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @forelse($bookingPackage->sessions as $session)
                            @php
                                $sessionSlotRows = method_exists($session, 'slots') && $session->slots->isNotEmpty()
                                    ? $session->slots->sortBy('slot_order')->values()
                                    : collect([(object) [
                                        'timeSlot' => $session->timeSlot,
                                        'price' => $session->price_per_session,
                                    ]]);

                                $firstSessionSlot = $sessionSlotRows->first()?->timeSlot;
                                $lastSessionSlot = $sessionSlotRows->last()?->timeSlot;

                                $sessionWeekday = (int) $session->weekday;
                                $sessionFirstDate = $startDate->dayOfWeek === $sessionWeekday
                                    ? $startDate->copy()
                                    : $startDate->copy()->next($sessionWeekday);
                            @endphp

                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-zinc-900">
                                            Buổi {{ $session->session_order }}
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-600">
                                            {{ $session->court?->name ?? '—' }}
                                        </p>

                                        <p class="mt-1 text-xs font-bold text-emerald-700">
                                            Bắt đầu: {{ $sessionFirstDate->format('d/m/Y') }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                        {{ $weekdayLabels[(int) $session->weekday] ?? '—' }}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($sessionSlotRows as $slotRow)
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                            {{ substr($slotRow->timeSlot?->start_time, 0, 5) }}
                                            -
                                            {{ substr($slotRow->timeSlot?->end_time, 0, 5) }}
                                        </span>
                                    @endforeach
                                </div>

                                <p class="mt-3 text-xs font-bold text-slate-500">
                                    Giá ghi nhận:
                                    <span class="text-emerald-700">
                                        {{ $formatMoney($session->price_per_session) }} / buổi
                                    </span>
                                </p>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-500">
                                Chưa có buổi cố định trong gói.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-zinc-900">
                        Thao tác nhanh
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Quản lý trạng thái gói của bạn.
                    </p>

                    <div class="mt-5 space-y-3">
                        @if($bookingPackage->status === 'pending_payment')
                            <form method="POST" action="{{ route('package-bookings.cancel', $bookingPackage) }}">
                                @csrf
                                <input type="hidden" name="mode" value="all">

                                <button type="submit"
                                        class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-black text-rose-700 transition hover:bg-rose-100"
                                        onclick="return confirm('Bạn chắc chắn muốn hủy yêu cầu đăng ký gói này?')">
                                    Hủy yêu cầu đăng ký
                                </button>
                            </form>
                        @elseif($bookingPackage->status === 'active')
                            <form method="POST" action="{{ route('package-bookings.pause', $bookingPackage) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="w-full rounded-2xl border border-sky-200 bg-sky-50 px-5 py-3 text-sm font-black text-sky-700 transition hover:bg-sky-100"
                                        onclick="return confirm('Bạn muốn tạm dừng gói này?')">
                                    Tạm dừng gói
                                </button>
                            </form>

                            <form method="POST" action="{{ route('package-bookings.cancel', $bookingPackage) }}">
                                @csrf
                                <input type="hidden" name="mode" value="future">

                                <button type="submit"
                                        class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-black text-rose-700 transition hover:bg-rose-100"
                                        onclick="return confirm('Bạn chắc chắn muốn hủy các buổi tương lai trong gói này?')">
                                    Hủy các buổi tương lai
                                </button>
                            </form>
                        @elseif($bookingPackage->status === 'paused')
                            <form method="POST" action="{{ route('package-bookings.resume', $bookingPackage) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                                    Kích hoạt lại gói
                                </button>
                            </form>

                            <form method="POST" action="{{ route('package-bookings.cancel', $bookingPackage) }}">
                                @csrf
                                <input type="hidden" name="mode" value="future">

                                <button type="submit"
                                        class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-black text-rose-700 transition hover:bg-rose-100"
                                        onclick="return confirm('Bạn chắc chắn muốn hủy gói này?')">
                                    Hủy gói
                                </button>
                            </form>
                        @else
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-500">
                                Gói ở trạng thái hiện tại không có thao tác khả dụng.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-zinc-900">
                        Thanh toán
                    </h2>

                    @if(! $isPaidPackage)
                        <p class="mt-1 text-sm text-slate-500">
                            Quét QR hoặc chuyển khoản theo thông tin bên dưới.
                        </p>

                        <div class="mt-5 rounded-3xl border border-amber-100 bg-amber-50 p-4">
                            @if($qrUrl)
                                <img src="{{ $qrUrl }}"
                                     alt="QR thanh toán gói"
                                     class="mx-auto h-52 w-52 rounded-2xl bg-white object-contain p-2 shadow-sm">
                            @else
                                <div class="grid h-52 place-items-center rounded-2xl border border-dashed border-amber-300 bg-white text-center text-sm font-bold text-amber-700">
                                    Chưa có thông tin ngân hàng
                                </div>
                            @endif

                            <div class="mt-4 text-center">
                                <p class="text-xs font-black uppercase tracking-wider text-amber-700">
                                    Số tiền
                                </p>

                                <p class="mt-1 text-2xl font-black text-zinc-900">
                                    {{ $formatMoney($finalAmount) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-slate-700">
                            <p>
                                <span class="font-black">Ngân hàng:</span>
                                {{ $bankName ?: 'Chưa cập nhật' }}
                            </p>

                            <p>
                                <span class="font-black">Số tài khoản:</span>
                                {{ $bankAccountNo ?: 'Chưa cập nhật' }}
                            </p>

                            <p>
                                <span class="font-black">Chủ tài khoản:</span>
                                {{ $bankAccountName ?: 'Chưa cập nhật' }}
                            </p>

                            <p>
                                <span class="font-black">Nội dung:</span>
                                THANH TOAN GOI PKG{{ $bookingPackage->id }}
                            </p>
                        </div>

                        @if($transaction)
                            <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm">
                                <p class="font-bold text-slate-500">
                                    Mã giao dịch
                                </p>

                                <p class="mt-1 font-black text-zinc-900">
                                    {{ $transaction->transaction_code }}
                                </p>

                                <p class="mt-2 font-bold text-slate-500">
                                    Trạng thái:
                                    <span class="text-amber-700">
                                        {{ $transactionStatus ?? '—' }}
                                    </span>
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="mt-4 rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                            <p class="text-sm font-black text-emerald-900">
                                Gói đã được thanh toán và xác nhận.
                            </p>

                            @if($transaction)
                                <div class="mt-3 space-y-2 text-sm text-emerald-800">
                                    <p>
                                        <span class="font-black">Mã giao dịch:</span>
                                        {{ $transaction->transaction_code }}
                                    </p>

                                    <p>
                                        <span class="font-black">Số tiền:</span>
                                        {{ $formatMoney($transaction->amount) }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-zinc-900">
                            {{ $bookingPackage->bookings->isEmpty() ? 'Lịch dự kiến trong gói' : 'Lịch đặt sân trong gói' }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $bookingPackage->bookings->isEmpty()
                                ? 'Các buổi này sẽ được tạo thành booking sau khi thanh toán thành công.'
                                : 'Đây là danh sách booking đã được sinh từ gói.' }}
                        </p>
                    </div>

                    <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        {{ $scheduleRows->count() }} buổi
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Buổi</th>
                            <th class="px-5 py-4">Ngày</th>
                            <th class="px-5 py-4">Thứ</th>
                            <th class="px-5 py-4">Sân</th>
                            <th class="px-5 py-4">Giờ</th>
                            <th class="px-5 py-4">Giá buổi</th>
                            <th class="px-5 py-4">Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($scheduleRows as $index => $row)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 font-black text-zinc-900">
                                    #{{ $index + 1 }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-black text-zinc-900">
                                    {{ $row['date']->format('d/m/Y') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-600">
                                    {{ $weekdayLabels[(int) $row['weekday']] ?? '—' }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $row['court_name'] }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-700">
                                    {{ substr((string) $row['start_time'], 0, 5) }}
                                    -
                                    {{ substr((string) $row['end_time'], 0, 5) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-black text-emerald-700">
                                    {{ $formatMoney($row['price']) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $row['status_class'] }}">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                                        </svg>
                                    </div>

                                    <p class="mt-3 text-sm font-bold text-slate-500">
                                        Chưa có lịch trong gói.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-zinc-900">
                Chính sách gói
            </h2>

            <div class="mt-4 grid gap-3 text-sm font-semibold text-slate-700 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 p-4">
                    ✓ Không thể đổi sân trong gói sau khi kích hoạt
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    ✓ Được đổi lịch 1 buổi nếu còn slot trống
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    ✓ Có thể tạm dừng gói theo quy định cơ sở
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    ✓ Không hoàn tiền sau khi gói đã kích hoạt
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    ✓ Có thể gia hạn sau khi hết gói
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    ✓ Lịch booking chỉ sinh sau khi thanh toán thành công
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
