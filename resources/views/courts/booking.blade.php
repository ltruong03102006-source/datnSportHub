@extends('layouts.app')

@section('title', $court->name . ' - Đặt sân | SportHub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    
    <!-- Hero Section: Clean Profile Card Style -->
    <div class="mb-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-col p-6 sm:p-8">
            <div class="mb-3 flex flex-wrap items-center gap-3">
                @if($court->venue?->sport)
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                        {{ $court->venue->sport->name }}
                    </span>
                @endif
                @if($court->status === 'active')
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-stone-50 px-2.5 py-1 text-xs font-semibold text-stone-600 ring-1 ring-inset ring-stone-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Đang hoạt động
                    </span>
                @endif
            </div>

            <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl">{{ $court->name }}</h1>
            
            <p class="mb-6 flex items-start gap-2 text-sm text-zinc-500">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                <span>{{ $court->venue?->address ?? 'Chưa cập nhật địa chỉ' }}</span>
            </p>

           
        </div>
    </div>

    <!-- Main Booking Layout -->
    <div class="grid gap-8 lg:grid-cols-12 pb-32">
        
        <!-- Sidebar: Date & Legend (Col span 4) -->
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="sticky top-24 space-y-6">
                <!-- Date Picker Card -->
                <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <label for="datePicker" class="mb-2.5 block text-sm font-bold text-zinc-900">Chọn ngày đá</label>
                        <div class="relative">
                            <input type="date" id="datePicker" class="w-full cursor-pointer rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-sm font-medium text-zinc-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                                   value="{{ now()->toDateString() }}"
                                   min="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    <div class="rounded-xl bg-stone-100/70 p-4 text-center">
                        <p class="text-xs font-medium uppercase tracking-widest text-stone-500 mb-1">Đang chọn</p>
                        <p id="selectedDateDisplay" class="text-base font-bold text-emerald-700">
                            {{ now()->locale('vi')->format('l, d/m/Y') }}
                        </p>
                    </div>
                </div>

                <!-- Legend Card -->
               <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm hidden sm:block">
                    <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-zinc-400">Chú thích trạng thái</h4>
                    <div class="space-y-4 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-white border-2 border-emerald-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            <span class="font-medium text-zinc-700">Giờ thường</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-orange-50 border border-orange-300">
                                <span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span>
                            </span>
                            <span class="font-medium text-orange-700">Giờ cao điểm</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-rose-50 border border-rose-200">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            </span>
                            <span class="font-medium text-zinc-700">Đã đặt</span>
                        </div>
                        <div class="flex items-center gap-3 opacity-70">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-slate-100 border border-slate-200">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            </span>
                            <span class="font-medium text-slate-500">Khóa / Quá giờ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Slots (Col span 8) -->
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="rounded-2xl border border-stone-200 bg-white p-1 sm:p-6 shadow-sm">
                
                <div class="mb-6 hidden sm:flex items-center justify-between border-b border-stone-100 pb-4">
                    <h3 class="text-xl font-bold text-zinc-900">Danh sách ca trống</h3>
                    <p class="text-sm font-medium text-zinc-500">Click vào thẻ để chọn ca</p>
                </div>

                <div class="p-3 sm:p-0">
                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" class="flex flex-col items-center justify-center py-20" style="display: none;">
                        <svg class="mb-4 h-10 w-10 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <p class="text-sm font-medium text-zinc-500 tracking-wide animate-pulse">Đang đồng bộ dữ liệu...</p>
                    </div>

                    <!-- Slots Grid -->
                    <div id="slotsList" style="display: none;">
                        <div class="grid gap-3.5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3" id="slotsContainer">
                            <!-- JS Will Render Cards Here -->
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="py-20 text-center" style="display: none;">
                        <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-stone-100">
                            <svg class="h-8 w-8 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <h4 class="mb-2 text-lg font-bold text-zinc-900">Không có ca trống</h4>
                        <p class="text-sm text-zinc-500">Ngày này đã kín lịch. Vui lòng chọn ngày khác.</p>
                    </div>

                    <!-- Error State -->
                    <div id="errorState" class="rounded-xl border border-red-200 bg-red-50 p-6 text-center" style="display: none;">
                        <p class="font-bold text-red-700 mb-1">Lỗi kết nối</p>
                        <p class="text-sm text-red-600" id="errorMessage">Vui lòng tải lại trang hoặc thử lại sau.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form -->
    <form id="bookingForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="court_id" id="courtIdInput" value="{{ $court->id }}">
        <input type="hidden" name="slot_ids" id="slotIdInput">
        <input type="hidden" name="selected_date" id="selectedDateInput">
    </form>
</div>

<!-- MỞ RỘNG: FLOATING SUMMARY BAR (APPLE STYLE) -->
<div id="bookingSummary" class="fixed bottom-6 left-1/2 z-40 w-[calc(100%-2rem)] max-w-4xl -translate-x-1/2 rounded-2xl border border-stone-200/80 bg-white/85 p-3 shadow-2xl backdrop-blur-xl sm:p-4 transition-all duration-300 transform translate-y-0" style="display: none;">
    <div class="flex items-center justify-between gap-4">
        <div class="flex-1 pl-2 sm:pl-4">
            <p class="text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1 hidden sm:block">Đang chọn</p>
            <p id="summaryText" class="text-sm sm:text-lg font-bold text-zinc-900"></p>
        </div>
        
        <div class="hidden sm:block border-l border-stone-200/60 pl-6 pr-4 text-right">
            <p class="text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Tổng tiền</p>
            <p id="summaryPrice" class="text-xl font-black text-emerald-600">0₫</p>
        </div>
        
        <button id="btnSubmitBooking" onclick="submitBooking()" class="shrink-0 rounded-xl bg-zinc-900 px-6 py-3 sm:px-8 text-sm font-bold text-white shadow-md shadow-zinc-900/20 transition hover:bg-emerald-600 hover:shadow-emerald-600/30 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 active:scale-95">
            Xác nhận ngay
        </button>
    </div>
</div>

<!-- Toast -->
<div id="toastContainer" class="fixed bottom-32 right-4 sm:right-8 z-50 pointer-events-none">
    <div id="toast" class="flex items-center gap-3 rounded-xl bg-zinc-900 px-5 py-4 text-sm font-medium text-white shadow-2xl transition-all duration-300 transform translate-y-4 opacity-0">
        <svg class="h-5 w-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
        <span id="toastMessage">Thành công</span>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const courtId = {{ $court->id }};
    const datePicker = document.getElementById('datePicker');
    const selectedDateDisplay = document.getElementById('selectedDateDisplay');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const slotsList = document.getElementById('slotsList');
    const slotsContainer = document.getElementById('slotsContainer');
    const emptyState = document.getElementById('emptyState');
    const errorState = document.getElementById('errorState');
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');

    let selectedSlots = [];

    datePicker.addEventListener('change', (e) => {
        selectedSlots = []; 
        updateSummaryUI(); 
        updateDateDisplay(e.target.value);
        fetchAvailability(e.target.value);
    });

    function updateDateDisplay(dateString) {
        const date = new Date(dateString + 'T00:00:00');
        const options = { weekday: 'long', year: 'numeric', month: '2-digit', day: '2-digit' };
        const formatted = date.toLocaleDateString('vi-VN', options);
        selectedDateDisplay.textContent = formatted.charAt(0).toUpperCase() + formatted.slice(1);
        document.getElementById('selectedDateInput').value = dateString;
    }

    function fetchAvailability(dateString) {
        showLoading(true);
        errorState.style.display = 'none';

        fetch(`/api/courts/${courtId}/availability?date=${dateString}`)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                renderSlots(data.data || []);
                showLoading(false);
            })
            .catch(error => {
                showError('Không thể tải dữ liệu. Vui lòng thử lại.');
                showLoading(false);
            });
    }

    function renderSlots(slots) {
        slotsContainer.innerHTML = '';
        if (slots.length === 0) {
            slotsList.style.display = 'none';
            emptyState.style.display = 'block';
            return;
        }
        slotsList.style.display = 'block';
        emptyState.style.display = 'none';

        slots.forEach(slot => {
            slotsContainer.appendChild(createSlotCard(slot));
        });
    }

    function createSlotCard(slot) {
        const div = document.createElement('div');
        const slotDataStr = encodeURIComponent(JSON.stringify(slot));

        // Base class cho toàn bộ thẻ
        div.className = `slot-card relative flex flex-col justify-between overflow-hidden rounded-2xl border p-4 transition-all duration-200 ease-out outline-none select-none`;
        div.dataset.id = slot.slot_id;

        if (!slot.is_available) {
            if (slot.is_booked) {
                // TRẠNG THÁI 1: ĐÃ ĐẶT (Màu Đỏ Hồng - Rose)
                div.classList.add('border-rose-200', 'bg-rose-50/60', 'pointer-events-none');
                div.innerHTML = `
                    <div class="mb-3 flex items-start justify-between opacity-70">
                        <div>
                            <h4 class="text-sm font-bold text-rose-900 line-through decoration-rose-400">${slot.start_time} - ${slot.end_time}</h4>
                        </div>
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 border border-rose-200">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        </div>
                    </div>
                    <div class="flex items-end justify-between opacity-70">
                        <p class="text-base font-bold text-rose-900">${parseInt(slot.price).toLocaleString('vi-VN')}₫</p>
                        <span class="rounded-md bg-rose-100 border border-rose-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-600">Đã đặt</span>
                    </div>
                `;
            } else {
                // TRẠNG THÁI 2: KHÓA / QUÁ GIỜ (Màu Xám - Slate)
                div.classList.add('border-slate-200', 'bg-slate-100/50', 'pointer-events-none');
                div.innerHTML = `
                    <div class="mb-3 flex items-start justify-between opacity-60">
                        <div>
                            <h4 class="text-sm font-medium text-slate-500 line-through decoration-slate-300">${slot.start_time} - ${slot.end_time}</h4>
                        </div>
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-200 border border-slate-300">
                            <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        </div>
                    </div>
                    <div class="flex items-end justify-between opacity-60">
                        <p class="text-base font-medium text-slate-500">${parseInt(slot.price).toLocaleString('vi-VN')}₫</p>
                        <span class="rounded-md bg-slate-200 border border-slate-300 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">Khóa</span>
                    </div>
                `;
            }
        } else {
            // CÁC TRẠNG THÁI CÓ THỂ ĐẶT (Cho phép click chọn)
            div.classList.add('cursor-pointer');
            div.setAttribute('onclick', `toggleSlot('${slotDataStr}')`);

            if (slot.price_type === 'peak') {
                // TRẠNG THÁI 3: CAO ĐIỂM (Màu Cam - Orange)
                div.classList.add('border-orange-200', 'bg-orange-50/50', 'hover:border-orange-400', 'hover:-translate-y-0.5', 'hover:shadow-md');
                div.innerHTML = `
                    <div class="mb-2 flex items-start justify-between">
                        <div>
                            <h4 class="slot-time text-sm font-bold text-orange-900 transition-colors">${slot.start_time} - ${slot.end_time}</h4>
                            <span class="mt-1 inline-block text-[10px] font-bold text-orange-700 bg-orange-100 px-1.5 py-0.5 rounded uppercase tracking-wider">Cao điểm</span>
                        </div>
                        <div class="slot-checkbox flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-orange-300 bg-white transition-all duration-200"></div>
                    </div>
                    <div class="flex items-end justify-between">
                        <p class="slot-price text-base font-black text-orange-600 transition-colors">${parseInt(slot.price).toLocaleString('vi-VN')}₫</p>
                        <span class="rounded-md bg-white border border-orange-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-orange-600 shadow-sm">Trống</span>
                    </div>
                `;
            } else {
                // TRẠNG THÁI 4: GIỜ THƯỜNG (Màu Xanh Lá - Emerald, thêm nhãn "Thường")
                div.classList.add('border-emerald-200', 'bg-white', 'hover:border-emerald-400', 'hover:-translate-y-0.5', 'hover:shadow-md');
                div.innerHTML = `
                    <div class="mb-2 flex items-start justify-between">
                        <div>
                            <h4 class="slot-time text-sm font-semibold text-zinc-800 transition-colors">${slot.start_time} - ${slot.end_time}</h4>
                            <span class="mt-1 inline-block text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded uppercase tracking-wider">Thường</span>
                        </div>
                        <div class="slot-checkbox flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-stone-300 bg-white transition-all duration-200"></div>
                    </div>
                    <div class="flex items-end justify-between">
                        <p class="slot-price text-base font-bold text-emerald-600 transition-colors">${parseInt(slot.price).toLocaleString('vi-VN')}₫</p>
                        <span class="rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">Trống</span>
                    </div>
                `;
            }
        }
        return div;
    }

    function toggleSlot(slotDataStr) {
        const slot = JSON.parse(decodeURIComponent(slotDataStr));
        const index = selectedSlots.findIndex(s => s.slot_id === slot.slot_id);

        if (index > -1) {
            selectedSlots.splice(index, 1);
        } else {
            selectedSlots.push(slot);
        }

        selectedSlots.sort((a, b) => a.start_time.localeCompare(b.start_time));
        updateSummaryUI();
    }

    function updateSummaryUI() {
        const summaryDiv = document.getElementById('bookingSummary');
        const submitBtn = document.getElementById('btnSubmitBooking');
        const summaryText = document.getElementById('summaryText');
        const summaryPrice = document.getElementById('summaryPrice');

        document.querySelectorAll('.slot-card').forEach(card => {
            if (card.classList.contains('pointer-events-none')) return;

            const id = parseInt(card.dataset.id);
            const isSelected = selectedSlots.some(s => s.slot_id === id);
            const checkbox = card.querySelector('.slot-checkbox');

            if (isSelected) {
                // Ép thẻ thành viền và nền xanh ngọc đậm khi được chọn
                card.style.borderColor = '#10b981'; // Emerald 500
                card.style.backgroundColor = '#ecfdf5'; // Emerald 50
                card.style.transform = 'translateY(-2px)';
                card.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
                
                checkbox.style.borderColor = '#10b981';
                checkbox.style.backgroundColor = '#10b981';
                checkbox.innerHTML = `<svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>`;
            } else {
                // Bỏ chọn: Xóa style nội tuyến để thẻ tự trả về màu CSS mặc định (Cam hoặc Trắng)
                card.style.borderColor = '';
                card.style.backgroundColor = '';
                card.style.transform = '';
                card.style.boxShadow = '';
                
                checkbox.style.borderColor = '';
                checkbox.style.backgroundColor = '';
                checkbox.innerHTML = '';
            }
        });

        // Xử lý Thanh Bottom Summary Bar
        if (selectedSlots.length > 0) {
            summaryDiv.style.display = 'block';
            
            const totalMins = selectedSlots.reduce((sum, s) => sum + parseInt(s.duration_minutes), 0);
            const totalPrice = selectedSlots.reduce((sum, s) => sum + parseInt(s.price), 0);

            summaryText.innerHTML = `<span class="text-emerald-600">${totalMins} phút</span> <span class="text-stone-400 font-medium ml-1">(${selectedSlots.length} ca)</span>`;
            summaryPrice.textContent = totalPrice.toLocaleString('vi-VN') + '₫';

            submitBtn.disabled = false;
        } else {
            summaryDiv.style.display = 'none';
        }
    }

    async function submitBooking() {
        const submitBtn = document.getElementById('btnSubmitBooking');
        if (submitBtn.disabled || selectedSlots.length === 0) return;

        const token = localStorage.getItem('sporthub_token');
        if (!token) {
            showToast('Vui lòng đăng nhập để đặt sân.');
            setTimeout(() => {
                window.location.href = '{{ route('login') }}';
            }, 800);
            return;
        }

        const selectedDate = datePicker.value;
        const slotIds = selectedSlots.map(s => s.slot_id);

        document.getElementById('slotIdInput').value = JSON.stringify(slotIds);
        document.getElementById('selectedDateInput').value = selectedDate;

        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang tạo đơn...';
        showToast('Đang tạo đơn đặt sân...');

        try {
            const response = await fetch('{{ url('/api/bookings') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({
                    court_id: courtId,
                    slot_date: selectedDate,
                    slots: selectedSlots.map(slot => ({
                        start_time: slot.start_time,
                        end_time: slot.end_time,
                    })),
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 401) {
                    localStorage.removeItem('sporthub_token');
                    localStorage.removeItem('sporthub_user');
                    showToast('Phiên đăng nhập đã hết hạn.');
                    setTimeout(() => {
                        window.location.href = '{{ route('login') }}';
                    }, 800);
                    return;
                }

                showToast(data.message || 'Không thể tạo đơn đặt sân.');
                return;
            }

            const bookings = Array.isArray(data.data) ? data.data : [data.data];
            const firstBooking = bookings.find(Boolean);

            if (!firstBooking?.id) {
                showToast('Đã tạo đơn nhưng không tìm thấy mã booking.');
                return;
            }

            const successUrl = @json(route('web.bookings.success', ['booking' => '__BOOKING_ID__']));
            window.location.href = successUrl.replace('__BOOKING_ID__', firstBooking.id);
        } catch (error) {
            showToast('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    function showLoading(show) { 
        loadingSpinner.style.display = show ? 'flex' : 'none';
        if (show) { slotsList.style.display = 'none'; emptyState.style.display = 'none'; }
    }

    function showError(message) { 
        document.getElementById('errorMessage').textContent = message; 
        errorState.style.display = 'block';
        emptyState.style.display = 'none'; 
        slotsList.style.display = 'none';
    }

    function showToast(message) { 
        toastMessage.textContent = message; 
        const toastEl = document.getElementById('toast');
        toastEl.classList.remove('opacity-0', 'translate-y-4');
        toastEl.classList.add('opacity-100', 'translate-y-0');
        setTimeout(() => {
            toastEl.classList.add('opacity-0', 'translate-y-4');
            toastEl.classList.remove('opacity-100', 'translate-y-0');
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateDateDisplay(datePicker.value);
        fetchAvailability(datePicker.value);
    });
</script>
@endsection
