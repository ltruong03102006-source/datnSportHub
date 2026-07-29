<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: 0.2s;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                Tổng Quan Kinh Doanh
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Quản lý cơ sở</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Quản lý gói</a>
        </div>
    </nav>

    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full" x-data="{ filterType: '{{ $period == "custom" ? "custom" : "quick" }}' }">
        

        {{-- @include('owner.partials.debt-warning') --}}
        @include('owner.partials.wallet-summary')

        <!-- Header & Filters Form -->
        <form method="GET" action="{{ route('owner.dashboard') }}" class="mb-8">
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-1">Xin chào, {{ Auth::user()->name ?? 'Chủ sân' }}</h2>
                    <p class="text-sm text-slate-500">Tùy chỉnh bộ lọc để xem thống kê chi tiết.</p>
                </div>
                
                <div class="flex flex-col lg:flex-row items-end gap-4 w-full xl:w-auto">
                    <!-- Venue Filter -->
                    <div class="w-full lg:w-48">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Cơ sở</label>
                        <select name="venue_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm py-2 px-3 outline-none border transition-colors bg-slate-50 hover:bg-white">
                            <option value="all">Tất cả cơ sở</option>
                            @foreach($allVenues as $v)
                                <option value="{{ $v->id }}" {{ $selectedVenueId == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Court Filter: chỉ bật khi đã chọn 1 cơ sở cụ thể -->
                    <div class="w-full lg:w-48">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Sân con</label>
                        <select name="court_id" id="courtSelect"
                                @disabled($courtsOfVenue->isEmpty())
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm py-2 px-3 outline-none border transition-colors bg-slate-50 hover:bg-white disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                            @if ($courtsOfVenue->isEmpty())
                                <option value="all">Chọn cơ sở trước</option>
                            @else
                                <option value="all">Tất cả sân con</option>
                                @foreach($courtsOfVenue as $court)
                                    <option value="{{ $court->id }}" {{ (string) $selectedCourtId === (string) $court->id ? 'selected' : '' }}>{{ $court->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Period Type Selection -->
                    <div class="w-full lg:w-auto">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Thời gian</label>
                        <div class="flex rounded-lg border border-slate-300 p-0.5 bg-slate-50 shadow-sm">
                            <button type="button" @click="filterType = 'quick'; $nextTick(() => { document.getElementById('periodSelect').value = 'month'; document.getElementById('filterFormBtn').click() })" 
                                    :class="filterType === 'quick' ? 'bg-white shadow text-emerald-700 font-medium' : 'text-slate-500 hover:text-slate-700'" 
                                    class="px-4 py-1.5 text-sm rounded-md transition-all">Lọc nhanh</button>
                            <button type="button" @click="filterType = 'custom'" 
                                    :class="filterType === 'custom' ? 'bg-white shadow text-emerald-700 font-medium' : 'text-slate-500 hover:text-slate-700'" 
                                    class="px-4 py-1.5 text-sm rounded-md transition-all">Tùy chọn</button>
                        </div>
                    </div>

                    <!-- Quick Filter Dropdown -->
                    <div class="w-full lg:w-32" x-show="filterType === 'quick'">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">&nbsp;</label>
                        <select name="period" id="periodSelect" onchange="this.form.submit()" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm py-2 px-3 outline-none border transition-colors bg-slate-50 hover:bg-white">
                            <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hôm nay</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Tuần này</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Tháng này</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Năm nay</option>
                            <option value="custom" class="hidden" {{ $period == 'custom' ? 'selected' : '' }}></option>
                        </select>
                    </div>

                    <!-- Custom Date Range -->
                    <div class="flex gap-2 w-full lg:w-auto" x-show="filterType === 'custom'" style="display: none;">
                        <input type="hidden" name="period" value="custom" :disabled="filterType !== 'custom'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Từ ngày</label>
                            <input type="date" name="start_date" value="{{ $customStart }}" class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 border outline-none focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Đến ngày</label>
                            <input type="date" name="end_date" value="{{ $customEnd }}" class="w-full rounded-lg border-slate-300 text-sm py-2 px-3 border outline-none focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="filterFormBtn" class="px-5 py-2.2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors h-[38px] flex items-center justify-center min-w-[80px]">
                        Lọc
                    </button>
                </div>
            </div>
        </form>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Revenue Card -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Tổng doanh thu</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($totalRevenue, 0, ',', '.') }} <span class="text-lg text-slate-500 font-normal">VNĐ</span></h3>
                
                <div class="mt-2 flex items-center">
                    @if($revenueChange > 0)
                        <span class="inline-flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            +{{ number_format($revenueChange, 1) }}%
                        </span>
                    @elseif($revenueChange < 0)
                        <span class="inline-flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-md">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            {{ number_format($revenueChange, 1) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-medium text-slate-500 bg-slate-50 px-2 py-1 rounded-md">0%</span>
                    @endif
                    <span class="text-xs text-slate-400 ml-2">so với kỳ trước</span>
                </div>
            </div>

            <!-- Package Revenue Card -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-violet-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l8 4v6c0 5.25-3.44 9.92-8 11-4.56-1.08-8-5.75-8-11V6l8-4zm0 3.18L6 7.24v3.36c0 3.84 2.4 7.34 6 8.44 3.6-1.1 6-4.6 6-8.44V7.24l-6-2.06z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Doanh thu gói</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($packageBookingRevenue, 0, ',', '.') }} <span class="text-lg text-slate-500 font-normal">VNĐ</span></h3>
                <p class="text-xs text-violet-600 mt-2 font-medium bg-violet-50 inline-block px-2 py-1 rounded-md">Từ booking phát sinh từ gói</p>
            </div>

            <!-- Total Bookings Card -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Tổng lượt đặt</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $totalBookings }}</h3>
                <p class="text-xs text-blue-600 mt-2 font-medium bg-blue-50 inline-block px-2 py-1 rounded-md">
                    {{ $bookingStatuses['completed'] ?? 0 }} lượt hoàn tất
                </p>
            </div>

            <!-- Occupancy & Hours Card -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Tỷ lệ lấp đầy</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{ number_format($occupancyRate, 1) }}<span class="text-lg text-slate-500 font-normal">%</span></h3>
                    </div>
                </div>
                <p class="text-xs text-indigo-600 mt-2 font-medium bg-indigo-50 inline-block px-2 py-1 rounded-md">Đã thuê: {{ number_format($totalHours, 1) }} giờ</p>
            </div>

            <!-- Unique Customers Card -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-orange-600" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Khách hàng mới</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $uniqueCustomers }}</h3>
                <p class="text-xs text-orange-600 mt-2 font-medium bg-orange-50 inline-block px-2 py-1 rounded-md">Khách hàng riêng biệt</p>
            </div>
        </div>

        <!-- Charts & Peak Hours Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Revenue Line Chart -->
            <div class="glass-card rounded-2xl p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Biểu đồ doanh thu</h3>
                <div class="relative h-72 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Peak Hours Ranking -->
            <div class="glass-card rounded-2xl p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Top khung giờ "hot"</h3>
                <p class="text-xs text-slate-500 mb-4">Các khung giờ được đặt nhiều nhất.</p>
                
                <div class="flex-1 overflow-y-auto pr-2" style="max-height: 280px;">
                    @forelse($peakHours->take(5) as $time => $count)
                        <div class="flex items-center justify-between mb-3 p-3 rounded-lg bg-slate-50 border border-slate-100 hover:border-indigo-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                    {{ $time }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Giờ vàng</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-indigo-600">{{ $count }}</span>
                                <span class="text-xs text-slate-500 ml-1">lượt đặt</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-8 text-sm">Chưa có dữ liệu.</div>
                    @endforelse
                </div>
            </div>
            
            <!-- Booking Status Chart -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Tỷ lệ trạng thái</h3>
                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Peak Hours Bar Chart -->
            <div class="glass-card rounded-2xl p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Mật độ khung giờ đặt sân</h3>
                <div class="relative h-64 w-full">
                    <canvas id="peakChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Top Venues Table -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Cơ sở hiệu quả nhất</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-lg">Tên cơ sở</th>
                                <th class="px-4 py-3 text-center">Lượt đặt</th>
                                <th class="px-4 py-3 text-right rounded-tr-lg">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topVenues as $venue)
                                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $venue['name'] }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">
                                        <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $venue['bookings_count'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ number_format($venue['revenue'], 0, ',', '.') }} đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500">Chưa có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Customers Table -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Khách hàng VIP</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-lg">Khách hàng</th>
                                <th class="px-4 py-3 text-center">Lượt đặt</th>
                                <th class="px-4 py-3 text-right rounded-tr-lg">Đã chi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">{{ $customer['name'] }}</div>
                                        <div class="text-xs text-slate-400">{{ $customer['email'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-500">
                                        <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $customer['bookings_count'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ number_format($customer['revenue'], 0, ',', '.') }} đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500">Chưa có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart Setup Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);

            // 1. Revenue Chart
            const revCtx = document.getElementById('revenueChart').getContext('2d');
            let gradient = revCtx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); 
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: chartData.revenueDates,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
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
                        tension: 0.4 
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false, drawBorder: false }, ticks: { font: { family: 'Inter' }, color: '#64748b' } },
                        y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'Inter' }, color: '#64748b', callback: function(value) { return value >= 1000 ? (value/1000) + 'k' : value; } } }
                    }
                }
            });

            // 2. Status Doughnut Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.statusLabels,
                    datasets: [{
                        data: chartData.statusValues,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, font: { family: 'Inter', size: 12 }, color: '#475569' } } } }
            });

            // 3. Peak Hours Bar Chart
            const peakCtx = document.getElementById('peakChart').getContext('2d');
            new Chart(peakCtx, {
                type: 'bar',
                data: {
                    labels: chartData.peakHourLabels,
                    datasets: [{
                        label: 'Số lượt đặt',
                        data: chartData.peakHourValues,
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false, drawBorder: false }, ticks: { font: { family: 'Inter' }, color: '#64748b' } }, y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'Inter' }, color: '#64748b', stepSize: 1 } } }
                }
            });
        });
    </script>
    @include('owner.partials.notification-script')
</body>
</html>
