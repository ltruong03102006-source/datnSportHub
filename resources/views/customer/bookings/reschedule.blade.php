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

        <div class="space-y-4 lg:col-span-4">
            <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-black text-zinc-900">Chọn ca cũ muốn đổi</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Tick 1 hoặc nhiều ca trong booking này.</p>

                <div class="mt-3 space-y-2.5">
                    @forelse($bookingItems as $item)
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 transition hover:border-emerald-300">
                            <input type="checkbox"
                                   name="booking_item_ids[]"
                                   value="{{ $item->id }}"
                                   data-price-type="{{ $item->price_type ?? 'normal' }}"
                                   data-start-time="{{ $item->start_time }}"
                                   class="old-slot-checkbox mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-black text-zinc-900">
                                    {{ $item->slot_date->format('d/m/Y') }}
                                </span>
                                <span class="mt-0.5 block text-sm font-bold text-slate-600">
                                    {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}
                                </span>
                                <span class="mt-0.5 flex items-center gap-2">
                                    <span class="text-sm font-black text-emerald-700">
                                        {{ number_format((float) $item->price, 0, ',', '.') }}đ
                                    </span>
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-black {{ ($item->price_type ?? 'normal') === 'peak' ? 'bg-orange-100 text-orange-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ ($item->price_type ?? 'normal') === 'peak' ? 'Cao điểm' : 'Thường' }}
                                    </span>
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

            <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <label for="new-slot-date" class="block text-base font-black text-zinc-900">Chọn ngày mới</label>
                <input id="new-slot-date"
                       name="new_slot_date"
                       type="date"
                       min="{{ now()->toDateString() }}"
                       value="{{ old('new_slot_date') }}"
                       class="mt-3 w-full rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm font-bold outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                       required>
            </section>

            <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">Trạng thái</p>
                <p id="selected-count-display" class="mt-1.5 text-xl font-black text-emerald-900">0 ca cũ · 0 ca mới</p>
                <p id="slot-message" class="mt-1.5 text-xs font-bold text-emerald-700">Chọn ca cũ và ngày mới để xem ca trống.</p>
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
            <!-- BẢNG TÍNH TIỀN & PHƯƠNG THỨC THANH TOÁN -->
            <section id="payment-summary-card" class="mt-6 hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.753 3h15.75c.621 0 1.125-.504 1.125-1.125V6.375c0-.621-.504-1.125-1.125-1.125H3.753c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            Chi tiết khoản chênh lệch & Thanh toán
                        </h3>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">Xác nhận thông tin tiền chênh lệch và chọn hình thức thanh toán</p>
                    </div>
                    <span id="payment-card-badge" class="rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-xs font-black text-amber-800">Cần thanh toán thêm</span>
                </div>

                <!-- Tóm tắt số tiền -->
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                        <span class="block text-xs font-bold text-slate-500">Giá tổng ca cũ</span>
                        <span id="summary-old-price" class="mt-1 block text-lg font-black text-slate-800">0đ</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                        <span class="block text-xs font-bold text-slate-500">Giá ca mới xin đổi</span>
                        <span id="summary-new-price" class="mt-1 block text-lg font-black text-slate-900">0đ</span>
                    </div>
                    <div id="diff-box" class="rounded-xl bg-emerald-700 p-4 text-white shadow-sm">
                        <span id="diff-box-label" class="block text-xs font-bold uppercase text-emerald-100">Số tiền cần thanh toán</span>
                        <span id="summary-diff-price" class="mt-1 block text-2xl font-black">+0đ</span>
                    </div>
                </div>

                <!-- Chọn phương thức thanh toán -->
                <div id="payment-method-selector-container" class="mt-5 border-t border-slate-100 pt-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-3">Chọn phương thức thanh toán khoản chênh lệch:</label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <!-- Option 1: Ví tài khoản -->
                        <label class="relative flex cursor-pointer rounded-xl border border-slate-200 p-4 shadow-2xs transition hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-500">
                            <input type="radio" name="payment_method" value="wallet" checked onchange="onPaymentMethodChange('wallet')" class="sr-only">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 font-bold">
                                        💳
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Ví tài khoản</p>
                                        <p class="text-xs font-semibold text-slate-500 mt-0.5">Số dư: <strong class="text-emerald-700 font-black">{{ number_format((float) ($userWallet->balance ?? 0), 0, ',', '.') }} VNĐ</strong></p>
                                    </div>
                                </div>
                                <span class="h-4 w-4 rounded-full border border-slate-300 flex items-center justify-center has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-600"></span>
                            </div>
                        </label>

                        <!-- Option 2: VNPay -->
                        <label class="relative flex cursor-pointer rounded-xl border border-slate-200 p-4 shadow-2xs transition hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                            <input type="radio" name="payment_method" value="vnpay" onchange="onPaymentMethodChange('vnpay')" class="sr-only">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700 font-bold">
                                        🏧
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Thanh toán VNPay</p>
                                        <p class="text-xs font-semibold text-slate-500 mt-0.5">Quét mã QR / ATM / Internet Banking</p>
                                    </div>
                                </div>
                                <span class="h-4 w-4 rounded-full border border-slate-300 flex items-center justify-center has-[:checked]:border-blue-600 has-[:checked]:bg-blue-600"></span>
                            </div>
                        </label>
                    </div>

                    <div id="wallet-status-badge" class="mt-3"></div>
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
                    <button id="submit-button" type="submit" disabled onclick="handleRescheduleSubmit(event)" class="inline-flex justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-stone-300">
                        Gửi yêu cầu & Thanh toán
                    </button>
                </div>
            </section>
        </div>
    </form>

    <!-- MODAL THANH TOÁN CHÊNH LỆCH ĐỔI LỊCH -->
    <div id="reschedulePaymentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-700 to-teal-800 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.753 3h15.75c.621 0 1.125-.504 1.125-1.125V6.375c0-.621-.504-1.125-1.125-1.125H3.753c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black">Xác nhận thanh toán đổi lịch</h3>
                            <p class="text-xs font-semibold text-emerald-100">Chi tiết khoản chênh lệch & Ví tài khoản</p>
                        </div>
                    </div>
                    <button type="button" onclick="closePaymentModal()" class="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <!-- Order summary card -->
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="font-bold text-slate-500">Giá tổng ca cũ:</span>
                        <span id="modal-old-price" class="font-black text-slate-800">0đ</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-bold text-slate-500">Giá tổng ca mới xin đổi:</span>
                        <span id="modal-new-price" class="font-black text-slate-800">0đ</span>
                    </div>
                    <div class="border-t border-slate-200 pt-2.5 flex justify-between items-center">
                        <span class="font-black text-slate-900 text-sm">Số tiền cần thanh toán thêm:</span>
                        <span id="modal-diff-price" class="text-xl font-black text-orange-600">+0đ</span>
                    </div>
                </div>

                <!-- Wallet balance info -->
                <div class="mt-4 rounded-2xl border border-slate-200/80 p-4 bg-white space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-500 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                            Ví tài khoản hiện tại:
                        </span>
                        <span class="font-black text-zinc-900 text-sm">{{ number_format((float) ($userWallet->balance ?? 0), 0, ',', '.') }} VNĐ</span>
                    </div>

                    <div id="wallet-sufficient-notice" class="hidden rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold text-emerald-800">
                        ✅ Số dư ví đủ thanh toán. Số dư còn lại sau khi trừ: <span id="wallet-after-balance" class="font-black">0 VNĐ</span>
                    </div>

                    <div id="wallet-insufficient-notice" class="hidden rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-800">
                        ❌ Số dư ví không đủ để thanh toán. Bạn còn thiếu: <span id="wallet-shortage-amount" class="font-black">0 VNĐ</span>
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="bg-slate-50 border-t border-slate-100 p-4 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">
                <button type="button" onclick="closePaymentModal()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 bg-white font-bold text-slate-700 hover:bg-slate-100 text-sm">
                    Hủy bỏ
                </button>

                <button type="button" id="confirm-pay-submit-btn" onclick="executeFormSubmit()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 font-black text-white hover:bg-emerald-700 text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Xác nhận thanh toán & Gửi yêu cầu
                </button>
            </div>
        </div>
    </div>
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

    function getSelectedOldItems() {
        return oldCheckboxes
            .filter(input => input.checked)
            .map(input => ({
                id: input.value,
                priceType: input.dataset.priceType || 'normal',
                startTime: input.dataset.startTime || ''
            }))
            .sort((a, b) => String(a.startTime).localeCompare(String(b.startTime)));
    }

    function selectedOldCount() {
        return getSelectedOldItems().length;
    }

    function isPriceTypeMatchingValid(oldItems, newSlots) {
        return true;
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
        const oldItems = getSelectedOldItems();
        const oldCount = oldItems.length;
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
        const currentNewSlots = selectedNewSlots();
        const consecutive = areConsecutive(currentNewSlots);

        submitButton.disabled = oldCount === 0 || selectedNewSlotIds.length !== oldCount || !consecutive;

        const paymentCard = document.getElementById('payment-summary-card');
        const summaryOld = document.getElementById('summary-old-price');
        const summaryNew = document.getElementById('summary-new-price');
        const summaryDiff = document.getElementById('summary-diff-price');
        const diffBox = document.getElementById('diff-box');
        const diffBoxLabel = document.getElementById('diff-box-label');
        const cardBadge = document.getElementById('payment-card-badge');
        const walletBadge = document.getElementById('wallet-status-badge');

        if (oldCount === 0) {
            slotMessage.textContent = 'Chọn ca cũ muốn đổi trước.';
            if (paymentCard) paymentCard.classList.add('hidden');
            submitButton.innerHTML = `Gửi yêu cầu & Thanh toán`;
        } else if (!consecutive) {
            slotMessage.textContent = 'Các ca mới phải liền nhau, ví dụ 18:00-19:00 và 19:00-20:00.';
            if (paymentCard) paymentCard.classList.add('hidden');
            submitButton.innerHTML = `Gửi yêu cầu & Thanh toán`;
        } else if (selectedNewSlotIds.length < oldCount) {
            slotMessage.textContent = `Chọn thêm ${oldCount - selectedNewSlotIds.length} ca mới.`;
            if (paymentCard) paymentCard.classList.add('hidden');
            submitButton.innerHTML = `Gửi yêu cầu & Thanh toán`;
        } else {
            // Tính toán chênh lệch giá
            let oldTotal = 0;
            let newTotal = 0;
            oldCheckboxes.filter(i => i.checked).forEach(i => {
                const parent = i.closest('label');
                const priceText = parent ? parent.querySelector('.text-emerald-700')?.textContent : '0';
                const p = parseInt((priceText || '0').replace(/[^0-9]/g, '')) || 0;
                oldTotal += p;
            });
            currentNewSlots.forEach(s => {
                newTotal += Number(s.price || 0);
            });
            const diff = newTotal - oldTotal;

            if (paymentCard) {
                paymentCard.classList.remove('hidden');
                if (summaryOld) summaryOld.textContent = money(oldTotal);
                if (summaryNew) summaryNew.textContent = money(newTotal);
            }

            const paymentSelectorContainer = document.getElementById('payment-method-selector-container');

            if (diff > 0) {
                if (paymentSelectorContainer) paymentSelectorContainer.classList.remove('hidden');
                if (cardBadge) {
                    cardBadge.textContent = 'Cần thanh toán thêm';
                    cardBadge.className = 'rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-xs font-black text-amber-800';
                }
                if (diffBox) diffBox.className = 'rounded-xl bg-emerald-700 p-4 text-white shadow-sm';
                if (diffBoxLabel) diffBoxLabel.textContent = 'SỐ TIỀN CẦN THANH TOÁN:';
                if (summaryDiff) summaryDiff.textContent = '+' + money(diff);

                onPaymentMethodChange();
                slotMessage.innerHTML = `Đã chọn đủ ca. <strong class="text-amber-700">Cần thanh toán thêm: +${money(diff)}</strong>.`;
            } else if (diff < 0) {
                if (paymentSelectorContainer) paymentSelectorContainer.classList.add('hidden');
                if (cardBadge) {
                    cardBadge.textContent = 'Được hoàn lại tiền';
                    cardBadge.className = 'rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800';
                }
                if (diffBox) diffBox.className = 'rounded-xl bg-emerald-600 p-4 text-white shadow-sm';
                if (diffBoxLabel) diffBoxLabel.textContent = 'ĐƯỢC HOÀN VỀ VÍ:';
                if (summaryDiff) summaryDiff.textContent = money(Math.abs(diff));

                submitButton.innerHTML = `Gửi yêu cầu (Hoàn về ví ${money(Math.abs(diff))})`;
                submitButton.className = "inline-flex justify-center rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-black text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition cursor-pointer";
                submitButton.disabled = false;

                slotMessage.innerHTML = `Đã chọn đủ ca. <strong class="text-emerald-700">Tiền thừa ${money(Math.abs(diff))} sẽ được tự động hoàn vào Ví tài khoản</strong> khi chủ sân duyệt.`;
            } else {
                if (paymentSelectorContainer) paymentSelectorContainer.classList.add('hidden');
                if (cardBadge) {
                    cardBadge.textContent = 'Giá ca không đổi';
                    cardBadge.className = 'rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700';
                }
                if (diffBox) diffBox.className = 'rounded-xl bg-slate-700 p-4 text-white shadow-sm';
                if (diffBoxLabel) diffBoxLabel.textContent = 'CHÊNH LỆCH:';
                if (summaryDiff) summaryDiff.textContent = '0đ';

                submitButton.innerHTML = `Gửi yêu cầu đổi lịch`;
                submitButton.className = "inline-flex justify-center rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-black text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition cursor-pointer";
                submitButton.disabled = false;

                slotMessage.textContent = 'Đã chọn đủ ca (Giá không đổi), có thể gửi yêu cầu.';
            }
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

        const oldItems = getSelectedOldItems();
        if (selectedNewSlotIds.length >= oldItems.length) {
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

    const userWalletBalance = {{ (float) ($userWallet->balance ?? 0) }};

    function onPaymentMethodChange(method) {
        const selectedMethod = method || document.querySelector('input[name="payment_method"]:checked')?.value || 'wallet';
        const submitBtn = document.getElementById('submit-button');

        const oldItems = getSelectedOldItems();
        const currentNewSlots = selectedNewSlots();
        let oldTotal = 0;
        let newTotal = 0;
        oldCheckboxes.filter(i => i.checked).forEach(i => {
            const parent = i.closest('label');
            const priceText = parent ? parent.querySelector('.text-emerald-700')?.textContent : '0';
            const p = parseInt((priceText || '0').replace(/[^0-9]/g, '')) || 0;
            oldTotal += p;
        });
        currentNewSlots.forEach(s => {
            newTotal += Number(s.price || 0);
        });
        const diff = newTotal - oldTotal;

        if (diff > 0) {
            if (selectedMethod === 'vnpay') {
                submitBtn.innerHTML = `🏧 Thanh toán +${money(diff)} qua VNPay`;
                submitBtn.className = "inline-flex justify-center rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-black text-white hover:bg-blue-700 shadow-md shadow-blue-600/20 transition cursor-pointer";
                submitBtn.disabled = false;
            } else {
                submitBtn.innerHTML = `💳 Thanh toán +${money(diff)} từ Ví tài khoản`;
                submitBtn.className = "inline-flex justify-center rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-black text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition cursor-pointer";
                submitBtn.disabled = false;
            }
        }
    }

    function handleRescheduleSubmit(e) {
        e.preventDefault();

        const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'wallet';
        const oldItems = getSelectedOldItems();
        const currentNewSlots = selectedNewSlots();

        let oldTotal = 0;
        let newTotal = 0;
        oldCheckboxes.filter(i => i.checked).forEach(i => {
            const parent = i.closest('label');
            const priceText = parent ? parent.querySelector('.text-emerald-700')?.textContent : '0';
            const p = parseInt((priceText || '0').replace(/[^0-9]/g, '')) || 0;
            oldTotal += p;
        });
        currentNewSlots.forEach(s => {
            newTotal += Number(s.price || 0);
        });

        const diff = newTotal - oldTotal;

        if (diff > 0) {
            if (selectedMethod === 'vnpay') {
                // Thanh toán qua VNPay -> Submit form để chuyển hướng thẳng tới cổng VNPay
                document.getElementById('reschedule-form').submit();
                return;
            }

            // Thanh toán qua Ví -> Kiểm tra số dư ví
            if (userWalletBalance >= diff) {
                document.getElementById('reschedule-form').submit();
            } else {
                // Mở modal thông báo ví không đủ tiền
                document.getElementById('modal-old-price').textContent = money(oldTotal);
                document.getElementById('modal-new-price').textContent = money(newTotal);
                document.getElementById('modal-diff-price').textContent = '+' + money(diff);

                document.getElementById('wallet-sufficient-notice').classList.add('hidden');
                document.getElementById('wallet-insufficient-notice').classList.remove('hidden');
                document.getElementById('wallet-shortage-amount').textContent = money(diff - userWalletBalance);
                document.getElementById('confirm-pay-submit-btn').classList.add('hidden');

                const modal = document.getElementById('reschedulePaymentModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        } else {
            // Giá bằng hoặc rẻ hơn -> Submit trực tiếp
            document.getElementById('reschedule-form').submit();
        }
    }

    function closePaymentModal() {
        const modal = document.getElementById('reschedulePaymentModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function executeFormSubmit() {
        const btn = document.getElementById('confirm-pay-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang xử lý...';
        document.getElementById('reschedule-form').submit();
    }
</script>
@endsection
