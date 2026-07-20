@extends('layouts.app')

@section('title', 'Yêu cầu đổi lịch | SportHub')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('account.bookings.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-800">
        ← Quay lại lịch sử đặt sân
    </a>

    <div class="mt-4">
        <p class="text-sm font-black uppercase tracking-wider text-emerald-700">Đổi lịch đặt sân</p>
        <h1 class="mt-2 text-3xl font-black text-zinc-900">{{ $booking->court->venue->name }}</h1>
        <p class="mt-1 font-semibold text-slate-500">{{ $booking->court->name }} · Booking #{{ $booking->id }}</p>
    </div>

    @if(session('error'))
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 font-bold text-red-700">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 font-bold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('customer.booking.reschedule.store', $booking) }}" id="reschedule-form" class="mt-6 grid gap-6 lg:grid-cols-12">
        @csrf

        <div class="space-y-6 lg:col-span-4">
            <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-zinc-900">Chọn ca cũ muốn đổi</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Tick 1 hoặc nhiều ca trong booking này.</p>

                <div class="mt-4 space-y-3">
                    @forelse($bookingItems as $item)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:border-emerald-300">
                            <input type="checkbox"
                                   name="booking_item_ids[]"
                                   value="{{ $item->id }}"
                                   class="old-slot-checkbox mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="block font-black text-zinc-900">
                                    {{ $item->slot_date->format('d/m/Y') }}
                                </span>
                                <span class="mt-1 block text-sm font-bold text-slate-600">
                                    {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}
                                </span>
                                <span class="mt-1 block text-sm font-black text-emerald-700">
                                    {{ number_format((float) $item->price, 0, ',', '.') }}đ
                                </span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-xl bg-amber-50 p-4 text-sm font-bold text-amber-800">
                            Booking này chưa có ca có thể đổi lịch.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <label for="new-slot-date" class="block text-lg font-black text-zinc-900">Chọn ngày mới</label>
                <input id="new-slot-date"
                       name="new_slot_date"
                       type="date"
                       min="{{ now()->toDateString() }}"
                       value="{{ old('new_slot_date') }}"
                       class="mt-4 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 font-bold outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                       required>
            </section>

            <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">Trạng thái</p>
                <p id="selected-count-display" class="mt-2 text-2xl font-black text-emerald-900">0 ca cũ · 0 ca mới</p>
                <p id="slot-message" class="mt-2 text-sm font-bold text-emerald-700">Chọn ca cũ và ngày mới để xem ca trống.</p>
            </section>
        </div>

        <div class="lg:col-span-8">
            <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="mb-5 flex flex-col gap-2 border-b border-stone-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-zinc-900">Danh sách ca trống</h2>
                        <p class="text-sm font-semibold text-slate-500">Click vào thẻ để chọn ca đổi lịch.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $booking->court->name }}</span>
                </div>

                <div id="loading-state" class="hidden rounded-xl border border-dashed border-slate-300 p-8 text-center font-bold text-slate-500">
                    Đang tải khung giờ...
                </div>

                <div id="slot-grid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"></div>

                <div id="empty-state" class="hidden rounded-xl border border-dashed border-slate-300 p-8 text-center font-bold text-slate-500">
                    Chưa có ca khả dụng trong ngày đã chọn.
                </div>

                <div id="error-state" class="hidden rounded-xl border border-red-200 bg-red-50 p-5 text-center font-bold text-red-700">
                    Không thể tải khung giờ. Vui lòng thử lại.
                </div>
            </section>

            <section class="mt-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <label class="block text-sm font-black text-zinc-900">Lý do đổi lịch <span class="font-semibold text-slate-400">(không bắt buộc)</span></label>
                <textarea name="reason"
                          rows="3"
                          class="mt-3 w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                          placeholder="Ví dụ: Bận việc cá nhân, muốn đổi sang khung giờ khác...">{{ old('reason') }}</textarea>

                <div id="hidden-slot-inputs"></div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('account.bookings.index') }}" class="inline-flex justify-center rounded-xl border border-stone-300 px-5 py-3 text-sm font-black text-slate-700 hover:bg-stone-50">
                        Quay lại
                    </a>
                    <button id="submit-button" type="submit" disabled class="inline-flex justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-stone-300">
                        Gửi yêu cầu
                    </button>
                </div>
            </section>
        </div>
    </form>
</div>

<script>
    const dateInput = document.getElementById('new-slot-date');
    const grid = document.getElementById('slot-grid');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const errorState = document.getElementById('error-state');
    const submitButton = document.getElementById('submit-button');
    const selectedCountDisplay = document.getElementById('selected-count-display');
    const slotMessage = document.getElementById('slot-message');
    const hiddenSlotInputs = document.getElementById('hidden-slot-inputs');
    const oldCheckboxes = Array.from(document.querySelectorAll('.old-slot-checkbox'));

    let loadedSlots = [];
    let selectedNewSlotIds = [];

    function money(amount) {
        return Number(amount || 0).toLocaleString('vi-VN') + 'đ';
    }

    function selectedOldCount() {
        return oldCheckboxes.filter(input => input.checked).length;
    }

    function getSlotId(slot) {
        return String(slot.slot_id ?? slot.id ?? '');
    }

    function isSlotAvailable(slot) {
        return Boolean(slot.is_available) && !Boolean(slot.is_past) && !Boolean(slot.is_locked_by_owner);
    }

    function slotBadge(slot) {
        if (isSlotAvailable(slot)) {
            return slot.price_type === 'peak' ? 'CAO ĐIỂM' : 'TRỐNG';
        }

        if (slot.is_booked) {
            return 'ĐÃ ĐẶT';
        }

        return slot.is_locked_by_owner ? 'KHÓA' : 'QUÁ GIỜ';
    }

    function badgeClasses(slot) {
        if (isSlotAvailable(slot)) {
            return slot.price_type === 'peak' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700';
        }

        return slot.is_booked ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-500';
    }

    function cardClasses(slot, selected) {
        const base = 'relative flex min-h-[68px] flex-col justify-center gap-1 rounded-xl border p-2.5 text-left transition outline-none';
        if (!isSlotAvailable(slot)) {
            return `${base} cursor-not-allowed border-slate-200 bg-slate-100/70 opacity-75`;
        }
        if (selected) {
            return `${base} cursor-pointer border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200`;
        }
        if (slot.price_type === 'peak') {
            return `${base} cursor-pointer border-orange-200 bg-orange-50/50 hover:border-orange-400`;
        }
        return `${base} cursor-pointer border-emerald-200 bg-white hover:border-emerald-400`;
    }

    function setVisible(element, visible) {
        element.classList.toggle('hidden', !visible);
    }

    function selectedNewSlots() {
        return selectedNewSlotIds
            .map(id => loadedSlots.find(slot => getSlotId(slot) === id))
            .filter(Boolean)
            .sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)));
    }

    function areConsecutive(slots) {
        if (slots.length <= 1) {
            return true;
        }

        for (let index = 1; index < slots.length; index++) {
            const previousEnd = String(slots[index - 1].end_time).substring(0, 5);
            const currentStart = String(slots[index].start_time).substring(0, 5);

            if (previousEnd !== currentStart) {
                return false;
            }
        }

        return true;
    }

    function syncState() {
        const oldCount = selectedOldCount();
        selectedNewSlotIds = selectedNewSlotIds.slice(0, oldCount);

        hiddenSlotInputs.innerHTML = '';
        selectedNewSlotIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'new_time_slot_ids[]';
            input.value = id;
            hiddenSlotInputs.appendChild(input);
        });

        selectedCountDisplay.textContent = `${oldCount} ca cũ · ${selectedNewSlotIds.length} ca mới`;
        const consecutive = areConsecutive(selectedNewSlots());
        submitButton.disabled = oldCount === 0 || selectedNewSlotIds.length !== oldCount || !consecutive;

        if (oldCount === 0) {
            slotMessage.textContent = 'Chọn ca cũ muốn đổi trước.';
        } else if (!consecutive) {
            slotMessage.textContent = 'Các ca mới phải liền nhau, ví dụ 18:00-19:00 và 19:00-20:00.';
        } else if (selectedNewSlotIds.length < oldCount) {
            slotMessage.textContent = `Chọn thêm ${oldCount - selectedNewSlotIds.length} ca mới.`;
        } else {
            slotMessage.textContent = 'Đã chọn đủ ca, có thể gửi yêu cầu.';
        }
    }

    function renderSlots() {
        grid.innerHTML = '';
        setVisible(emptyState, loadedSlots.length === 0);

        loadedSlots.forEach(slot => {
            const id = getSlotId(slot);
            const available = isSlotAvailable(slot);
            const selected = selectedNewSlotIds.includes(id);
            const button = document.createElement('button');
            button.type = 'button';
            button.disabled = !available;
            button.className = cardClasses(slot, selected);
            button.innerHTML = `
                <div class="flex items-center justify-between gap-2">
                    <strong class="text-xs leading-tight ${available ? (slot.price_type === 'peak' ? 'text-orange-900' : 'text-zinc-900') : 'text-slate-500 line-through'}">
                        ${String(slot.start_time).substring(0, 5)} - ${String(slot.end_time).substring(0, 5)}
                    </strong>
                    <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded border ${selected ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 bg-white'}">
                        ${selected ? '<svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>' : ''}
                    </span>
                </div>
                <div class="mt-0.5 flex items-center justify-between gap-1">
                    <span class="text-sm font-black ${available ? (slot.price_type === 'peak' ? 'text-orange-600' : 'text-emerald-600') : 'text-slate-500'}">${money(slot.price)}</span>
                    <span class="rounded px-1 py-0.5 text-[9px] font-black leading-none ${badgeClasses(slot)}">${slotBadge(slot)}</span>
                </div>
            `;

            if (available) {
                button.addEventListener('click', () => toggleNewSlot(id));
            }

            grid.appendChild(button);
        });

        syncState();
    }

    function toggleNewSlot(id) {
        if (selectedNewSlotIds.includes(id)) {
            selectedNewSlotIds = selectedNewSlotIds.filter(item => item !== id);
            renderSlots();
            return;
        }

        if (selectedNewSlotIds.length >= selectedOldCount()) {
            slotMessage.textContent = 'Bạn đã chọn đủ số ca mới. Bỏ chọn ca cũ hoặc ca mới trước khi chọn thêm.';
            return;
        }

        const nextSelectedIds = [...selectedNewSlotIds, id];
        const nextSlots = nextSelectedIds
            .map(slotId => loadedSlots.find(slot => getSlotId(slot) === slotId))
            .filter(Boolean)
            .sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)));

        if (!areConsecutive(nextSlots)) {
            slotMessage.textContent = 'Vui lòng chọn các ca mới liền nhau, không chọn ca rời nhau.';
            return;
        }

        selectedNewSlotIds = nextSelectedIds;
        renderSlots();
    }

    async function loadSlots() {
        selectedNewSlotIds = [];
        loadedSlots = [];
        grid.innerHTML = '';
        setVisible(errorState, false);

        if (!dateInput.value || selectedOldCount() === 0) {
            setVisible(emptyState, true);
            syncState();
            return;
        }

        setVisible(loadingState, true);
        setVisible(emptyState, false);

        try {
            const response = await fetch(`/api/courts/{{ $booking->court_id }}/availability?date=${encodeURIComponent(dateInput.value)}&_t=${Date.now()}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) throw new Error('Load failed');
            const payload = await response.json();
            loadedSlots = Array.isArray(payload.data) ? payload.data : [];
            renderSlots();
        } catch (error) {
            setVisible(errorState, true);
            syncState();
        } finally {
            setVisible(loadingState, false);
        }
    }

    oldCheckboxes.forEach(input => input.addEventListener('change', () => {
        syncState();
        renderSlots();
        if (dateInput.value) loadSlots();
    }));
    dateInput.addEventListener('change', loadSlots);
    syncState();
    if (dateInput.value) loadSlots();
</script>
@endsection
