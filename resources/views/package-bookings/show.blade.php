@extends('layouts.app')

@section('title', 'Chi tiết gói đặt sân | SportHub')

@section('content')
@php
    use Carbon\Carbon;

    $transaction = $bookingPackage->transactions->first();

    $paidTransactionStatuses = ['success', 'paid', 'completed'];
    $paidPackageStatuses = ['active', 'paused', 'completed'];

    $isPaidTransaction = $transaction && in_array($transaction->payment_status, $paidTransactionStatuses, true);
    $isPaidPackage = in_array($bookingPackage->status, $paidPackageStatuses, true)
        || filled($bookingPackage->paid_at)
        || $isPaidTransaction;

    $owner = $bookingPackage->venue?->owner;
    $legalDoc = $bookingPackage->venue?->legalDocument;

    $bankName = $owner?->bank_name ?? $legalDoc?->bank_name;
    $bankAccountNo = $owner?->bank_account_no ?? $legalDoc?->bank_account_number;
    $bankAccountName = $owner?->bank_account_name ?? $legalDoc?->bank_account_holder ?? 'CHU SAN';
    $hasBankInfo = $bankName && $bankAccountNo;

    $amount = (float) ($bookingPackage->final_amount ?? 0);
    $qrUrl = null;

    if (! $isPaidPackage && $hasBankInfo && $amount > 0) {
        $addInfo = 'THANH TOAN GOI PKG' . $bookingPackage->id;

        $qrUrl = 'https://img.vietqr.io/image/'
            . trim($bankName)
            . '-'
            . trim($bankAccountNo)
            . '-compact2.png?amount='
            . (int) $amount
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
        'pending_payment' => 'bg-amber-100 text-amber-700',
        'active' => 'bg-emerald-100 text-emerald-700',
        'paused' => 'bg-sky-100 text-sky-700',
        'completed' => 'bg-indigo-100 text-indigo-700',
        'cancelled' => 'bg-rose-100 text-rose-700',
        'expired' => 'bg-stone-100 text-stone-600',
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
        'pending' => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-emerald-100 text-emerald-700',
        'completed' => 'bg-indigo-100 text-indigo-700',
        'cancelled' => 'bg-rose-100 text-rose-700',
    ];

    $startDate = Carbon::parse($bookingPackage->start_date)->startOfDay();
    $endDate = Carbon::parse($bookingPackage->end_date)->startOfDay();

    $previewRows = collect();

    if ($bookingPackage->bookings->isEmpty()) {
        foreach ($bookingPackage->sessions as $session) {
            $weekday = (int) $session->weekday;
            $sessionSlotRows = $session->slots->isNotEmpty()
                ? $session->slots
                : collect([(object) ['timeSlot' => $session->timeSlot, 'price' => $session->price_per_session]]);
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
                    'status_class' => 'bg-amber-100 text-amber-700',
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
            'status_class' => $bookingStatusClasses[$booking->status] ?? 'bg-stone-100 text-stone-600',
        ];
    })->sortBy('date')->values();

    $scheduleRows = $actualRows->isNotEmpty() ? $actualRows : $previewRows;

    $nextBooking = $bookingPackage->bookings
        ->filter(function ($booking) {
            return ! in_array($booking->status, ['completed', 'cancelled'], true)
                && Carbon::parse($booking->slot_date . ' ' . $booking->start_time)->gte(now());
        })
        ->sortBy(function ($booking) {
            return $booking->slot_date . ' ' . $booking->start_time;
        })
        ->first();

    $usedSessions = (int) ($bookingPackage->used_sessions ?? 0);
    $totalSessions = (int) ($bookingPackage->total_sessions ?: $scheduleRows->count());
    $remainingSessions = max(0, $totalSessions - $usedSessions);
@endphp

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                Gói đặt sân #{{ $bookingPackage->id }}
            </p>

            <h1 class="mt-1 text-3xl font-extrabold text-zinc-900">
                {{ $bookingPackage->package?->name ?? 'Gói đặt sân' }}
            </h1>

            <p class="mt-2 text-sm text-stone-500">
                {{ $bookingPackage->venue?->name }}
                ·
                {{ $bookingPackage->start_date->format('d/m/Y') }}
                -
                {{ $bookingPackage->end_date->format('d/m/Y') }}
            </p>
        </div>

        <span class="inline-flex rounded-full px-4 py-2 text-sm font-bold {{ $statusClasses[$bookingPackage->status] ?? 'bg-stone-100 text-stone-600' }}">
            {{ $statusLabels[$bookingPackage->status] ?? $bookingPackage->status }}
        </span>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    @if($bookingPackage->status === 'pending_payment')
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold text-amber-900">
                        Gói đang chờ thanh toán
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-amber-800">
                        Hệ thống đã ghi nhận yêu cầu đăng ký gói. Các lịch bên dưới hiện là lịch dự kiến.
                        Sau khi thanh toán thành công, hệ thống mới sinh toàn bộ booking con và chuyển gói sang trạng thái hoạt động.
                    </p>

                    @if($transaction)
                        <p class="mt-3 text-sm text-amber-800">
                            Mã giao dịch:
                            <strong>{{ $transaction->transaction_code }}</strong>
                            ·
                            Trạng thái:
                            <strong>{{ $transaction->payment_status }}</strong>
                        </p>
                    @endif
                </div>

                <div class="shrink-0 rounded-xl bg-white px-5 py-4 text-right">
                    <p class="text-xs font-bold uppercase text-stone-400">
                        Cần thanh toán
                    </p>
                    <p class="mt-1 text-2xl font-black text-zinc-900">
                        {{ number_format((float) $bookingPackage->final_amount, 0, ',', '.') }}đ
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-4">
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase text-stone-400">Giá gốc</p>
            <p class="mt-2 text-2xl font-extrabold text-zinc-900">
                {{ number_format((float) $bookingPackage->total_amount, 0, ',', '.') }}đ
            </p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase text-stone-400">Đã giảm</p>
            <p class="mt-2 text-2xl font-extrabold text-emerald-600">
                {{ number_format((float) $bookingPackage->discount_amount, 0, ',', '.') }}đ
            </p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase text-stone-400">Thanh toán</p>
            <p class="mt-2 text-2xl font-extrabold text-emerald-700">
                {{ number_format((float) $bookingPackage->final_amount, 0, ',', '.') }}đ
            </p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase text-stone-400">Tiến độ</p>
            <p class="mt-2 text-2xl font-extrabold text-zinc-900">
                {{ $usedSessions }}/{{ $totalSessions }}
            </p>
            <p class="mt-1 text-xs font-semibold text-stone-500">
                Còn lại {{ $remainingSessions }} buổi
            </p>
        </div>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-extrabold text-zinc-900">
                Thông tin gói
            </h2>

            <div class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                <div class="rounded-xl bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase text-stone-400">Loại gói</p>
                    <p class="mt-1 font-extrabold text-zinc-900">
                        {{ $bookingPackage->package?->type === 'week' ? 'Theo tuần' : 'Theo tháng' }}
                    </p>
                </div>

                <div class="rounded-xl bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase text-stone-400">Thời lượng</p>
                    <p class="mt-1 font-extrabold text-zinc-900">
                        {{ $bookingPackage->package?->duration }}
                        {{ $bookingPackage->package?->type === 'week' ? 'tuần' : 'tháng' }}
                    </p>
                </div>

                <div class="rounded-xl bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase text-stone-400">Số buổi/tuần</p>
                    <p class="mt-1 font-extrabold text-zinc-900">
                        {{ $bookingPackage->weekly_sessions }} buổi/tuần
                    </p>
                </div>

                <div class="rounded-xl bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase text-stone-400">Lần đặt tiếp theo</p>

                    @if($nextBooking)
                        <p class="mt-1 font-extrabold text-zinc-900">
                            {{ Carbon::parse($nextBooking->slot_date)->format('d/m/Y') }}
                            ·
                            {{ substr($nextBooking->start_time, 0, 5) }}
                        </p>
                    @else
                        <p class="mt-1 font-extrabold text-zinc-900">—</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-extrabold text-zinc-900">
                Thao tác
            </h2>

            <div class="mt-4 space-y-3">
                @if($bookingPackage->status === 'pending_payment')
                    <form method="POST" action="{{ route('package-bookings.cancel', $bookingPackage) }}">
                        @csrf
                        <input type="hidden" name="mode" value="all">

                        <button type="submit"
                                class="w-full rounded-xl border border-rose-200 px-5 py-3 text-sm font-extrabold text-rose-700 hover:bg-rose-50"
                                onclick="return confirm('Bạn chắc chắn muốn hủy yêu cầu đăng ký gói này?')">
                            Hủy yêu cầu
                        </button>
                    </form>
                @elseif($bookingPackage->status === 'active')
                    <form method="POST" action="{{ route('package-bookings.pause', $bookingPackage) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="w-full rounded-xl border border-sky-200 px-5 py-3 text-sm font-extrabold text-sky-700 hover:bg-sky-50"
                                onclick="return confirm('Bạn muốn tạm dừng gói này?')">
                            Tạm dừng gói
                        </button>
                    </form>

                    <form method="POST" action="{{ route('package-bookings.cancel', $bookingPackage) }}">
                        @csrf
                        <input type="hidden" name="mode" value="future">

                        <button type="submit"
                                class="w-full rounded-xl border border-rose-200 px-5 py-3 text-sm font-extrabold text-rose-700 hover:bg-rose-50"
                                onclick="return confirm('Bạn chắc chắn muốn hủy các buổi tương lai trong gói này?')">
                            Hủy gói
                        </button>
                    </form>
                @elseif($bookingPackage->status === 'paused')
                    <form method="POST" action="{{ route('package-bookings.resume', $bookingPackage) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white hover:bg-emerald-700">
                            Kích hoạt lại gói
                        </button>
                    </form>

                    <form method="POST" action="{{ route('package-bookings.cancel', $bookingPackage) }}">
                        @csrf
                        <input type="hidden" name="mode" value="future">

                        <button type="submit"
                                class="w-full rounded-xl border border-rose-200 px-5 py-3 text-sm font-extrabold text-rose-700 hover:bg-rose-50"
                                onclick="return confirm('Bạn chắc chắn muốn hủy gói này?')">
                            Hủy gói
                        </button>
                    </form>
                @else
                    <div class="rounded-xl bg-stone-50 p-4 text-sm font-semibold text-stone-500">
                        Gói ở trạng thái hiện tại không có thao tác khả dụng.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(! $isPaidPackage)
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <h2 class="text-lg font-extrabold text-amber-900">
                Thanh toán gói
            </h2>

            @if($transaction)
                <p class="mt-2 text-sm text-amber-800">
                    Mã giao dịch:
                    <strong>{{ $transaction->transaction_code }}</strong>
                    ·
                    Số tiền:
                    <strong>{{ number_format((float) $transaction->amount, 0, ',', '.') }}đ</strong>
                    ·
                    Trạng thái:
                    <strong>{{ $transaction->payment_status }}</strong>
                </p>
            @else
                <p class="mt-2 text-sm text-amber-800">
                    Chưa có giao dịch cho gói này.
                </p>
            @endif

            <div class="mt-5 grid gap-5 rounded-2xl border border-amber-200 bg-white p-5 md:grid-cols-[220px_1fr]">
                <div class="rounded-2xl bg-stone-50 p-4 text-center">
                    @if($qrUrl)
                        <img src="{{ $qrUrl }}"
                             alt="QR thanh toán gói"
                             class="mx-auto h-48 w-48 rounded-xl object-contain">
                    @else
                        <div class="grid h-48 w-full place-items-center rounded-xl border border-dashed border-stone-300 text-sm font-semibold text-stone-400">
                            Chưa có thông tin ngân hàng
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-sm font-extrabold uppercase tracking-wider text-amber-700">
                        Quét QR để thanh toán gói
                    </p>

                    <p class="mt-2 text-3xl font-black text-zinc-900">
                        {{ number_format((float) $bookingPackage->final_amount, 0, ',', '.') }}đ
                    </p>

                    <div class="mt-4 grid gap-2 text-sm text-zinc-700">
                        <p><span class="font-bold">Ngân hàng:</span> {{ $bankName ?: 'Chưa cập nhật' }}</p>
                        <p><span class="font-bold">Số tài khoản:</span> {{ $bankAccountNo ?: 'Chưa cập nhật' }}</p>
                        <p><span class="font-bold">Chủ tài khoản:</span> {{ $bankAccountName ?: 'Chưa cập nhật' }}</p>
                        <p><span class="font-bold">Nội dung:</span> THANH TOAN GOI PKG{{ $bookingPackage->id }}</p>
                    </div>

                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                        Sau khi thanh toán thành công, chủ sân hoặc hệ thống thanh toán sẽ xác nhận giao dịch.
                        Khi đó toàn bộ booking trong gói mới được sinh ra.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <h2 class="text-lg font-extrabold text-emerald-900">
                Thanh toán gói
            </h2>

            <p class="mt-2 text-sm text-emerald-800">
                Gói đã được thanh toán và xác nhận.
                @if($transaction)
                    Mã giao dịch:
                    <strong>{{ $transaction->transaction_code }}</strong>
                    ·
                    Số tiền:
                    <strong>{{ number_format((float) $transaction->amount, 0, ',', '.') }}đ</strong>
                @endif
            </p>
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-extrabold text-zinc-900">
            Các buổi cố định trong tuần
        </h2>

        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @forelse($bookingPackage->sessions as $session)
                @php
                    $sessionSlotRows = $session->slots->isNotEmpty()
                        ? $session->slots
                        : collect([(object) ['timeSlot' => $session->timeSlot, 'price' => $session->price_per_session]]);
                    $firstSessionSlot = $sessionSlotRows->first()?->timeSlot;
                    $lastSessionSlot = $sessionSlotRows->last()?->timeSlot;
                @endphp
                <div class="rounded-xl bg-stone-50 p-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-zinc-900">
                            Buổi {{ $session->session_order }}
                        </p>

                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-500">
                            {{ $weekdayLabels[(int) $session->weekday] ?? '—' }}
                        </span>
                    </div>

                    <p class="mt-2 text-stone-600">
                        {{ $session->court?->name ?? '—' }}
                        ·
                        {{ substr($firstSessionSlot?->start_time, 0, 5) }}
                        -
                        {{ substr($lastSessionSlot?->end_time, 0, 5) }}
                    </p>

                    <p class="mt-2 text-xs font-semibold text-stone-500">
                        Giá ghi nhận:
                        {{ number_format((float) $session->price_per_session, 0, ',', '.') }}đ / buổi
                    </p>
                </div>
            @empty
                <div class="rounded-xl bg-stone-50 p-4 text-sm font-semibold text-stone-500">
                    Chưa có buổi cố định trong gói.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-100 px-5 py-4">
            <h2 class="text-lg font-extrabold text-zinc-900">
                {{ $bookingPackage->bookings->isEmpty() ? 'Lịch dự kiến trong gói' : 'Lịch đặt sân trong gói' }}
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                {{ $bookingPackage->bookings->isEmpty()
                    ? 'Các buổi này sẽ được tạo thành booking sau khi thanh toán thành công.'
                    : 'Đây là danh sách booking đã được sinh từ gói.' }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-bold uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Buổi</th>
                        <th class="px-5 py-3">Ngày</th>
                        <th class="px-5 py-3">Thứ</th>
                        <th class="px-5 py-3">Sân</th>
                        <th class="px-5 py-3">Giờ</th>
                        <th class="px-5 py-3">Giá buổi</th>
                        <th class="px-5 py-3">Trạng thái</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($scheduleRows as $index => $row)
                        <tr>
                            <td class="px-5 py-4 font-bold">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-5 py-4 font-bold">
                                {{ $row['date']->format('d/m/Y') }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $weekdayLabels[(int) $row['weekday']] ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $row['court_name'] }}
                            </td>

                            <td class="px-5 py-4">
                                {{ substr($row['start_time'], 0, 5) }}
                                -
                                {{ substr($row['end_time'], 0, 5) }}
                            </td>

                            <td class="px-5 py-4">
                                {{ number_format((float) $row['price'], 0, ',', '.') }}đ
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $row['status_class'] }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-stone-500">
                                Chưa có lịch trong gói.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-extrabold text-zinc-900">
            Chính sách gói
        </h2>

        <div class="mt-4 grid gap-3 text-sm text-stone-700 md:grid-cols-2">
            <p>✓ Không thể đổi sân trong gói sau khi kích hoạt</p>
            <p>✓ Được đổi lịch 1 buổi nếu còn slot trống</p>
            <p>✓ Có thể tạm dừng gói theo quy định cơ sở</p>
            <p>✓ Không hoàn tiền sau khi gói đã kích hoạt</p>
            <p>✓ Có thể gia hạn sau khi hết gói</p>
            <p>✓ Lịch booking chỉ sinh sau khi thanh toán thành công</p>
        </div>
    </div>
</div>
@endsection
