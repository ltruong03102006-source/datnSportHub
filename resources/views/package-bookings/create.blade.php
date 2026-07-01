@extends('layouts.app')

@section('title', 'Đặt sân theo gói | SportHub')

@section('content')
@php
    $packageBookingEnabled = data_get($venue, 'package_booking_enabled', true);

    $activePackages = $venue->packages
        ->filter(function ($package) {
            $isActive = data_get($package, 'is_active', true);
            $status = data_get($package, 'status', 'active');

            return $isActive && $status === 'active';
        })
        ->values();

    $defaultPackageId = old('package_id', optional($activePackages->first())->id);

    $defaultCourtId = old(
        'court_id',
        old('sessions.0.court_id', optional($venue->courts->first())->id)
    );

    $oldSessions = old('sessions', [
        [
            'weekday' => '1',
            'court_id' => $defaultCourtId,
            'time_slot_ids' => [],
        ]
    ]);

    $weeklySessions = old('weekly_sessions', count($oldSessions));
$weeklySessions = max(1, min(7, (int) $weeklySessions));

$weekdayOrder = ['1', '2', '3', '4', '5', '6', '0'];

    $packagesData = $activePackages->map(function ($package) {
        return [
            'id' => (string) $package->id,
            'name' => $package->name,
            'type' => $package->type,
            'duration' => (int) $package->duration,
            'discount_percent' => (float) $package->discount_percent,
        ];
    })->values();

    $courtsData = $venue->courts->map(function ($court) {
    return [
        'id' => (string) $court->id,
        'name' => $court->name,
        'time_slots' => $court->timeSlots->map(function ($slot) {
            $start = \Carbon\Carbon::parse($slot->start_time);
            $end = \Carbon\Carbon::parse($slot->end_time);

            $hours = max(0.5, $start->floatDiffInHours($end));
            $fallbackPrice = round($hours * 150000);

            return [
                'id' => (string) $slot->id,
                'label' => substr($slot->start_time, 0, 5) . ' - ' . substr($slot->end_time, 0, 5),
                'start_time' => substr($slot->start_time, 0, 5),
                'end_time' => substr($slot->end_time, 0, 5),

                'default_price' => (float) $fallbackPrice,

                'prices_by_weekday' => $slot->prices
                    ->mapWithKeys(function ($price) {
                        return [
                            (string) $price->day_of_week => (float) $price->price,
                        ];
                    })
                    ->all(),
            ];
        })->values(),
    ];
})->values();

    $weekdays = [
        '1' => 'Thứ 2',
        '2' => 'Thứ 3',
        '3' => 'Thứ 4',
        '4' => 'Thứ 5',
        '5' => 'Thứ 6',
        '6' => 'Thứ 7',
        '0' => 'Chủ nhật',
    ];
@endphp

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('venues.show', $venue->id) }}"
       class="mb-5 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-800">
        ← Quay lại cơ sở
    </a>

    <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-100 bg-gradient-to-r from-emerald-50 to-white p-6">
            <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                Đăng ký gói sân cố định
            </p>

            <h1 class="mt-1 text-3xl font-extrabold text-zinc-900">
                {{ $venue->name }}
            </h1>

            <p class="mt-2 max-w-3xl text-sm text-stone-600">
                Chọn gói, sân, số buổi mỗi tuần và ngày bắt đầu. Hệ thống sẽ preview lịch, tính tổng tiền và tạo yêu cầu thanh toán.
            </p>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                    Vui lòng kiểm tra lại thông tin đặt gói.
                </div>
            @endif

            @if(!$packageBookingEnabled)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
                    Cơ sở này hiện đã tạm tắt chức năng đặt sân theo gói.
                </div>
            @elseif($activePackages->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
                    Cơ sở này hiện chưa có gói ưu đãi khả dụng.
                </div>
            @elseif($venue->courts->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
                    Cơ sở này hiện chưa có sân con để đặt.
                </div>
            @else
                <form id="package-booking-form"
                      method="POST"
                      action="{{ route('package-bookings.store') }}"
                      class="grid gap-6 lg:grid-cols-3">
                    @csrf

                    <input type="hidden" name="venue_id" value="{{ $venue->id }}">
                    <input type="hidden" name="package_id" id="package_id" value="{{ $defaultPackageId }}">
                    <input type="hidden" name="court_id" id="court_id" value="{{ $defaultCourtId }}">

                    <div class="space-y-6 lg:col-span-2">
                        <section class="rounded-2xl border border-stone-200 bg-white p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    1
                                </div>

                                <div>
                                    <h2 class="text-lg font-extrabold text-zinc-900">
                                        Chọn gói
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Khách nhìn card sẽ dễ hiểu hơn chọn trong danh sách.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach($activePackages as $package)
                                    @php
                                        $durationLabel = $package->type === 'week'
                                            ? $package->duration . ' tuần'
                                            : $package->duration . ' tháng';

                                        $discount = rtrim(rtrim(number_format($package->discount_percent, 2), '0'), '.');
                                    @endphp

                                    <button type="button"
                                            class="package-card rounded-2xl border p-5 text-left transition hover:border-emerald-400 hover:bg-emerald-50"
                                            data-package-id="{{ $package->id }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-lg font-extrabold text-zinc-900">
                                                    ⭐ {{ $package->name }}
                                                </p>

                                                <p class="mt-1 text-sm font-semibold text-stone-500">
                                                    Thời hạn {{ $durationLabel }}
                                                </p>
                                            </div>

                                            <span class="package-check hidden rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">
                                                Đã chọn
                                            </span>
                                        </div>

                                        <div class="mt-4 space-y-2 text-sm text-stone-700">
                                            <p>✓ Lịch cố định theo tuần</p>
                                            <p>✓ Giảm {{ $discount }}%</p>
                                            <p>✓ Giá sau giảm tính tự động theo số buổi</p>
                                        </div>

                                        <div class="mt-4 rounded-xl bg-stone-50 px-3 py-2 text-xs font-semibold text-stone-500">
                                            Phù hợp khách chơi đều đặn, muốn giữ sân cố định.
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            @error('package_id')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <section class="rounded-2xl border border-stone-200 bg-white p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    2
                                </div>

                                <div>
                                    <h2 class="text-lg font-extrabold text-zinc-900">
                                        Chọn sân
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Sau khi chọn sân, hệ thống chỉ hiện khung giờ thuộc sân đó.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                @foreach($venue->courts as $court)
                                    <button type="button"
                                            class="court-card rounded-2xl border border-stone-200 p-4 text-left transition hover:border-emerald-400 hover:bg-emerald-50"
                                            data-court-id="{{ $court->id }}">
                                        <p class="font-extrabold text-zinc-900">
                                            {{ $court->name }}
                                        </p>

                                        <p class="mt-1 text-xs text-stone-500">
                                            {{ $court->timeSlots->count() }} khung giờ khả dụng
                                        </p>
                                    </button>
                                @endforeach
                            </div>

                            @error('court_id')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <section class="rounded-2xl border border-stone-200 bg-white p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    3
                                </div>

                                <div>
                                    <h2 class="text-lg font-extrabold text-zinc-900">
                                        Chọn số buổi mỗi tuần
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Chọn số buổi trước, hệ thống sẽ tự sinh form tương ứng. Nếu chọn 7 buổi/tuần, hệ thống hiểu là chơi mỗi ngày.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                @for($i = 1; $i <= 7; $i++)
    <label class="weekly-option flex cursor-pointer items-center gap-3 rounded-2xl border border-stone-200 p-4 hover:border-emerald-400 hover:bg-emerald-50">
        <input type="radio"
               name="weekly_sessions"
               value="{{ $i }}"
               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500"
               @checked($weeklySessions === $i)>

        <span class="text-sm font-extrabold text-zinc-800">
            {{ $i }} buổi / tuần
            @if($i === 7)
                <span class="block text-xs font-semibold text-emerald-600">
                    Chơi mỗi ngày
                </span>
            @endif
        </span>
    </label>
@endfor
                            </div>

                            <div id="sessions-wrapper" class="mt-5 grid gap-4"></div>

                            <p id="duplicate-warning" class="mt-2 hidden text-xs font-semibold text-rose-600"></p>

                            @error('sessions')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <section class="rounded-2xl border border-stone-200 bg-white p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    4
                                </div>

                                <div>
                                    <h2 class="text-lg font-extrabold text-zinc-900">
                                        Chọn ngày bắt đầu
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Lịch dự kiến sẽ được cập nhật ngay bên dưới.
                                    </p>
                                </div>
                            </div>

                            <input type="date"
                                   name="start_date"
                                   id="start_date"
                                   value="{{ old('start_date', now()->toDateString()) }}"
                                   min="{{ now()->toDateString() }}"
                                   class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                   required>

                            @error('start_date')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-5 rounded-2xl bg-stone-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-extrabold text-zinc-900">
                                        Lịch dự kiến
                                    </p>

                                    <p id="schedule-count" class="text-xs font-bold text-stone-500"></p>
                                </div>

                                <div id="schedule-preview" class="mt-3 grid max-h-72 gap-2 overflow-y-auto pr-1"></div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-stone-200 bg-white p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    5
                                </div>

                                <div>
                                    <h2 class="text-lg font-extrabold text-zinc-900">
                                        Chính sách gói
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Nên hiển thị rõ để tránh tranh chấp sau khi khách mua gói.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 text-sm text-stone-700 md:grid-cols-2">
                                <p>✓ Không thể đổi sân trong gói</p>
                                <p>✓ Được đổi lịch 1 buổi nếu còn slot trống</p>
                                <p>✓ Có thể tạm dừng gói theo quy định cơ sở</p>
                                <p>✓ Không hoàn tiền sau khi kích hoạt</p>
                                <p>✓ Có thể gia hạn sau khi hết gói</p>
                                <p>✓ Lịch chỉ được tạo sau khi thanh toán thành công</p>
                            </div>
                        </section>
                    </div>

                    <aside class="lg:col-span-1">
                        <div class="sticky top-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                            <p class="text-lg font-extrabold text-zinc-900">
                                Tổng tiền
                            </p>

                            <div class="mt-4 space-y-3 text-sm">
                                <div class="flex justify-between gap-3">
                                    <span class="text-stone-500">Gói</span>
                                    <span id="summary-package" class="text-right font-bold text-zinc-800">—</span>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-stone-500">Sân</span>
                                    <span id="summary-court" class="text-right font-bold text-zinc-800">—</span>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-stone-500">Số buổi dự kiến</span>
                                    <span id="summary-total-sessions" class="text-right font-bold text-zinc-800">0</span>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-stone-500">Giá gốc</span>
                                    <span id="summary-original" class="text-right font-bold text-zinc-800">0đ</span>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-stone-500">Giảm giá</span>
                                    <span id="summary-discount" class="text-right font-bold text-rose-600">0đ</span>
                                </div>

                                <div class="border-t border-stone-200 pt-3">
                                    <div class="flex justify-between gap-3">
                                        <span class="font-extrabold text-zinc-900">Thanh toán</span>
                                        <span id="summary-final" class="text-right text-xl font-extrabold text-emerald-700">0đ</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 rounded-xl bg-emerald-50 p-4 text-xs font-semibold leading-6 text-emerald-800">
                                Sau khi bấm đăng ký, hệ thống nên tạo PackageBooking ở trạng thái pending_payment.
                                Khi thanh toán thành công mới sinh toàn bộ Booking.
                            </div>

                            <button type="submit"
                                    id="submit-button"
                                    class="mt-5 w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white hover:bg-emerald-700">
                                Đăng ký gói
                            </button>
                        </div>
                    </aside>
                </form>
            @endif
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                    Xác nhận đăng ký
                </p>

                <h3 class="mt-1 text-2xl font-extrabold text-zinc-900">
                    Bạn xác nhận mua gói này?
                </h3>
            </div>

            <button type="button"
                    id="close-confirm-modal"
                    class="rounded-full bg-stone-100 px-3 py-1 text-lg font-bold text-stone-600 hover:bg-stone-200">
                ×
            </button>
        </div>

        <div class="mt-5 space-y-3 rounded-2xl bg-stone-50 p-4 text-sm">
            <div class="flex justify-between gap-3">
                <span class="text-stone-500">Gói</span>
                <span id="modal-package" class="text-right font-bold text-zinc-900">—</span>
            </div>

            <div class="flex justify-between gap-3">
                <span class="text-stone-500">Sân</span>
                <span id="modal-court" class="text-right font-bold text-zinc-900">—</span>
            </div>

            <div class="flex justify-between gap-3">
                <span class="text-stone-500">Lịch chơi</span>
                <span id="modal-sessions" class="text-right font-bold text-zinc-900">—</span>
            </div>

            <div class="flex justify-between gap-3">
                <span class="text-stone-500">Tổng số buổi</span>
                <span id="modal-total-sessions" class="text-right font-bold text-zinc-900">—</span>
            </div>

            <div class="border-t border-stone-200 pt-3">
                <div class="flex justify-between gap-3">
                    <span class="font-extrabold text-zinc-900">Thanh toán</span>
                    <span id="modal-final" class="text-right text-xl font-extrabold text-emerald-700">—</span>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <button type="button"
                    id="cancel-confirm"
                    class="rounded-xl border border-stone-300 px-5 py-3 text-sm font-extrabold text-stone-700 hover:bg-stone-50">
                Kiểm tra lại
            </button>

            <button type="button"
                    id="confirm-submit"
                    class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white hover:bg-emerald-700">
                Xác nhận
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const packages = @json($packagesData);
const courts = @json($courtsData);
const weekdays = @json($weekdays);
const weekdayOrder = @json($weekdayOrder);

        const oldState = {
            packageId: @json((string) $defaultPackageId),
            courtId: @json((string) $defaultCourtId),
            weeklySessions: @json((int) $weeklySessions),
            startDate: @json(old('start_date', now()->toDateString())),
            sessions: @json($oldSessions),
        };

        const state = {
            packageId: oldState.packageId,
            courtId: oldState.courtId,
            weeklySessions: oldState.weeklySessions,
            sessions: [],
            schedule: [],
            originalAmount: 0,
            discountAmount: 0,
            finalAmount: 0,
            submitting: false,
        };

        const packageInput = document.getElementById('package_id');
        const courtInput = document.getElementById('court_id');
        const startDateInput = document.getElementById('start_date');
        const sessionsWrapper = document.getElementById('sessions-wrapper');
        const schedulePreview = document.getElementById('schedule-preview');
        const scheduleCount = document.getElementById('schedule-count');
        const duplicateWarning = document.getElementById('duplicate-warning');
        const form = document.getElementById('package-booking-form');

        const confirmModal = document.getElementById('confirm-modal');

        function money(amount) {
            amount = Number(amount || 0);

            return amount.toLocaleString('vi-VN') + 'đ';
        }

        function getSelectedPackage() {
            return packages.find(item => item.id === String(state.packageId)) || null;
        }

        function getSelectedCourt() {
            return courts.find(item => item.id === String(state.courtId)) || null;
        }

        function getSelectedSlot(slotId) {
            const court = getSelectedCourt();

            if (!court) {
                return null;
            }

            return court.time_slots.find(slot => slot.id === String(slotId)) || null;
        }

        function getDurationText(pkg) {
            if (!pkg) {
                return '—';
            }

            return pkg.type === 'week'
                ? `${pkg.duration} tuần`
                : `${pkg.duration} tháng`;
        }

        function formatDate(date) {
            return date.toLocaleDateString('vi-VN');
        }

        function getDateFromInput(value) {
            if (!value) {
                return null;
            }

            const [year, month, day] = value.split('-').map(Number);

            return new Date(year, month - 1, day);
        }

        function addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);

            return result;
        }

        function addMonths(date, months) {
    const originalDay = date.getDate();

    const result = new Date(date);
    result.setDate(1);
    result.setMonth(result.getMonth() + months);

    const lastDayOfTargetMonth = new Date(
        result.getFullYear(),
        result.getMonth() + 1,
        0
    ).getDate();

    result.setDate(Math.min(originalDay, lastDayOfTargetMonth));

    return result;
}

        function getFirstDateByWeekday(startDate, weekday) {
            const target = Number(weekday);
            const current = startDate.getDay();
            const diff = (target - current + 7) % 7;

            return addDays(startDate, diff);
        }

        function setActiveCards() {
            document.querySelectorAll('.package-card').forEach(card => {
                const isActive = card.dataset.packageId === String(state.packageId);

                card.classList.toggle('border-emerald-500', isActive);
                card.classList.toggle('bg-emerald-50', isActive);
                card.classList.toggle('ring-2', isActive);
                card.classList.toggle('ring-emerald-100', isActive);
                card.classList.toggle('border-stone-200', !isActive);

                const check = card.querySelector('.package-check');
                if (check) {
                    check.classList.toggle('hidden', !isActive);
                }
            });

            document.querySelectorAll('.court-card').forEach(card => {
                const isActive = card.dataset.courtId === String(state.courtId);

                card.classList.toggle('border-emerald-500', isActive);
                card.classList.toggle('bg-emerald-50', isActive);
                card.classList.toggle('ring-2', isActive);
                card.classList.toggle('ring-emerald-100', isActive);
                card.classList.toggle('border-stone-200', !isActive);
            });
        }

        function buildSlotCards(index, selectedSlotIds = [], weekday = '1') {
            const court = getSelectedCourt();
            selectedSlotIds = Array.isArray(selectedSlotIds) ? selectedSlotIds.map(String) : [String(selectedSlotIds || '')];

            if (!court || court.time_slots.length === 0) {
                return `
                    <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-4 text-sm font-semibold text-stone-500">
                        Sân này chưa có khung giờ
                    </div>
                `;
            }

            return court.time_slots.map(slot => {
                const selected = selectedSlotIds.includes(String(slot.id));
                const price = getSlotPrice(slot, weekday);
                const priceText = price > 0 ? money(price) : 'Chưa có giá';

                return `
                    <label class="slot-card flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-3 transition hover:-translate-y-0.5 hover:border-emerald-400 hover:bg-emerald-50 hover:shadow-sm ${selected ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-100' : 'border-stone-200 bg-white'}">
                        <input type="checkbox"
                               name="sessions[${index}][time_slot_ids][]"
                               value="${slot.id}"
                               class="session-slot peer sr-only"
                               ${selected ? 'checked' : ''}>

                        <span class="min-w-0">
                            <span class="block text-sm font-extrabold text-zinc-900">${slot.label}</span>
                            <span class="mt-0.5 block text-xs font-semibold text-stone-500">Giá theo ${weekdays[String(weekday)] || 'ngày chọn'}</span>
                        </span>

                        <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                            ${priceText}
                        </span>
                    </label>
                `;
            }).join('');
        }

        function normalizeSessions() {
    const oldSessions = Array.isArray(oldState.sessions) ? oldState.sessions : [];

    state.sessions = Array.from({ length: state.weeklySessions }).map((_, index) => {
        const oldSession = oldSessions[index] || {};
        const defaultWeekday = weekdayOrder[index] ?? weekdayOrder[0];

        return {
            weekday: String(oldSession.weekday ?? defaultWeekday),
            court_id: String(state.courtId),
            time_slot_ids: Array.isArray(oldSession.time_slot_ids)
                ? oldSession.time_slot_ids.map(String)
                : (oldSession.time_slot_id ? [String(oldSession.time_slot_id)] : []),
        };
    });
}

        function renderSessions() {
            normalizeSessions();

            sessionsWrapper.innerHTML = state.sessions.map((session, index) => {
                const weekdayOptions = Object.entries(weekdays).map(([value, label]) => {
                    const selected = String(session.weekday) === String(value) ? 'selected' : '';

                    return `<option value="${value}" ${selected}>${label}</option>`;
                }).join('');

                return `
                    <div class="session-item rounded-2xl border border-stone-200 bg-stone-50 p-4" data-index="${index}">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="font-extrabold text-zinc-900">
                                Buổi ${index + 1}
                            </p>

                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-500">
                                Cố định hằng tuần
                            </span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-zinc-700">
                                    Thứ
                                </label>

                                <select name="sessions[${index}][weekday]"
                                        class="session-weekday w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                        required>
                                    ${weekdayOptions}
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-zinc-700">
                                    Khung giờ
                                </label>

                                <input type="hidden" name="sessions[${index}][court_id]" value="${state.courtId}">

                                <div class="grid max-h-72 gap-2 overflow-y-auto rounded-2xl border border-stone-200 bg-white p-2">
                                    ${buildSlotCards(index, session.time_slot_ids, session.weekday)}
                                </div>
                                <p class="mt-1 text-xs font-semibold text-stone-500">Bấm trực tiếp vào từng khung giờ để chọn nhiều ca.</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            bindSessionEvents();
            readSessionsFromDOM();
            updateAll();
        }

        function bindSessionEvents() {
            document.querySelectorAll('.session-weekday').forEach(input => {
                input.addEventListener('change', () => {
                    readSessionsFromDOM();
                    oldState.sessions = state.sessions;
                    renderSessions();
                });
            });

            document.querySelectorAll('.session-slot').forEach(input => {
                input.addEventListener('change', () => {
                    readSessionsFromDOM();
                    renderSlotCardState(input);
                    updateAll();
                });
            });
        }

        function readSessionsFromDOM() {
            state.sessions = [...document.querySelectorAll('.session-item')].map(item => {
                const weekday = item.querySelector('.session-weekday')?.value;
                const timeSlotIds = [...item.querySelectorAll('.session-slot:checked')]
                    .map(input => String(input.value))
                    .filter(Boolean);

                return {
                    weekday: String(weekday),
                    court_id: String(state.courtId),
                    time_slot_ids: timeSlotIds,
                };
            });
        }

        function renderSlotCardState(input) {
            const card = input.closest('.slot-card');

            if (!card) {
                return;
            }

            card.classList.toggle('border-emerald-500', input.checked);
            card.classList.toggle('bg-emerald-50', input.checked);
            card.classList.toggle('ring-2', input.checked);
            card.classList.toggle('ring-emerald-100', input.checked);
            card.classList.toggle('border-stone-200', !input.checked);
            card.classList.toggle('bg-white', !input.checked);
        }

        function hasDuplicateSessions() {
            const keys = state.sessions.map(session => {
                return `${session.weekday}-${(session.time_slot_ids || []).slice().sort().join(',')}`;
            });

            return new Set(keys).size !== keys.length;
        }

        function getSlotPrice(slot, weekday) {
    if (!slot) {
        return 0;
    }

    const pricesByWeekday = slot.prices_by_weekday || {};
    const weekdayKey = String(weekday);

    if (pricesByWeekday[weekdayKey] !== undefined) {
        return Number(pricesByWeekday[weekdayKey] || 0);
    }

    return Number(slot.default_price || 0);
}

        function buildSchedule() {
            const pkg = getSelectedPackage();
            const startDate = getDateFromInput(startDateInput.value);

            if (!pkg || !startDate || state.sessions.length === 0) {
                state.schedule = [];
                return;
            }

            const endDate = pkg.type === 'week'
                ? addDays(startDate, pkg.duration * 7)
                : addMonths(startDate, pkg.duration);

            const result = [];

            state.sessions.forEach((session, sessionIndex) => {
                const selectedSlotIds = session.time_slot_ids || [];

                if (selectedSlotIds.length === 0) {
                    return;
                }

                const slots = selectedSlotIds
                    .map(id => getSelectedSlot(id))
                    .filter(Boolean)
                    .sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)));
                const firstSlot = slots[0];
                const lastSlot = slots[slots.length - 1];
                const sessionPrice = slots.reduce((sum, slot) => sum + getSlotPrice(slot, session.weekday), 0);
                let date = getFirstDateByWeekday(startDate, session.weekday);

                while (date < endDate) {
                    result.push({
                        sessionIndex: sessionIndex + 1,
                        date: new Date(date),
                        weekday: session.weekday,
                        weekdayLabel: weekdays[session.weekday],
                        slotId: selectedSlotIds.join(','),
                        slotLabel: firstSlot && lastSlot ? `${firstSlot.start_time} - ${lastSlot.end_time}` : '—',
                        price: sessionPrice,
                    });

                    date = addDays(date, 7);
                }
            });

            result.sort((a, b) => a.date - b.date);

            state.schedule = result;
        }

        function calculatePrice() {
            const pkg = getSelectedPackage();

            if (!pkg) {
                state.originalAmount = 0;
                state.discountAmount = 0;
                state.finalAmount = 0;
                return;
            }

            const original = state.schedule.reduce((total, item) => {
                return total + Number(item.price || 0);
            }, 0);

            const discount = Math.round(original * Number(pkg.discount_percent || 0) / 100);
            const final = original - discount;

            state.originalAmount = original;
            state.discountAmount = discount;
            state.finalAmount = final;
        }

        function renderSchedulePreview() {
            if (state.schedule.length === 0) {
                schedulePreview.innerHTML = `
                    <div class="rounded-xl border border-dashed border-stone-300 bg-white p-4 text-sm text-stone-500">
                        Vui lòng chọn đầy đủ gói, sân, khung giờ và ngày bắt đầu để xem lịch dự kiến.
                    </div>
                `;

                scheduleCount.textContent = '';
                return;
            }

            scheduleCount.textContent = `${state.schedule.length} buổi`;

            schedulePreview.innerHTML = state.schedule.map((item, index) => {
                return `
                    <div class="grid grid-cols-[48px,1fr] gap-3 rounded-xl bg-white p-3 text-sm">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-extrabold text-emerald-700">
                            ${index + 1}
                        </div>

                        <div>
                            <p class="font-extrabold text-zinc-900">
                                ${formatDate(item.date)} · ${item.weekdayLabel}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-stone-500">
                                Buổi ${item.sessionIndex} · ${item.slotLabel}
                            </p>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderSummary() {
            const pkg = getSelectedPackage();
            const court = getSelectedCourt();

            document.getElementById('summary-package').textContent = pkg
                ? `${pkg.name} · ${getDurationText(pkg)}`
                : '—';

            document.getElementById('summary-court').textContent = court
                ? court.name
                : '—';

            document.getElementById('summary-total-sessions').textContent = `${state.schedule.length} buổi`;
            document.getElementById('summary-original').textContent = money(state.originalAmount);
            document.getElementById('summary-discount').textContent = `- ${money(state.discountAmount)}`;
            document.getElementById('summary-final').textContent = money(state.finalAmount);
        }

        function renderDuplicateWarning() {
            if (hasDuplicateSessions()) {
                duplicateWarning.textContent = 'Không nên chọn trùng cùng thứ và cùng khung giờ trong một gói.';
                duplicateWarning.classList.remove('hidden');
                return;
            }

            duplicateWarning.textContent = '';
            duplicateWarning.classList.add('hidden');
        }

        function updateAll() {
            packageInput.value = state.packageId || '';
            courtInput.value = state.courtId || '';

            setActiveCards();
            buildSchedule();
            calculatePrice();
            renderSchedulePreview();
            renderSummary();
            renderDuplicateWarning();
        }

        function openConfirmModal() {
            const pkg = getSelectedPackage();
            const court = getSelectedCourt();

            const sessionText = state.sessions.map(session => {
                const slots = (session.time_slot_ids || [])
                    .map(id => getSelectedSlot(id))
                    .filter(Boolean)
                    .sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)));
                const firstSlot = slots[0];
                const lastSlot = slots[slots.length - 1];

                return `${weekdays[session.weekday]} · ${firstSlot && lastSlot ? `${firstSlot.start_time} - ${lastSlot.end_time}` : '—'}`;
            }).join('<br>');

            document.getElementById('modal-package').textContent = pkg
                ? `${pkg.name} · ${getDurationText(pkg)}`
                : '—';

            document.getElementById('modal-court').textContent = court ? court.name : '—';
            document.getElementById('modal-sessions').innerHTML = sessionText || '—';
            document.getElementById('modal-total-sessions').textContent = `${state.schedule.length} buổi`;
            document.getElementById('modal-final').textContent = money(state.finalAmount);

            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
        }

        function closeConfirmModal() {
            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('flex');
        }

        function validateBeforeSubmit() {
            readSessionsFromDOM();
            updateAll();

            if (!state.packageId) {
                alert('Vui lòng chọn gói.');
                return false;
            }

            if (!state.courtId) {
                alert('Vui lòng chọn sân.');
                return false;
            }

            if (state.sessions.length === 0) {
                alert('Vui lòng chọn số buổi mỗi tuần.');
                return false;
            }

            const missingSlot = state.sessions.some(session => !session.time_slot_ids || session.time_slot_ids.length === 0);

            if (missingSlot) {
                alert('Vui lòng chọn đầy đủ khung giờ cho từng buổi.');
                return false;
            }

            if (hasDuplicateSessions()) {
                alert('Bạn đang chọn trùng thứ và khung giờ. Vui lòng kiểm tra lại.');
                return false;
            }

            if (state.schedule.length === 0) {
                alert('Không sinh được lịch dự kiến. Vui lòng kiểm tra ngày bắt đầu và khung giờ.');
                return false;
            }

            return true;
        }

        document.querySelectorAll('.package-card').forEach(card => {
            card.addEventListener('click', () => {
                state.packageId = String(card.dataset.packageId);
                updateAll();
            });
        });

        document.querySelectorAll('.court-card').forEach(card => {
            card.addEventListener('click', () => {
                state.courtId = String(card.dataset.courtId);

                oldState.sessions = state.sessions.map(session => {
                    return {
                        ...session,
                        court_id: state.courtId,
                        time_slot_ids: [],
                    };
                });

                renderSessions();
            });
        });

        document.querySelectorAll('input[name="weekly_sessions"]').forEach(radio => {
            radio.addEventListener('change', () => {
                state.weeklySessions = Number(radio.value);

                oldState.sessions = state.sessions;
                renderSessions();
            });
        });

        startDateInput?.addEventListener('change', updateAll);

        form?.addEventListener('submit', event => {
            if (state.submitting) {
                return;
            }

            event.preventDefault();

            if (!validateBeforeSubmit()) {
                return;
            }

            openConfirmModal();
        });

        document.getElementById('close-confirm-modal')?.addEventListener('click', closeConfirmModal);
        document.getElementById('cancel-confirm')?.addEventListener('click', closeConfirmModal);

        document.getElementById('confirm-submit')?.addEventListener('click', () => {
            state.submitting = true;
            closeConfirmModal();
            form.submit();
        });

        setActiveCards();
        renderSessions();
        updateAll();
    });
</script>
@endsection
