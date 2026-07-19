@extends('layouts.app')

@section('title', 'Yêu cầu đổi lịch | SportHub')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('account.bookings.index') }}"
           class="inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-800">
            ← Lịch sử đặt sân
        </a>

        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900">
            Yêu cầu đổi lịch
        </h1>

        <p class="mt-2 text-sm font-semibold text-zinc-500">
            {{ $booking->court->venue->name }} · {{ $booking->court->name }}
        </p>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('customer.booking.reschedule.store', $booking) }}"
          id="reschedule-form">
        @csrf

        <div class="grid gap-8 lg:grid-cols-12 pb-32">
            {{-- SIDEBAR --}}
            <div class="lg:col-span-4 xl:col-span-3">
                <div class="sticky top-24 space-y-6">
                    {{-- Lịch cũ --}}
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-amber-700">
                            Lịch cũ
                        </p>

                        <div class="mt-3 space-y-2 text-sm font-bold text-amber-900">
                            @foreach($sourceBookings as $oldBooking)
                                <div class="rounded-xl bg-white/70 px-3 py-2">
                                    {{ $oldBooking->slot_date->format('d/m/Y') }}
                                    · {{ substr($oldBooking->start_time, 0, 5) }} - {{ substr($oldBooking->end_time, 0, 5) }}
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-3 text-xs font-semibold text-amber-800">
                            Cần chọn đúng {{ $slotCount }} ca mới để gửi yêu cầu.
                        </p>
                    </div>

                    {{-- Chọn ngày --}}
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <label for="new-slot-date" class="mb-2.5 block text-sm font-bold text-zinc-900">
                                Chọn ngày mới
                            </label>

                            <input type="date"
                                   id="new-slot-date"
                                   name="new_slot_date"
                                   min="{{ now()->toDateString() }}"
                                   value="{{ old('new_slot_date') }}"
                                   class="w-full cursor-pointer rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-sm font-bold text-zinc-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                                   required>
                        </div>

                        <div class="rounded-xl bg-stone-100/70 p-4 text-center">
                            <p class="mb-1 text-xs font-medium uppercase tracking-widest text-stone-500">
                                Đang chọn
                            </p>

                            <p id="selected-date-display" class="text-base font-bold text-emerald-700">
                                Chưa chọn ngày
                            </p>
                        </div>
                    </div>

                    {{-- Đã chọn --}}
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-emerald-700">
                            Trạng thái
                        </p>

                        <p id="selected-count-display" class="mt-2 text-2xl font-black text-emerald-800">
                            0/{{ $slotCount }} ca
                        </p>

                        <p id="slot-message" class="mt-2 text-xs font-bold text-emerald-700">
                            Chọn ngày để xem khung giờ.
                        </p>
                    </div>

                    {{-- Chú thích --}}
                    <div class="hidden rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:block">
                        <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-zinc-400">
                            Chú thích trạng thái
                        </h4>

                        <div class="space-y-4 text-sm">
                            <div class="flex items-center gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg border-2 border-emerald-200 bg-white">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                </span>

                                <span class="font-medium text-zinc-700">
                                    Giờ thường
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg border border-orange-300 bg-orange-50">
                                    <span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span>
                                </span>

                                <span class="font-medium text-orange-700">
                                    Giờ cao điểm
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg border border-rose-200 bg-rose-50">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                </span>

                                <span class="font-medium text-zinc-700">
                                    Đã đặt
                                </span>
                            </div>

                            <div class="flex items-center gap-3 opacity-70">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg border border-slate-200 bg-slate-100">
                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                </span>

                                <span class="font-medium text-slate-500">
                                    Khóa / Quá giờ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN --}}
            <div class="lg:col-span-8 xl:col-span-9">
                <div class="rounded-2xl border border-stone-200 bg-white p-1 shadow-sm sm:p-6">
                    <div class="mb-6 hidden items-center justify-between border-b border-stone-100 pb-4 sm:flex">
                        <div>
                            <h3 class="text-xl font-bold text-zinc-900">
                                Danh sách ca trống
                            </h3>

                            <p class="mt-1 text-sm font-medium text-zinc-500">
                                Click vào thẻ để chọn ca đổi lịch
                            </p>
                        </div>

                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                            Chọn đúng {{ $slotCount }} ca
                        </span>
                    </div>

                    <div class="p-3 sm:p-0">
                        {{-- Loading --}}
                        <div id="loading-state" class="hidden flex-col items-center justify-center py-20">
                            <svg class="mb-4 h-10 w-10 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>

                            <p class="animate-pulse text-sm font-medium tracking-wide text-zinc-500">
                                Đang tải khung giờ...
                            </p>
                        </div>

                        {{-- Slots --}}
                        <div id="slot-grid" class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4"></div>

                        {{-- Empty --}}
                        <div id="empty-state" class="hidden py-20 text-center">
                            <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-stone-100">
                                <svg class="h-8 w-8 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>

                            <h4 class="mb-2 text-lg font-bold text-zinc-900">
                                Không có ca trống
                            </h4>

                            <p class="text-sm text-zinc-500">
                                Ngày này đã kín lịch. Vui lòng chọn ngày khác.
                            </p>
                        </div>

                        {{-- Error --}}
                        <div id="error-state" class="hidden rounded-xl border border-red-200 bg-red-50 p-6 text-center">
                            <p class="mb-1 font-bold text-red-700">
                                Lỗi kết nối
                            </p>

                            <p class="text-sm text-red-600">
                                Không thể tải khung giờ. Vui lòng thử lại.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Lý do --}}
                <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <label class="mb-2 block text-sm font-bold text-zinc-900">
                        Lý do đổi lịch
                        <span class="font-semibold text-stone-400">(không bắt buộc)</span>
                    </label>

                    <textarea name="reason"
                              rows="3"
                              class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                              placeholder="Ví dụ: Bận việc cá nhân, muốn đổi sang khung giờ khác...">{{ old('reason') }}</textarea>

                    <div id="hidden-slot-inputs"></div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('account.bookings.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-stone-300 bg-white px-5 py-3 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50">
                            Quay lại
                        </a>

                        <button type="submit"
                                id="submit-button"
                                disabled
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-stone-300">
                            Gửi yêu cầu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const dateInput = document.getElementById('new-slot-date');
    const grid = document.getElementById('slot-grid');
    const submitButton = document.getElementById('submit-button');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const errorState = document.getElementById('error-state');
    const selectedDateDisplay = document.getElementById('selected-date-display');
    const selectedCountDisplay = document.getElementById('selected-count-display');
    const slotMessage = document.getElementById('slot-message');
    const hiddenSlotInputs = document.getElementById('hidden-slot-inputs');

    const requiredCount = {{ $slotCount }};
    let selectedIds = [];
    let loadedSlots = [];

    function money(amount) {
        amount = Number(amount || 0);

        return amount.toLocaleString('vi-VN') + 'đ';
    }

    function formatDateText(value) {
        if (!value) {
            return 'Chưa chọn ngày';
        }

        const date = new Date(`${value}T00:00:00`);
        const formatted = date.toLocaleDateString('vi-VN', {
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });

        return formatted.charAt(0).toUpperCase() + formatted.slice(1);
    }

    function setVisible(element, visible, flex = false) {
        if (!element) {
            return;
        }

        element.classList.toggle('hidden', !visible);

        if (flex) {
            element.classList.toggle('flex', visible);
        }
    }

    function getSlotId(slot) {
        return String(slot.slot_id ?? slot.id ?? '');
    }

    function isSlotAvailable(slot) {
        return Boolean(slot.is_available) && !Boolean(slot.is_past);
    }

    function getSlotBadge(slot) {
        if (isSlotAvailable(slot)) {
            return slot.price_type === 'peak' ? 'Cao điểm' : 'Còn trống';
        }

        if (slot.is_booked) {
            return 'Đã đặt';
        }

        if (slot.is_locked_by_owner) {
            return 'Khóa';
        }

        return 'Quá giờ';
    }

    function getCardClasses(slot, selected) {
        const available = isSlotAvailable(slot);
        const isPeak = slot.price_type === 'peak';

        let classes = 'slot-card group relative flex min-h-[68px] flex-col justify-center overflow-hidden rounded-xl border p-2.5 text-left transition-all duration-200 ease-out outline-none select-none gap-1';

        if (!available) {
            return classes + ' cursor-not-allowed border-slate-200 bg-slate-100/60 opacity-75';
        }

        if (selected) {
            return classes + ' cursor-pointer border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200 shadow-sm';
        }

        if (isPeak) {
            return classes + ' cursor-pointer border-orange-200 bg-orange-50/50 hover:-translate-y-0.5 hover:border-orange-400 hover:shadow-sm';
        }

        return classes + ' cursor-pointer border-emerald-200 bg-white hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-sm';
    }

    function getBadgeClasses(slot) {
        if (isSlotAvailable(slot)) {
            return slot.price_type === 'peak'
                ? 'bg-orange-100 text-orange-700'
                : 'bg-emerald-100 text-emerald-700';
        }

        if (slot.is_booked) {
            return 'bg-rose-100 text-rose-700';
        }

        return 'bg-slate-200 text-slate-500';
    }

    function renderSelectedState() {
        hiddenSlotInputs.innerHTML = '';

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'new_time_slot_ids[]';
            input.value = id;

            hiddenSlotInputs.appendChild(input);
        });

        selectedCountDisplay.textContent = `${selectedIds.length}/${requiredCount} ca`;
        submitButton.disabled = selectedIds.length !== requiredCount;

        if (selectedIds.length === 0) {
            slotMessage.textContent = `Chọn đúng ${requiredCount} ca trống.`;
        } else if (selectedIds.length < requiredCount) {
            slotMessage.textContent = `Đã chọn ${selectedIds.length}/${requiredCount} ca. Chọn thêm ${requiredCount - selectedIds.length} ca.`;
        } else {
            slotMessage.textContent = `Đã chọn đủ ${requiredCount}/${requiredCount} ca. Có thể gửi yêu cầu.`;
        }
    }

    function renderSlots() {
        grid.innerHTML = '';

        const visibleSlots = loadedSlots.filter(slot => {
            return !Boolean(slot.is_hidden);
        });

        if (visibleSlots.length === 0) {
            setVisible(emptyState, true);
            renderSelectedState();
            return;
        }

        setVisible(emptyState, false);

        visibleSlots.forEach(slot => {
            const slotId = getSlotId(slot);
            const available = isSlotAvailable(slot);
            const selected = selectedIds.includes(slotId);
            const startTime = String(slot.start_time || '').substring(0, 5);
            const endTime = String(slot.end_time || '').substring(0, 5);
            const price = Number(slot.price || slot.default_price || 0);
            const priceText = price > 0 ? money(price) : 'Chưa có giá';

            const button = document.createElement('button');
            button.type = 'button';
            button.disabled = !available;
            button.dataset.id = slotId;
            button.className = getCardClasses(slot, selected);

            button.innerHTML = `
                <div class="flex items-center justify-between gap-2">
                    <h4 class="slot-time text-xs leading-tight ${available ? (slot.price_type === 'peak' ? 'font-bold text-orange-900' : 'font-semibold text-zinc-800') : 'font-medium text-slate-500 line-through decoration-slate-300'}">
                        ${startTime} - ${endTime}
                    </h4>

                    <span class="slot-checkbox flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded border transition-all duration-200
                        ${selected ? 'border-emerald-500 bg-emerald-500 text-white' : (slot.price_type === 'peak' ? 'border-orange-300 bg-white' : 'border-stone-300 bg-white')}">
                        ${selected ? '<svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>' : ''}
                    </span>
                </div>

                <div class="mt-0.5 flex items-center justify-between gap-1">
                    <p class="slot-price text-sm leading-tight ${available ? (slot.price_type === 'peak' ? 'font-black text-orange-600' : 'font-bold text-emerald-600') : 'font-medium text-slate-500'}">
                        ${priceText}
                    </p>

                    <span class="rounded px-1 py-0.5 text-[9px] font-bold uppercase leading-none ${getBadgeClasses(slot)}">
                        ${getSlotBadge(slot)}
                    </span>
                </div>
            `;

            if (available) {
                button.addEventListener('click', () => {
                    toggleSlot(slotId);
                });
            }

            grid.appendChild(button);
        });

        renderSelectedState();
    }

    function toggleSlot(slotId) {
        if (selectedIds.includes(slotId)) {
            selectedIds = selectedIds.filter(id => id !== slotId);
            renderSlots();
            return;
        }

        if (selectedIds.length >= requiredCount) {
            slotMessage.textContent = `Bạn đã chọn đủ ${requiredCount} ca. Bỏ chọn ca cũ trước khi chọn ca khác.`;
            return;
        }

        selectedIds.push(slotId);
        renderSlots();
    }

    async function loadSlots() {
        const date = dateInput.value;

        selectedDateDisplay.textContent = formatDateText(date);
        selectedIds = [];
        loadedSlots = [];
        grid.innerHTML = '';

        setVisible(emptyState, false);
        setVisible(errorState, false);

        renderSelectedState();

        if (!date) {
            slotMessage.textContent = 'Chọn ngày để xem khung giờ.';
            return;
        }

        setVisible(loadingState, true, true);
        slotMessage.textContent = 'Đang tải khung giờ...';

        try {
            const response = await fetch(`/api/courts/{{ $booking->court_id }}/availability?date=${encodeURIComponent(date)}&_t=${Date.now()}`, {
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
            loadedSlots = Array.isArray(payload.data) ? payload.data : payload;

            setVisible(loadingState, false, true);

            if (!loadedSlots.length) {
                setVisible(emptyState, true);
                slotMessage.textContent = 'Ngày này chưa có khung giờ khả dụng.';
                return;
            }

            slotMessage.textContent = `Chọn đúng ${requiredCount} ca trống.`;
            renderSlots();
        } catch (error) {
            setVisible(loadingState, false, true);
            setVisible(errorState, true);
            slotMessage.textContent = 'Không thể tải khung giờ. Vui lòng thử lại.';
        }
    }

    dateInput.addEventListener('change', loadSlots);

    selectedDateDisplay.textContent = formatDateText(dateInput.value);
    renderSelectedState();

    if (dateInput.value) {
        loadSlots();
    }
</script>
@endsection
