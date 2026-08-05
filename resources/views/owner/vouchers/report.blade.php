@extends('owner.layoutOwner.app')

@section('title', 'Báo cáo hiệu quả Voucher | SportHub')

@section('content')
<div class="space-y-8 px-6 py-8">
    <!-- Header và Bộ lọc -->
    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">📊 Báo cáo hiệu quả Voucher</h1>
            <p class="mt-1.5 text-xs font-semibold text-slate-500">Phân tích chuyên sâu số liệu và biểu đồ sử dụng mã giảm giá của cơ sở.</p>
        </div>

        <form action="{{ route('owner.web.vouchers.report') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Chọn Cơ sở -->
            <div>
                <select name="venue_id" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Tất cả cơ sở</option>
                    @foreach($venues as $v)
                        <option value="{{ $v->id }}" {{ $venueId == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Chọn Tháng/Năm -->
            <div>
                <input type="month" name="month_year" value="{{ $monthYear }}" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            
            <a href="{{ route('owner.web.vouchers.index') }}" class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 text-sm font-bold transition">
                Quản lý mã
            </a>
        </form>
    </div>

    <!-- Khối KPIs -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Tổng số voucher -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Tổng số voucher</span>
                <span class="rounded-xl bg-slate-100 p-2 text-lg">🎫</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ $totalVouchers }}</span>
                <span class="text-xs font-bold text-slate-400">mã</span>
            </div>
        </div>

        <!-- Tổng lượt sử dụng -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Lượt sử dụng</span>
                <span class="rounded-xl bg-indigo-50 p-2 text-lg text-indigo-500">⚡</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ $totalUses }}</span>
                <span class="text-xs font-bold text-slate-400">lượt</span>
            </div>
        </div>

        <!-- Doanh thu từ voucher -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Doanh thu áp mã</span>
                <span class="rounded-xl bg-emerald-50 p-2 text-lg text-emerald-500">💰</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-emerald-600">{{ number_format($totalRevenue, 0, ',', '.') }}đ</span>
            </div>
        </div>

        <!-- Tổng tiền giảm -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Tổng tiền giảm</span>
                <span class="rounded-xl bg-rose-50 p-2 text-lg text-rose-500">🔥</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-rose-600">{{ number_format($totalDiscount, 0, ',', '.') }}đ</span>
            </div>
        </div>

        <!-- Hiệu quả trung bình -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Hiệu quả trung bình</span>
                <span class="rounded-xl bg-amber-50 p-2 text-lg text-amber-500">📈</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-slate-800">{{ number_format($avgDiscount, 0, ',', '.') }}đ<span class="text-xs font-bold text-slate-400">/đơn</span></span>
            </div>
        </div>
    </div>

    <!-- Khối Hiệu quả / Kém hiệu quả -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Voucher hiệu quả nhất -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2 mb-6">
                <span class="text-emerald-500 text-lg">📈</span> Voucher hiệu quả nhất (sử dụng nhiều nhất)
            </h3>
            
            <div class="space-y-5">
                @forelse($mostEffective as $index => $v)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <div class="flex items-center gap-2">
                                <span class="grid h-5 w-5 place-items-center rounded bg-emerald-50 text-[10px] font-black text-emerald-600">{{ $index + 1 }}</span>
                                <span class="font-mono text-slate-800">{{ $v->code }}</span>
                                <span class="font-medium text-slate-400">({{ $v->name }})</span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-800">{{ $v->uses }} lượt</span>
                                <span class="mx-1 text-slate-300">|</span>
                                <span class="text-emerald-600">Giảm {{ number_format($v->total_discount, 0, ',', '.') }}đ</span>
                            </div>
                        </div>
                        @php
                            $percentage = $totalUses > 0 ? ($v->uses / $totalUses) * 100 : 0;
                        @endphp
                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-400 py-6 text-center">Chưa có dữ liệu voucher nào được sử dụng trong thời gian này.</p>
                @endforelse
            </div>
        </div>

        <!-- Voucher kém hiệu quả -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2 mb-6">
                <span class="text-rose-500 text-lg">📉</span> Voucher kém hiệu quả (ít sử dụng nhất)
            </h3>
            
            <div class="space-y-5">
                @forelse($leastEffective as $index => $v)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <div class="flex items-center gap-2">
                                <span class="grid h-5 w-5 place-items-center rounded bg-slate-100 text-[10px] font-black text-slate-500">{{ $index + 1 }}</span>
                                <span class="font-mono text-slate-800">{{ $v->code }}</span>
                                <span class="font-medium text-slate-400">({{ $v->name }})</span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-800">{{ $v->uses }} lượt</span>
                                <span class="mx-1 text-slate-300">|</span>
                                <span class="text-rose-500">Giảm {{ number_format($v->total_discount, 0, ',', '.') }}đ</span>
                            </div>
                        </div>
                        @php
                            $percentage = $totalUses > 0 ? ($v->uses / $totalUses) * 100 : 0;
                        @endphp
                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-rose-400" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-400 py-6 text-center">Không có dữ liệu voucher nào.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Khối Biểu đồ Phân tích -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Biểu đồ lượt sử dụng theo ngày -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-extrabold text-slate-800 mb-4">📈 Số lượng voucher sử dụng theo ngày</h3>
            <div class="h-80 w-full">
                <canvas id="dailyUsageChart"></canvas>
            </div>
        </div>

        <!-- Biểu đồ doanh thu so sánh -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-extrabold text-slate-800 mb-4">💰 Doanh thu áp mã giảm giá vs Không áp mã</h3>
            <div class="h-80 w-full flex items-center justify-center">
                <div class="w-64">
                    <canvas id="revenueComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối Biểu đồ Phân tích giờ & Tỷ lệ hoàn thành hạn mức -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Biểu đồ Khung giờ -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-extrabold text-slate-800 mb-4">⏰ Phân tích sử dụng theo khung giờ (Giờ vàng vs Giờ thường)</h3>
            <div class="h-80 w-full flex items-center justify-center">
                <div class="w-64">
                    <canvas id="timeSlotChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Biểu đồ tỷ lệ hoàn thành -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-extrabold text-slate-800 mb-4">📊 Tỷ lệ sử dụng voucher so với tổng hạn mức phát hành</h3>
            <div class="h-80 w-full">
                <canvas id="limitRateChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data from Controller
        const dailyLabels = {!! json_encode($dailyLabels) !!};
        const dailyValues = {!! json_encode($dailyValues) !!};
        const voucherRevenue = {{ $totalRevenue }};
        const noVoucherRevenue = {{ $noVoucherRevenue }};
        const peakCount = {{ $peakCount }};
        const normalCount = {{ $normalCount }};
        const limitRates = {!! json_encode($limitRates) !!};

        // 1. Line Chart: Daily Usage
        new Chart(document.getElementById('dailyUsageChart'), {
            type: 'line',
            data: {
                labels: dailyLabels.length > 0 ? dailyLabels : ['Chưa có dữ liệu'],
                datasets: [{
                    label: 'Lượt sử dụng',
                    data: dailyValues.length > 0 ? dailyValues : [0],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // 2. Doughnut Chart: Revenue Comparison
        new Chart(document.getElementById('revenueComparisonChart'), {
            type: 'doughnut',
            data: {
                labels: ['Có áp mã', 'Không áp mã'],
                datasets: [{
                    data: [voucherRevenue, noVoucherRevenue],
                    backgroundColor: ['#10b981', '#cbd5e1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } }
                    }
                }
            }
        });

        // 3. Pie/Doughnut Chart: Peak vs Normal
        new Chart(document.getElementById('timeSlotChart'), {
            type: 'pie',
            data: {
                labels: ['Khung giờ cao điểm', 'Giờ thấp điểm'],
                datasets: [{
                    data: [peakCount, normalCount],
                    backgroundColor: ['#f59e0b', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } }
                    }
                }
            }
        });

        // 4. Horizontal Bar Chart: Usage/Limit Fill Rates
        new Chart(document.getElementById('limitRateChart'), {
            type: 'bar',
            data: {
                labels: limitRates.map(item => item.code),
                datasets: [{
                    label: 'Tỷ lệ đã dùng (%)',
                    data: limitRates.map(item => item.rate),
                    backgroundColor: '#6366f1',
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: value => value + '%' }
                    }
                }
            }
        });
    });
</script>
@endsection
