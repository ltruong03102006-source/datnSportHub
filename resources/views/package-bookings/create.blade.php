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
    $defaultPackage = $activePackages->firstWhere('id', (int) $defaultPackageId) ?? $activePackages->first();
    $configuredWeeklySessions = max(1, min(7, (int) (data_get($defaultPackage, 'max_sessions_per_week') ?: 7)));

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

    $weeklySessions = $configuredWeeklySessions;

    $weekdayOrder = ['1', '2', '3', '4', '5', '6', '0'];

    $packagesData = $activePackages->map(function ($package) {
        return [
            'id' => (string) $package->id,
            'name' => $package->name,
            'type' => $package->type,
            'duration' => (int) $package->duration,
            'max_sessions_per_week' => max(1, min(7, (int) (data_get($package, 'max_sessions_per_week') ?: 7))),
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

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ url('/venues/' . $venue->id) }}"
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
                    <div class="font-black">Vui long kiem tra lai thong tin dat goi.</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs font-bold">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                <!-- Thay đổi bố cục Grid từ xl:grid-cols-[minmax(0,1fr)_360px] sang lg:grid-cols-[1fr_360px] để chia cột sớm hơn -->
                <form id="package-booking-form"
                      method="POST"
                      action="{{ route('package-bookings.store') }}"
                      class="grid gap-6 lg:grid-cols-[1fr_360px] items-start">
                    @csrf

                    <input type="hidden" name="venue_id" value="{{ $venue->id }}">
                    <input type="hidden" name="package_id" id="package_id" value="{{ $defaultPackageId }}">
                    <input type="hidden" name="court_id" id="court_id" value="{{ $defaultCourtId }}">

                    <!-- CỘT TRÁI: CHỨA CÁC BƯỚC CẤU HÌNH CƠ BẢN -->
                    <div class="min-w-0 space-y-6">
                        <!-- BƯỚC 1: CHỌN GÓI -->
                        <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                    1
                                </div>

                                <div>
                                    <h2 class="text-lg font-black text-zinc-900">
                                        Chọn gói
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Chọn gói ưu đãi phù hợp với nhu cầu chơi cố định.
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
                                            class="package-card rounded-3xl border border-stone-200 p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-400 hover:bg-emerald-50"
                                            data-package-id="{{ $package->id }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-lg font-black text-zinc-900">
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

                                        <div class="mt-4 grid gap-2 text-sm font-semibold text-stone-700">
                                            <p>✓ Lịch cố định theo tuần</p>
                                            <p>✓ Giảm {{ $discount }}%</p>
                                            <p>✓ Tự động tính giá sau giảm</p>
                                        </div>

                                        <div class="mt-4 rounded-2xl bg-stone-50 px-3 py-2 text-xs font-semibold text-stone-500">
                                            Phù hợp khách chơi đều đặn, muốn giữ sân cố định.
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            @error('package_id')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <!-- BƯỚC 2: CHỌN SÂN -->
                        <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                    2
                                </div>

                                <div>
                                    <h2 class="text-lg font-black text-zinc-900">
                                        Chọn sân
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Sau khi chọn sân, hệ thống chỉ hiển thị khung giờ của sân đó.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                @foreach($venue->courts as $court)
                                    <button type="button"
                                            class="court-card rounded-3xl border border-stone-200 p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-400 hover:bg-emerald-50"
                                            data-court-id="{{ $court->id }}">
                                        <p class="font-black text-zinc-900">
                                            {{ $court->name }}
                                        </p>

                                        <p class="mt-1 text-xs font-semibold text-stone-500">
                                            {{ $court->timeSlots->count() }} khung giờ khả dụng
                                        </p>
                                    </button>
                                @endforeach
                            </div>

                            @error('court_id')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <!-- BƯỚC 3: CHỌN SỐ BUỔI VÀ KHUNG GIỜ -->
                        <section class="rounded-[28px] border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                        3
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-black text-zinc-900">
                                            Chọn số buổi mỗi tuần
                                        </h2>

                                        <p class="mt-1 max-w-3xl text-sm text-stone-500">
                                            Chọn số buổi trước, hệ thống sẽ tự sinh từng card chọn ca bên dưới.
                                        </p>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                                    Có thể chọn nhiều ca trong cùng một buổi
                                </div>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                                @for($i = 1; $i <= 7; $i++)
                                    <label class="weekly-option flex cursor-pointer items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 transition hover:border-emerald-400 hover:bg-emerald-50">
                                        <input type="radio"
                                               name="weekly_sessions"
                                               value="{{ $i }}"
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                                               @checked($weeklySessions === $i)>

                                        <span class="text-sm font-bold text-zinc-800">
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

                            <div id="sessions-wrapper" class="mt-6 grid gap-5"></div>

                            <p id="duplicate-warning" class="mt-3 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>

                            @error('sessions')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </section>
                    </div>

                    <!-- CỘT PHẢI (SIDEBAR): TÍNH TIỀN, CHỌN NGÀY VÀ CHÍNH SÁCH -->
                    <aside class="min-w-0 space-y-6 lg:sticky lg:top-6">
                        <!-- TỔNG TIỀN -->
                        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                            <p class="text-lg font-black text-zinc-900">
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
                                        <span class="font-black text-zinc-900">Thanh toán</span>
                                        <span id="summary-final" class="text-right text-xl font-black text-emerald-700">0đ</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 rounded-2xl bg-emerald-50 p-4 text-xs font-semibold leading-6 text-emerald-800">
                                Sau khi đăng ký, gói sẽ ở trạng thái chờ thanh toán. Khi thanh toán thành công, hệ thống mới sinh toàn bộ lịch đặt sân.
                            </div>

                            <button type="submit"
                                    id="submit-button"
                                    class="mt-5 w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                                Đăng ký gói
                            </button>
                        </div>

                        <!-- BƯỚC 4: CHỌN NGÀY BẮT ĐẦU VÀ LỊCH DỰ KIẾN -->
                        <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                    4
                                </div>

                                <div>
                                    <h2 class="text-lg font-black text-zinc-900">
                                        Chọn ngày bắt đầu
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Lịch dự kiến sẽ được cập nhật tự động.
                                    </p>
                                </div>
                            </div>

                            <input type="date"
                                   name="start_date"
                                   id="start_date"
                                   value="{{ old('start_date', now()->toDateString()) }}"
                                   min="{{ now()->toDateString() }}"
                                   class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm font-bold outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                   required>

                            @error('start_date')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-5 rounded-3xl bg-stone-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-black text-zinc-900">
                                        Lịch dự kiến
                                    </p>

                                    <p id="schedule-count" class="text-xs font-bold text-stone-500"></p>
                                </div>

                                <div id="schedule-preview" class="mt-3 grid max-h-96 gap-2.5 overflow-y-auto pr-2"></div>
                            </div>
                        </section>



                        <!-- BƯỚC 5: CHÍNH SÁCH GÓI -->
                        <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                    5
                                </div>

                                <div>
                                    <h2 class="text-lg font-black text-zinc-900">
                                        Chính sách gói
                                    </h2>

                                    <p class="mt-1 text-sm text-stone-500">
                                        Hiển thị rõ chính sách để tránh tranh chấp sau khi khách mua gói.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 text-sm font-semibold text-stone-700">
                                <p class="rounded-2xl bg-stone-50 p-3">✓ Không thể đổi sân trong gói</p>
                                <p class="rounded-2xl bg-stone-50 p-3">✓ Được đổi lịch 1 buổi nếu còn slot trống</p>
                                <p class="rounded-2xl bg-stone-50 p-3">✓ Có thể tạm dừng gói theo quy định cơ sở</p>
                                <p class="rounded-2xl bg-stone-50 p-3">✓ Không hoàn tiền sau khi kích hoạt</p>
                                <p class="rounded-2xl bg-stone-50 p-3">✓ Có thể gia hạn sau khi hết gói</p>
                                <p class="rounded-2xl bg-stone-50 p-3">✓ Lịch chỉ được tạo sau khi thanh toán thành công</p>
                            </div>
                        </section>
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
            weeklySessions: Math.max(1, Math.min(7, Number((packages.find(item => item.id === String(oldState.packageId)) || {}).max_sessions_per_week || 7))),
            sessions: [],
            schedule: [],
            originalAmount: 0,
            discountAmount: 0,
            finalAmount: 0,
            availability: {},
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

        function getConfiguredWeeklySessions(pkg = getSelectedPackage()) {
            return Math.max(1, Math.min(7, Number(pkg?.max_sessions_per_week || 7)));
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

        function isSameCalendarDate(firstDate, secondDate) {
            if (!firstDate || !secondDate) {
                return false;
            }

            return firstDate.getFullYear() === secondDate.getFullYear()
                && firstDate.getMonth() === secondDate.getMonth()
                && firstDate.getDate() === secondDate.getDate();
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

        function getEffectivePackageStartDate() {
            const startDate = getDateFromInput(startDateInput.value);

            if (!startDate) {
                return null;
            }

            return startDate;
        }

        function getFirstDateByWeekday(startDate, weekday) {
            const target = Number(weekday);
            const current = startDate.getDay();
            const diff = (target - current + 7) % 7;

            return addDays(startDate, diff);
        }

        function sessionStartHasPassed(date, slot) {
            if (!date || !slot?.start_time) {
                return false;
            }

            const now = new Date();

            if (date.toDateString() !== now.toDateString()) {
                return false;
            }

            const [hour, minute, second] = String(slot.start_time).split(':').map(Number);
            const sessionStart = new Date(date);
            sessionStart.setHours(hour || 0, minute || 0, second || 0, 0);

            return sessionStart <= now;
        }

        function getFirstBookableSessionDate(startDate, weekday, firstSlot = null) {
            const date = getFirstDateByWeekday(startDate, weekday);

            return isSameCalendarDate(date, new Date()) || sessionStartHasPassed(date, firstSlot)
                ? addDays(date, 7)
                : date;
        }

        function toDateInputValue(date) {
            if (!date) {
                return '';
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function getSessionDate(session) {
            const startDate = getEffectivePackageStartDate();

            if (!startDate || !session?.weekday) {
                return null;
            }
            const slotIds = Array.isArray(session.time_slot_ids) ? session.time_slot_ids : [];
            const firstSlot = slotIds
                .map(id => getSelectedSlot(id))
                .filter(Boolean)
                .sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)))[0] || null;

            return getFirstBookableSessionDate(startDate, session.weekday, firstSlot);
        }

        function formatSessionDate(date) {
            if (!date) {
                return '—';
            }

            const formatted = date.toLocaleDateString('vi-VN', {
                weekday: 'long',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });

            return formatted.charAt(0).toUpperCase() + formatted.slice(1);
        }

        function availabilityCacheKey(courtId, dateString) {
            return `${courtId || 'none'}|${dateString || 'none'}`;
        }

        async function fetchAvailabilityForSession(courtId, dateString) {
            if (!courtId || !dateString) {
                return [];
            }

            const key = availabilityCacheKey(courtId, dateString);

            if (Array.isArray(state.availability[key])) {
                return state.availability[key];
            }

            const response = await fetch(`/api/courts/${courtId}/availability?date=${dateString}&_t=${Date.now()}`, {
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache',
                },
            });

            if (!response.ok) {
                throw new Error('Availability request failed.');
            }

            const payload = await response.json();
            state.availability[key] = Array.isArray(payload.data) ? payload.data : [];

            return state.availability[key];
        }

        function hasVisibleAvailabilitySlots(slots) {
            return Array.isArray(slots) && slots.some(slot => !Boolean(slot.is_past));
        }

        async function resolveAvailabilityForDisplay(courtId, sessionDate) {
            let displayDate = sessionDate;
            let dateValue = toDateInputValue(displayDate);
            let slots = await fetchAvailabilityForSession(courtId, dateValue);

            if (!hasVisibleAvailabilitySlots(slots) && displayDate) {
                displayDate = addDays(displayDate, 7);
                dateValue = toDateInputValue(displayDate);
                slots = await fetchAvailabilityForSession(courtId, dateValue);
            }

            return {
                date: displayDate,
                dateValue,
                slots,
            };
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

        function renderWeeklySessionOptions() {
            const configuredSessions = getConfiguredWeeklySessions();

            if (Number(state.weeklySessions) > configuredSessions) {
                state.weeklySessions = configuredSessions;
            }

            document.querySelectorAll('input[name="weekly_sessions"]').forEach(radio => {
                const option = radio.closest('.weekly-option');
                const value = Number(radio.value);
                const isAllowed = value <= configuredSessions;
                const isSelected = value === Number(state.weeklySessions);

                radio.checked = isSelected;
                radio.disabled = !isAllowed;

                if (option) {
                    option.classList.toggle('hidden', !isAllowed);
                    option.classList.toggle('border-emerald-500', isSelected);
                    option.classList.toggle('bg-emerald-50', isSelected);
                    option.classList.toggle('ring-2', isSelected);
                    option.classList.toggle('ring-emerald-100', isSelected);
                }
            });
        }

        function updateSessionSelectedCount(index) {
            const item = sessionsWrapper.querySelector(`.session-item[data-index="${index}"]`);
            const badge = item?.querySelector(`[data-selected-count="${index}"]`);

            if (!item || !badge) {
                return;
            }

            const count = item.querySelectorAll('.session-slot:checked').length;
            badge.textContent = `${count} ca da chon`;
        }

        // CẢI TIẾN: Sắp xếp lại ô mô tả và rút gọn không gian để gọn gàng hơn
        function refreshSessionEffectiveDate(index) {
            const item = sessionsWrapper.querySelector(`.session-item[data-index="${index}"]`);
            const grid = sessionsWrapper.querySelector(`.slot-grid[data-session-index="${index}"]`);
            const label = item?.querySelector(`[data-session-date-label="${index}"]`);
            const session = state.sessions[index];
            const sessionDate = getSessionDate(session);
            const dateValue = toDateInputValue(sessionDate);

            if (label) {
                label.textContent = formatSessionDate(sessionDate);
            }

            return Boolean(grid && dateValue && grid.dataset.date !== dateValue);
        }

        async function reloadAvailabilityForSession(index) {
            const session = state.sessions[index];
            const grid = sessionsWrapper.querySelector(`.slot-grid[data-session-index="${index}"]`);
            const sessionDate = getSessionDate(session);
            const dateValue = toDateInputValue(sessionDate);

            if (!grid || !state.courtId || !dateValue) {
                return;
            }

            grid.dataset.date = dateValue;
            grid.innerHTML = `
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-xs font-bold text-slate-500 text-center">
                    Đang tải danh sách ca...
                </div>
            `;

            try {
                const availability = await resolveAvailabilityForDisplay(state.courtId, sessionDate);
                const slots = availability.slots;

                grid.dataset.date = availability.dateValue;

                const label = sessionsWrapper.querySelector(`[data-session-date-label="${index}"]`);
                if (label) {
                    label.textContent = formatSessionDate(availability.date);
                }

                grid.innerHTML = buildAvailabilitySlotCards(index, slots, session.time_slot_ids || []);

                grid.querySelectorAll('.session-slot').forEach(input => {
                    input.addEventListener('change', () => {
                        handleSessionSlotChange(input);
                    });
                });

                readSessionsFromDOM();
                updateSessionSelectedCount(index);
                updateAll();
            } catch (error) {
                grid.innerHTML = `
                    <div class="col-span-full rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-bold text-rose-700">
                        Không tải được danh sách ca. Vui lòng chọn lại ngày hoặc sân.
                    </div>
                `;
            }
        }

        function handleSessionSlotChange(input) {
            const item = input.closest('.session-item');
            const index = Number(item?.dataset.index ?? -1);

            readSessionsFromDOM();

            if (index >= 0) {
                refreshSessionEffectiveDate(index);
            }

            renderSlotCardState(input);
            updateSessionSelectedCount(index);
            updateAll();
        }

        function buildSlotCards(index, selectedSlotIds = [], weekday = '1') {
            selectedSlotIds = Array.isArray(selectedSlotIds)
                ? selectedSlotIds.map(String)
                : [String(selectedSlotIds || '')];

            const session = {
                weekday: String(weekday),
                court_id: String(state.courtId),
                time_slot_ids: selectedSlotIds,
            };

            const sessionDate = getSessionDate(session);
            const dateValue = toDateInputValue(sessionDate);
            const selectedCount = selectedSlotIds.filter(Boolean).length;

            return `
                <div class="grid gap-4 md:grid-cols-[200px_minmax(0,1fr)]">
                    <div class="space-y-3">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs">
                            <p class="font-black uppercase tracking-wider text-slate-400 text-[10px]"></p>
                            <p class="mt-1 font-bold text-zinc-900" data-session-date-label="${index}">
                                ${formatSessionDate(sessionDate)}
                            </p>
                            <p class="mt-0.5 text-[11px] font-medium text-slate-500">Nếu chọn ngày hiện tại thì sẽ chuyển lịch sang đúng ngày vào tuần sau</p>
                        </div>

                        <div class="rounded-xl bg-emerald-50 p-3 text-xs">
                            <p class="font-black uppercase tracking-wider text-emerald-700 text-[10px]">
                                Trạng thái
                            </p>
                            <p class="mt-1 font-black text-emerald-800 text-xs leading-snug" data-selected-count="${index}">
                                ${selectedCount} ca da chon
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-[11px]">
                            <p class="font-black uppercase tracking-wider text-slate-400 text-[10px] mb-2">
                                Chú thích
                            </p>
                            <div class="grid grid-cols-2 gap-2 font-bold md:grid-cols-1">
                                <span class="flex items-center gap-1.5 text-emerald-700">
                                    <i class="h-3 w-3 rounded border border-emerald-300 bg-emerald-50 shrink-0"></i>
                                    Thường
                                </span>
                                <span class="flex items-center gap-1.5 text-orange-700">
                                    <i class="h-3 w-3 rounded border border-orange-300 bg-orange-50 shrink-0"></i>
                                    Cao điểm
                                </span>
                                <span class="flex items-center gap-1.5 text-rose-700">
                                    <i class="h-3 w-3 rounded border border-rose-300 bg-rose-50 shrink-0"></i>
                                    Đã đặt
                                </span>
                                <span class="flex items-center gap-1.5 text-slate-500">
                                    <i class="h-3 w-3 rounded border border-slate-300 bg-slate-100 shrink-0"></i>
                                    Khóa
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50">
                        <div class="border-b border-slate-200 px-4 py-2 flex items-center justify-between">
                            <h4 class="text-sm font-black text-zinc-900">
                                Chọn mẫu ca hằng tuần
                            </h4>
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-black text-slate-500">
                                ${getSelectedCourt()?.name || 'Chưa chọn sân'}
                            </span>
                        </div>

                        <div class="p-3">
                            <!-- CẢI TIẾN: Thay thế grid ca thành các ô nhỏ mini-card, tăng số lượng cột hiển thị giúp gom gọn ca lại -->
                            <div class="slot-grid grid gap-1 grid-cols-2 sm:grid-cols-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                                 data-session-index="${index}"
                                 data-date="${dateValue}">
                                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-xs font-bold text-slate-500 text-center">
                                    Đang tải danh sách ca...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // CẢI TIẾN: Thu gọn padding, khoảng cách và cỡ chữ bên trong từng ô chọn ca để tạo cấu trúc thân thiện, dễ nhìn
        function buildAvailabilitySlotCards(index, slots, selectedSlotIds = []) {
            selectedSlotIds = Array.isArray(selectedSlotIds)
                ? selectedSlotIds.map(String)
                : [];

            const visibleSlots = slots.filter(slot => !Boolean(slot.is_past));

            if (!visibleSlots.length) {
                return `
                    <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-xs font-bold text-slate-500">
                        San nay chua co ca kha dung trong ngay da chon.
                    </div>
                `;
            }

            return visibleSlots.map(slot => {
                const slotId = String(slot.slot_id ?? slot.id ?? '');
                const startTime = String(slot.start_time || '').substring(0, 5);
                const endTime = String(slot.end_time || '').substring(0, 5);
                const price = Number(slot.price || 0);
                const priceText = price > 0 ? money(price) : 'Chua co gia';
                const isAvailable = Boolean(slot.is_available);
                const isSelected = selectedSlotIds.includes(slotId) && isAvailable;
                const isPeak = slot.price_type === 'peak';
                const disabled = !isAvailable;

                let classes = 'slot-card relative flex flex-col justify-between min-h-[38px] rounded-lg border px-1.5 py-1 transition text-[10px]';

                let badge = 'Trống';
                let badgeClasses = 'bg-emerald-100 text-emerald-700';
                let priceClasses = 'text-emerald-700';
                let timeClasses = 'text-zinc-900';

                if (disabled) {
                    classes += ' cursor-not-allowed border-slate-200 bg-slate-100 opacity-70';
                    badge = slot.is_booked ? 'Đã đặt' : (slot.is_locked_by_owner ? 'Khóa' : 'Quá giờ');
                    badgeClasses = slot.is_booked ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-500';
                    priceClasses = 'text-slate-400';
                    timeClasses = 'text-slate-400 line-through';
                } else if (isPeak) {
                    classes += ' cursor-pointer border-orange-300 bg-orange-50 hover:-translate-y-0.5 hover:border-orange-400 hover:shadow-sm';
                    badge = 'Cao điểm';
                    badgeClasses = 'bg-orange-100 text-orange-700';
                    priceClasses = 'text-orange-600';
                    timeClasses = 'text-orange-950';
                } else {
                    classes += ' cursor-pointer border-emerald-300 bg-emerald-50/40 hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-sm';
                }

                if (isSelected) {
                    classes += ' border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200 shadow-sm';
                }

                return `
                    <label class="${classes}">
                        <input type="checkbox"
                               name="sessions[${index}][time_slot_ids][]"
                               value="${slotId}"
                               class="session-slot sr-only"
                               ${isSelected ? 'checked' : ''}
                               ${disabled ? 'disabled' : ''}>

                        <span class="slot-check absolute right-1.5 top-1.5 flex h-3.5 w-3.5 items-center justify-center rounded border text-[8px] font-black leading-none transition
                            ${isSelected ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white text-transparent'}">
                            ${isSelected ? '&#10003;' : ''}
                        </span>

                        <div class="pr-4 leading-tight">
                            <p class="font-black ${timeClasses} text-[10px]">
                                ${startTime} - ${endTime}
                            </p>

                            <p class="mt-0.5 font-black ${priceClasses} text-[10px]">
                                ${priceText}
                            </p>
                        </div>

                        <div class="mt-0.5 flex items-center justify-between gap-1">
                            <span class="rounded px-1 py-0.5 text-[7px] font-black uppercase leading-none ${badgeClasses}">
                                ${badge}
                            </span>
                        </div>
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

        async function loadAvailabilityForSessions() {
            const sessionsSnapshot = state.sessions.map(session => ({
                ...session,
                time_slot_ids: Array.isArray(session.time_slot_ids) ? [...session.time_slot_ids] : [],
            }));

            await Promise.all(sessionsSnapshot.map(async (session, index) => {
                const grid = sessionsWrapper.querySelector(`.slot-grid[data-session-index="${index}"]`);
                const sessionDate = getSessionDate(session);
                const dateValue = toDateInputValue(sessionDate);

                if (!grid || !state.courtId || !dateValue) {
                    return;
                }

                try {
                    const availability = await resolveAvailabilityForDisplay(state.courtId, sessionDate);
                    const slots = availability.slots;

                    if (grid.dataset.date !== dateValue) {
                        return;
                    }

                    grid.dataset.date = availability.dateValue;

                    const label = sessionsWrapper.querySelector(`[data-session-date-label="${index}"]`);
                    if (label) {
                        label.textContent = formatSessionDate(availability.date);
                    }

                    grid.innerHTML = buildAvailabilitySlotCards(index, slots, session.time_slot_ids);

                    grid.querySelectorAll('.session-slot').forEach(input => {
                    input.addEventListener('change', () => {
                        handleSessionSlotChange(input);
                    });
                });

                    readSessionsFromDOM();
                    updateSessionSelectedCount(index);
                    updateAll();
                } catch (error) {
                    grid.innerHTML = `
                        <div class="col-span-full rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-bold text-rose-700 text-center">
                            Không tải được danh sách ca. Vui lòng chọn lại ngày hoặc sân.
                        </div>
                    `;
                }
            }));
        }

        function renderSessions() {
            normalizeSessions();

            sessionsWrapper.innerHTML = state.sessions.map((session, index) => {
                const weekdayOptions = Object.entries(weekdays).map(([value, label]) => {
                    const selected = String(session.weekday) === String(value) ? 'selected' : '';

                    return `<option value="${value}" ${selected}>${label}</option>`;
                }).join('');

                return `
                    <div class="session-item rounded-2xl border border-slate-200 bg-white shadow-sm" data-index="${index}">
                        <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-2.5">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-xs font-black text-white">
                                        ${index + 1}
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-black text-zinc-900">
                                            Buổi cố định hằng tuần
                                        </h3>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Thứ trong tuần:
                                    </label>

                                    <select name="sessions[${index}][weekday]"
                                            class="session-weekday rounded-xl border border-slate-300 bg-white px-3 py-1 text-xs font-bold text-zinc-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                            required>
                                        ${weekdayOptions}
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="sessions[${index}][court_id]" value="${state.courtId}">
                        </div>

                        <div class="p-4">
                            ${buildSlotCards(index, session.time_slot_ids, session.weekday)}
                        </div>
                    </div>
                `;
            }).join('');

            bindSessionEvents();
            readSessionsFromDOM();
            updateAll();
            loadAvailabilityForSessions();
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
                    handleSessionSlotChange(input);
                });
            });
        }

        function readSessionsFromDOM() {
            state.sessions = [...document.querySelectorAll('.session-item')].map(item => {
                const weekday = item.querySelector('.session-weekday')?.value;
                const selectedInputs = [...item.querySelectorAll('.session-slot:checked')];
                const timeSlotIds = selectedInputs
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
            card.classList.toggle('ring-emerald-200', input.checked);
            card.classList.toggle('shadow-sm', input.checked);

            const check = card.querySelector('.slot-check');

            if (check) {
                check.classList.toggle('border-emerald-500', input.checked);
                check.classList.toggle('bg-emerald-500', input.checked);
                check.classList.toggle('text-white', input.checked);
                check.classList.toggle('border-slate-300', !input.checked);
                check.classList.toggle('bg-white', !input.checked);
                check.classList.toggle('text-transparent', !input.checked);
                check.innerHTML = input.checked ? '✓' : '';
            }

            const item = input.closest('.session-item');
            if (item) {
                updateSessionSelectedCount(item.dataset.index);
            }
        }

        function hasDuplicateSessions() {
            const keys = [];

            state.sessions.forEach(session => {
                (session.time_slot_ids || []).forEach(slotId => {
                    keys.push(`${session.weekday}-${slotId}`);
                });
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
            const startDate = getEffectivePackageStartDate();

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
                const baseDate = getFirstDateByWeekday(startDate, session.weekday);
                let date = getFirstBookableSessionDate(startDate, session.weekday, firstSlot);
                const sessionEndDate = pkg.type === 'week' && date > baseDate
                    ? addDays(endDate, 7)
                    : endDate;

                while (date < sessionEndDate) {
                    result.push({
                        sessionIndex: sessionIndex + 1,
                        date: new Date(date),
                        weekday: session.weekday,
                        weekdayLabel: weekdays[session.weekday],
                        courtName: getSelectedCourt()?.name || '—',
                        slotId: selectedSlotIds.join(','),
                        slotLabel: firstSlot && lastSlot ? `${firstSlot.start_time} - ${lastSlot.end_time}` : '—',
                        startTime: firstSlot?.start_time || '',
                        price: sessionPrice,
                    });

                    date = addDays(date, 7);
                }
            });

            result.sort((a, b) => {
                const dateDiff = a.date - b.date;

                if (dateDiff !== 0) {
                    return dateDiff;
                }

                return String(a.startTime || '').localeCompare(String(b.startTime || ''));
            });

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
                    <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-4 text-xs text-stone-500 text-center">
                        Vui lòng chọn đầy đủ thông tin để xem lịch dự kiến.
                    </div>
                `;

                scheduleCount.textContent = '';
                return;
            }

            scheduleCount.textContent = `${state.schedule.length} buổi`;

            schedulePreview.innerHTML = state.schedule.map((item, index) => {
                return `
                    <div class="rounded-2xl border border-slate-100 bg-white p-3 text-xs shadow-sm">
                        <div class="flex items-start gap-2.5">
                            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-black text-emerald-700">
                                ${index + 1}
                            </div>
                            <p class="font-bold text-zinc-900">
                                ${formatDate(item.date)} · ${item.weekdayLabel}
                            </p>
                        </div>
                        <p class="mt-2 rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-bold leading-relaxed text-slate-600">
                            Buổi ${index + 1} · ${item.slotLabel}
                        </p>
                        <div class="mt-2 grid gap-1.5 rounded-xl bg-white px-3 py-2 text-[11px] font-semibold leading-relaxed text-slate-600">
                            <div class="flex items-start justify-between gap-2">
                                <span class="shrink-0 text-slate-400">Sân</span>
                                <span class="text-right font-bold text-zinc-800 break-words">${item.courtName}</span>
                            </div>

                            <div class="flex items-start justify-between gap-2">
                                <span class="shrink-0 text-slate-400">Giờ</span>
                                <span class="text-right font-black text-emerald-700">${item.slotLabel}</span>
                            </div>
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
            renderWeeklySessionOptions();
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

            if (state.sessions.length > getConfiguredWeeklySessions()) {
                alert('Số buổi/tuần phải đúng theo cấu hình gói.');
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
                oldState.sessions = state.sessions;
                state.packageId = String(card.dataset.packageId);
                state.weeklySessions = Math.min(Number(state.weeklySessions || 1), getConfiguredWeeklySessions());
                renderSessions();
            });
        });

        document.querySelectorAll('.court-card').forEach(card => {
            card.addEventListener('click', () => {
                state.courtId = String(card.dataset.courtId);
                state.availability = {};

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

        startDateInput?.addEventListener('change', () => {
            state.availability = {};
            oldState.sessions = state.sessions;
            renderSessions();
        });

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
