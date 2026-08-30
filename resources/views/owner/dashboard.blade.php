@extends('owner.layoutOwner.app')

@section('title', 'Tổng quan kinh doanh')

@section('content')

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

<!-- Tích hợp Font Inter cho dữ liệu hiển thị sắc nét -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; }
    
    .saas-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .saas-card:hover {
        box-shadow: 0 10px 25px -3px rgba(15, 23, 42, 0.08);
    }
    
    .filter-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
    }

    /* Ẩn thanh cuộn cho bảng */
    .hide-scroll::-webkit-scrollbar { height: 6px; }
    .hide-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .hide-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .hide-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

@php
    // GOM DỮ LIỆU THÔNG MINH BẰNG PHP BLADE (Không chạm vào Controller)
    $totalBookings = $singleBookingsCount + $packageBookingsCount;
    $totalCompleted = $singleBookingsCompletedCount + $packageBookingsCompletedCount;
    $failedBookings = $totalBookings - $totalCompleted;
    $completionRate = $totalBookings > 0 ? ($totalCompleted / $totalBookings) * 100 : 0;
@endphp

<div class="flex-1 p-4 lg:p-8 max-w-[1600px] mx-auto w-full bg-slate-50/50 min-h-screen" x-data="{ filterType: '{{ $period == "custom" ? "custom" : "quick" }}' }">

    @include('owner.partials.wallet-summary')

    <!-- BỘ LỌC ĐIỀU HƯỚNG (CONTROL PANEL) -->
    <!-- BỘ LỌC ĐIỀU HƯỚNG (CONTROL PANEL) -->
    <form method="GET" action="{{ route('owner.dashboard') }}" class="mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col 2xl:flex-row justify-between items-start 2xl:items-center gap-6">
            
            <!-- Phần Tiêu đề -->
            <div class="shrink-0">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tổng quan kinh doanh</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">
                    Theo dõi hiệu suất hoạt động của các cơ sở thể thao.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full 2xl:w-auto">
                
                <!-- NHÓM 1: Lọc Cơ sở + Sân con (Luôn đi liền nhau) -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full lg:w-auto">
                    <!-- Lọc Cơ sở -->
                    <div class="relative w-full sm:w-48 shrink-0">
                        <select name="venue_id" class="filter-select w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50 py-2.5 pl-4 pr-10 outline-none transition-all cursor-pointer hover:bg-slate-100">
                            <option value="all">🏢 Tất cả cơ sở</option>
                            @foreach($allVenues as $v)
                                <option value="{{ $v->id }}" {{ $selectedVenueId == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lọc Sân con -->
                    <div class="relative w-full sm:w-44 shrink-0">
                        <select name="court_id" id="courtSelect" @disabled($courtsOfVenue->isEmpty())
                                class="filter-select w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50 py-2.5 pl-4 pr-10 outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer hover:bg-slate-100">
                            @if ($courtsOfVenue->isEmpty())
                                <option value="all">📍 Chọn cơ sở trước</option>
                            @else
                                <option value="all">📍 Tất cả sân con</option>
                                @foreach($courtsOfVenue as $court)
                                    <option value="{{ $court->id }}" {{ (string) $selectedCourtId === (string) $court->id ? 'selected' : '' }}>{{ $court->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <!-- NHÓM 2: Lọc Thời gian + Nút Lọc (Gom chung để nút không bị rớt lẻ loi) -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full lg:w-auto">
                    <!-- Tab Chọn kiểu thời gian -->
                    <div class="flex bg-slate-100 p-1 rounded-xl shrink-0">
                        <button type="button" @click="filterType = 'quick'"
                                :class="filterType === 'quick' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                                class="px-4 py-1.5 text-sm rounded-lg transition-all">Bộ lọc</button>
                        <button type="button" @click="filterType = 'custom'"
                                :class="filterType === 'custom' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                                class="px-4 py-1.5 text-sm rounded-lg transition-all">Tùy chỉnh</button>
                    </div>

                    <!-- Select Lọc nhanh -->
                    <div x-show="filterType === 'quick'" class="w-full sm:w-36 shrink-0">
                        <select name="period" id="periodSelect" onchange="this.form.submit()" class="filter-select w-full rounded-xl border-slate-200 text-sm font-semibold text-emerald-700 bg-emerald-50 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 pl-4 pr-10 outline-none cursor-pointer">
                            <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hôm nay</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Tuần này</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Tháng này</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Năm nay</option>
                            <option value="custom" class="hidden" {{ $period == 'custom' ? 'selected' : '' }}></option>
                        </select>
                    </div>

                    <!-- Date Picker (Tùy chỉnh) - Đã khóa width cố định để không bị phình to -->
                    <div x-show="filterType === 'custom'" x-cloak class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                        <input type="hidden" name="period" value="custom" :disabled="filterType !== 'custom'">
                        <input type="date" name="start_date" value="{{ $customStart }}" class="rounded-xl border-slate-200 text-sm font-medium py-2 px-3 outline-none focus:border-emerald-500 focus:ring-emerald-500 w-[130px]">
                        <span class="text-slate-400 font-medium">-</span>
                        <input type="date" name="end_date" value="{{ $customEnd }}" class="rounded-xl border-slate-200 text-sm font-medium py-2 px-3 outline-none focus:border-emerald-500 focus:ring-emerald-500 w-[130px]">
                    </div>

                    <!-- Nút lọc (Luôn dính liền với Nhóm 2) -->
                    <button type="submit" id="filterFormBtn" class="px-5 py-2.5 text-sm font-bold text-white bg-slate-900 hover:bg-emerald-600 rounded-xl shadow-sm transition-all h-[42px] shrink-0 w-full sm:w-auto">
                        Lọc dữ liệu
                    </button>
                </div>

            </div>
        </div>
    </form>
    <!-- 3 THẺ CHỈ SỐ CỐT LÕI (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- KPI 1: Doanh Thu -->
        <div class="saas-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tổng Doanh Thu</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalRevenue, 0, ',', '.') }}<span class="text-base text-slate-400 font-semibold ml-1">đ</span></h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($revenueChange > 0)
                        <span class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            +{{ number_format($revenueChange, 1) }}%
                        </span>
                    @elseif($revenueChange < 0)
                        <span class="inline-flex items-center text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-md">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            {{ number_format($revenueChange, 1) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">0%</span>
                    @endif
                    <span class="text-xs font-medium text-slate-400 ml-2">so với kỳ trước</span>
                </div>
                <!-- Thông tin phụ: Doanh thu Đặt lẻ & Đặt gói -->
                <div class="text-[10px] font-bold text-slate-400 uppercase text-right leading-tight" title="Doanh thu từ Đặt lẻ & Đặt gói">
                    <div>Lẻ: {{ number_format($singleBookingRevenue ?? ($totalRevenue - $packageBookingRevenue), 0, ',', '.') }}đ</div>
                    <div>Gói: {{ number_format($packageBookingRevenue, 0, ',', '.') }}đ</div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Tỷ lệ lấp đầy & Thời lượng -->
        <div class="saas-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tỷ Lệ Lấp Đầy</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($occupancyRate, 1) }}<span class="text-lg text-slate-400 font-semibold">%</span></h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="text-xs font-semibold text-slate-500">Tổng thời gian cho thuê</span>
                <span class="text-sm font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">{{ number_format($totalHours, 1) }} giờ</span>
            </div>
        </div>

        <!-- KPI 4: Khách hàng -->
        <div class="saas-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Khách Hàng</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ $uniqueCustomers }}</h3>
                        <span class="text-sm font-semibold text-slate-400">người</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="text-xs font-semibold text-slate-500">Khách phát sinh giao dịch</span>
                <span class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">Unique</span>
            </div>
        </div>
    </div>

    <!-- WIDGET PHÂN TÍCH CA LẺ VS ĐẶT GÓI -->
    @php
        $singleRevenueRatio = $totalRevenue > 0 ? min(100, round(($singleBookingRevenue / $totalRevenue) * 100, 1)) : 0;
        $packageRevenueRatio = $totalRevenue > 0 ? min(100, round(($packageBookingRevenue / $totalRevenue) * 100, 1)) : 0;
        $singleCountRatio = $totalBookings > 0 ? min(100, round(($singleBookingsCount / $totalBookings) * 100, 1)) : 0;
        $packageCountRatio = $totalBookings > 0 ? min(100, round(($packageBookingsCount / $totalBookings) * 100, 1)) : 0;
    @endphp

    <div class="saas-card p-6 mb-8 bg-gradient-to-br from-white to-slate-50/50">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    Phân Tích Chi Tiết: Ca Lẻ vs Đặt Gói
                </h3>
                <p class="text-xs font-medium text-slate-500 mt-1">So sánh hiệu quả khai thác và doanh thu giữa hình thức đặt lẻ trực tiếp và theo gói hội viên.</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Đặt Ca Lẻ
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Đặt Theo Gói
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cột 1: Phân khúc Ca Lẻ -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">Ca Lẻ Trực Tiếp</span>
                        <span class="text-xs font-bold text-slate-400">Chiếm {{ $singleRevenueRatio }}% Doanh thu</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase">Doanh Thu Lẻ</p>
                            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($singleBookingRevenue, 0, ',', '.') }}<span class="text-sm text-slate-400 ml-1">đ</span></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase">Tổng Lượt Đặt Lẻ</p>
                            <p class="text-2xl font-black text-slate-900 mt-1">{{ $singleBookingsCount }} <span class="text-xs text-slate-400 font-bold">lượt</span></p>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-100 pt-3 flex flex-wrap justify-between items-center gap-2 text-xs">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="font-semibold text-slate-500">Hoàn tất: <strong class="text-emerald-700">{{ $singleBookingsCompletedCount }} ca</strong></span>
                        <span class="font-semibold text-blue-600">Chưa đá: <strong>{{ $singleBookingsUpcomingCount }} ca</strong></span>
                        <span class="font-semibold text-rose-500">Hủy: <strong>{{ $singleBookingsCancelledCount }} ca</strong></span>
                    </div>
                    <span class="font-semibold text-slate-400">Tỷ lệ lượt đặt: {{ $singleCountRatio }}%</span>
                </div>
            </div>

            <!-- Cột 2: Phân khúc Đặt Gói -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg">Đặt Theo Gói</span>
                        <span class="text-xs font-bold text-slate-400">Chiếm {{ $packageRevenueRatio }}% Doanh thu</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase">Doanh Thu Gói</p>
                            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($packageBookingRevenue, 0, ',', '.') }}<span class="text-sm text-slate-400 ml-1">đ</span></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-indigo-600 uppercase">Số Gói Đã Đặt</p>
                            <p class="text-2xl font-black text-indigo-700 mt-1">{{ $purchasedPackagesCount }} <span class="text-xs text-indigo-500 font-bold">gói</span></p>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-100 pt-3 flex flex-wrap justify-between items-center gap-2 text-xs">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="font-semibold text-slate-500">Hoàn tất: <strong class="text-indigo-700">{{ $packageBookingsCompletedCount }} ca</strong></span>
                        <span class="font-semibold text-blue-600">Chưa đá: <strong>{{ $packageBookingsUpcomingCount }} ca</strong></span>
                        <!-- <span class="font-semibold text-rose-500">Hủy: <strong>{{ $packageBookingsCancelledCount }} ca</strong></span> -->
                    </div>
                    <span class="font-semibold text-slate-400">Tỷ lệ lượt đặt: {{ $packageCountRatio }}%</span>
                </div>
            </div>
        </div>

        <!-- Thanh Tỷ Lệ Đóng Góp Doanh Thu -->
        <div class="mt-6">
            <div class="flex justify-between text-xs font-bold text-slate-500 mb-2">
                <span>Tỷ trọng đóng góp Doanh thu</span>
                <span>Lẻ {{ $singleRevenueRatio }}% • Gói {{ $packageRevenueRatio }}%</span>
            </div>
            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden flex">
                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $singleRevenueRatio }}%" title="Doanh thu lẻ: {{ $singleRevenueRatio }}%"></div>
                <div class="bg-indigo-500 h-full transition-all duration-500" style="width: {{ $packageRevenueRatio }}%" title="Doanh thu gói: {{ $packageRevenueRatio }}%"></div>
            </div>
        </div>
    </div>

    <!-- KHU VỰC BIỂU ĐỒ (Row 1) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Biểu đồ Doanh thu (Line Chart) -->
        <div class="saas-card p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Xu hướng Doanh thu</h3>
                    <p class="text-xs font-medium text-slate-500">Thống kê theo dòng thời gian bạn đã chọn.</p>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Biểu đồ Trạng thái (Doughnut) -->
        <div class="saas-card p-6 flex flex-col">
            <h3 class="text-base font-bold text-slate-900 mb-1">Tỷ lệ Trạng thái</h3>
            <p class="text-xs font-medium text-slate-500 mb-6">Mức độ hoàn thành của các booking.</p>
            <div class="relative flex-1 w-full min-h-[220px] flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- KHU VỰC BẢNG THỐNG KÊ CHI TIẾT (Row 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- Top Cơ sở (Venues) -->
        <div class="saas-card p-0 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-900">🏆 Cơ Sở Hiệu Quả Nhất</h3>
            </div>
            <div class="overflow-x-auto hide-scroll flex-1">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-slate-400 uppercase tracking-wider bg-white">
                        <tr>
                            <th class="px-6 py-4 font-bold">Tên cơ sở</th>
                            <th class="px-6 py-4 font-bold text-center">Lượt đặt lẻ</th>
                            <th class="px-6 py-4 font-bold text-center">Đặt theo gói</th>
                            <th class="px-6 py-4 font-bold text-right">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($topVenues as $venue)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $venue['name'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-bold group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">{{ $venue['single_bookings_count'] ?? 0 }} ca</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(($venue['purchased_packages_count'] ?? 0) > 0)
                                        <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-md text-xs font-bold">{{ $venue['purchased_packages_count'] }} gói</span>
                                    @else
                                        <span class="text-slate-400 font-semibold text-xs">Không</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="font-black text-emerald-600">{{ number_format($venue['revenue'], 0, ',', '.') }} đ</div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Lẻ: {{ number_format($venue['single_revenue'] ?? 0, 0, ',', '.') }}đ • Gói: {{ number_format($venue['package_revenue'] ?? 0, 0, ',', '.') }}đ</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500 text-sm font-medium">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Khách hàng (Customers) -->
        <div class="saas-card p-0 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-900">💎 Khách Hàng Thân Thiết</h3>
            </div>
            <div class="overflow-x-auto hide-scroll flex-1">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-slate-400 uppercase tracking-wider bg-white">
                        <tr>
                            <th class="px-6 py-4 font-bold">Khách hàng</th>
                            <th class="px-6 py-4 font-bold text-center">Lượt đặt lẻ</th>
                            <th class="px-6 py-4 font-bold text-center">Đặt theo gói</th>
                            <th class="px-6 py-4 font-bold text-right">Tổng chi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($topCustomers as $customer)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-800">{{ $customer['name'] }}</div>
                                    <div class="text-xs text-slate-400 font-medium">{{ $customer['email'] }}</div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="bg-amber-50 text-amber-600 px-2.5 py-1 rounded-md text-xs font-bold">{{ $customer['single_bookings_count'] ?? 0 }} ca</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if(($customer['purchased_packages_count'] ?? 0) > 0)
                                        <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-md text-xs font-bold">{{ $customer['purchased_packages_count'] }} gói</span>
                                    @else
                                        <span class="text-slate-400 font-semibold text-xs">Không</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="font-black text-slate-800">{{ number_format($customer['revenue'], 0, ',', '.') }} đ</div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Lẻ: {{ number_format($customer['single_revenue'] ?? 0, 0, ',', '.') }}đ • Gói: {{ number_format($customer['package_revenue'] ?? 0, 0, ',', '.') }}đ</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500 text-sm font-medium">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KHU VỰC PHÂN TÍCH CHUYÊN SÂU (Deep Dive) -->
    <div class="saas-card p-0 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap justify-between items-center gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Phân tích Sân con & Khung giờ</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Đánh giá hiệu quả khai thác của từng sân thuộc cơ sở.</p>
            </div>
            @if ($selectedVenueId === 'all')
                <div class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                    <i class="fa-solid fa-circle-info mr-1"></i> Chọn 1 cơ sở để xem chi tiết
                </div>
            @endif
        </div>

        <div class="p-6">
            <!-- 2 Biểu đồ Bar Chart: Mật độ giờ & Doanh thu sân -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 border-b border-slate-100 pb-8">
                <!-- Bar chart: Peak Hours -->
                <div>
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Mật độ khung giờ đặt sân chung</h4>
                    <div class="relative h-56 w-full"><canvas id="peakChart"></canvas></div>
                </div>
                <!-- Bar chart: Court Revenue -->
                <div>
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Doanh thu so sánh giữa các sân con</h4>
                    @if ($courtStats->isNotEmpty())
                        <div class="relative h-56 w-full"><canvas id="courtRevenueChart"></canvas></div>
                    @else
                        <div class="h-56 flex items-center justify-center text-slate-400 text-sm font-medium bg-slate-50 rounded-xl border border-dashed border-slate-200">Không có dữ liệu sân con</div>
                    @endif
                </div>
            </div>

            <!-- Ma trận nhiệt (Heatmap) thiết kế chuẩn GitHub -->
            @if (!empty($courtHeatmap['rows']))
                <div class="mb-8">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <h4 class="text-sm font-bold text-slate-700">Ma trận nhiệt: Khung giờ đông khách</h4>
                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>Vắng</span>
                            <div class="flex gap-1">
                                <span class="w-3 h-3 rounded-sm bg-slate-100"></span>
                                <span class="w-3 h-3 rounded-sm bg-emerald-200"></span>
                                <span class="w-3 h-3 rounded-sm bg-emerald-400"></span>
                                <span class="w-3 h-3 rounded-sm bg-emerald-600"></span>
                            </div>
                            <span>Đông</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto hide-scroll pb-2">
                        <table class="border-separate" style="border-spacing: 2px;">
                            <thead>
                                <tr>
                                    <th class="text-left font-semibold text-slate-400 text-xs pr-4 pb-2">Sân</th>
                                    @foreach($courtHeatmap['hours'] as $hour)
                                        <th class="font-medium text-slate-400 text-[10px] text-center w-7 pb-2">{{ $hour }}h</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courtHeatmap['rows'] as $row)
                                    <tr>
                                        <td class="pr-4 whitespace-nowrap text-xs font-bold {{ (string) $selectedCourtId === (string) $row['id'] ? 'text-emerald-600' : 'text-slate-600' }}">
                                            {{ $row['name'] }}
                                        </td>
                                        @foreach($row['cells'] as $cell)
                                            <td @class([
                                                    'w-7 h-7 text-center rounded-sm text-[10px] font-bold transition-all hover:scale-110 cursor-crosshair',
                                                    'bg-slate-100 text-transparent hover:text-slate-400' => $cell['level'] === 0,
                                                    'bg-emerald-200 text-emerald-800' => $cell['level'] === 1,
                                                    'bg-emerald-400 text-white shadow-sm' => $cell['level'] === 2,
                                                    'bg-emerald-600 text-white shadow-sm' => $cell['level'] === 3,
                                                ])
                                                title="{{ $row['name'] }} | {{ str_pad($cell['hour'], 2, '0', STR_PAD_LEFT) }}:00 | {{ $cell['count'] }} lượt">
                                                {{ $cell['count'] ?: '' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Bảng thống kê chi tiết Sân con -->
            @if ($courtStats->isNotEmpty())
                <div>
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Chi tiết hiệu suất khai thác</h4>
                    <div class="overflow-x-auto hide-scroll border border-slate-200 rounded-xl">
                        <table class="w-full text-sm text-left">
                            <thead class="text-[10px] text-slate-500 uppercase tracking-wider bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 font-bold">Sân con</th>
                                    <th class="px-4 py-3 font-bold text-center">Lượt đặt</th>
                                    <th class="px-4 py-3 font-bold text-center">Khách</th>
                                    <th class="px-4 py-3 font-bold text-center">Giờ đặt</th>
                                    <th class="px-4 py-3 font-bold text-center min-w-[120px]">Lấp đầy</th>
                                    <th class="px-4 py-3 font-bold text-center">Giờ vàng</th>
                                    <th class="px-4 py-3 font-bold text-right">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($courtStats as $court)
                                    @php($isSelectedCourt = (string) $selectedCourtId === (string) $court['id'])
                                    <tr class="transition-colors {{ $isSelectedCourt ? 'bg-emerald-50/50' : 'hover:bg-slate-50' }}">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-800">
                                                {{ $court['name'] }}
                                                @if ($isSelectedCourt)
                                                    <span class="ml-2 align-middle text-[9px] font-black uppercase text-white bg-emerald-500 px-1.5 py-0.5 rounded">Active</span>
                                                @endif
                                            </div>
                                            @if ($court['status'] !== 'active')
                                                <span class="text-[10px] font-semibold text-rose-500">Tạm ẩn</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="font-bold text-slate-700">{{ $court['bookings_count'] }}</div>
                                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $court['single_bookings_count'] ?? 0 }} Lẻ • {{ $court['package_bookings_count'] ?? 0 }} Gói</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-600">{{ $court['customers_count'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-600">{{ $court['hours'] }}h</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <span class="text-xs font-bold text-slate-700 w-8 text-right">{{ $court['occupancy_rate'] }}%</span>
                                                <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ min(100, $court['occupancy_rate']) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-xs font-medium text-slate-500">
                                            {{ $court['peak_hours'] ? implode(', ', $court['peak_hours']) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="font-black text-emerald-600">{{ number_format($court['revenue'], 0, ',', '.') }} đ</div>
                                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Lẻ: {{ number_format($court['single_revenue'] ?? 0, 0, ',', '.') }}đ • Gói: {{ number_format($court['package_revenue'] ?? 0, 0, ',', '.') }}đ</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Script khởi tạo Chart.js giữ nguyên 100% logic để nhận đủ dữ liệu từ Backend -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cấu hình chung cho Chart.js để giao diện sạch sẽ hơn
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';
            
            const chartData = @json($chartData);

            // 1. Revenue Chart
            const revCanvas = document.getElementById('revenueChart');
            if (revCanvas) {
                const revCtx = revCanvas.getContext('2d');
                let gradient = revCtx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

                new Chart(revCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.revenueDates,
                        datasets: [{
                            label: 'Doanh thu',
                            data: chartData.revenueValues,
                            borderColor: '#10b981',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#10b981',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.3 // Làm cong mượt mà
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a', titleFont: { size: 13 }, bodyFont: { size: 14, weight: 'bold' },
                                padding: 12, cornerRadius: 8, displayColors: false,
                                callbacks: { label: (context) => new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ' }
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { 
                                grid: { color: '#f1f5f9', borderDash: [5, 5] }, 
                                border: { display: false },
                                ticks: { callback: (value) => value >= 1000 ? (value/1000) + 'k' : value } 
                            }
                        }
                    }
                });
            }

            // 2. Status Doughnut Chart
            const statusCanvas = document.getElementById('statusChart');
            if (statusCanvas) {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.statusLabels,
                        datasets: [{
                            data: chartData.statusValues,
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#f43f5e'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false, cutout: '70%', 
                        plugins: { 
                            legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, boxWidth: 8, font: { weight: '600' } } } 
                        } 
                    }
                });
            }

            // 3. Peak Hours Bar Chart
            const peakCanvas = document.getElementById('peakChart');
            if (peakCanvas) {
                new Chart(peakCanvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.peakHourLabels,
                        datasets: [{
                            label: 'Lượt đặt',
                            data: chartData.peakHourValues,
                            backgroundColor: '#6366f1',
                            borderRadius: 4,
                            barPercentage: 0.5,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { 
                            x: { grid: { display: false } }, 
                            y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } } 
                        }
                    }
                });
            }

            // 4. Biểu đồ doanh thu sân con
            const courtCanvas = document.getElementById('courtRevenueChart');
            if (courtCanvas) {
                new Chart(courtCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($courtStats->pluck('name')),
                        datasets: [{
                            label: 'Doanh thu',
                            data: @json($courtStats->pluck('revenue')),
                            backgroundColor: '#14b8a6', // Màu Teal hiện đại
                            borderRadius: 4,
                            barPercentage: 0.5,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (context) => new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ' } }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { callback: (value) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) } }
                        }
                    }
                });
            }

            // 5. Logic Ajax nạp danh sách Sân con khi đổi Cơ sở (Giữ nguyên)
            const venueSelect = document.querySelector('select[name="venue_id"]');
            const courtSelect = document.getElementById('courtSelect');

            if (venueSelect && courtSelect) {
                venueSelect.addEventListener('change', async function () {
                    const venueId = this.value;
                    if (venueId === 'all') {
                        courtSelect.innerHTML = '<option value="all">📍 Chọn cơ sở trước</option>';
                        courtSelect.disabled = true;
                        return;
                    }
                    courtSelect.disabled = true;
                    courtSelect.innerHTML = '<option value="all">⏳ Đang tải...</option>';

                    try {
                        const res = await fetch(`/owner/venues/${venueId}/courts-lookup`, {
                            headers: { Accept: 'application/json' },
                        });
                        const json = await res.json();
                        const options = ['<option value="all">📍 Tất cả sân con</option>'];
                        for (const court of json.data) {
                            options.push(`<option value="${court.id}">${court.name}</option>`);
                        }
                        courtSelect.innerHTML = options.join('');
                        courtSelect.disabled = json.data.length === 0;
                    } catch (error) {
                        courtSelect.innerHTML = '<option value="all">❌ Lỗi tải dữ liệu</option>';
                    }
                });
            }
        });
    </script>
@endpush