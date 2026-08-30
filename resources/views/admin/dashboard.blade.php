@extends('admin.layouts.app')

@push('styles')
<style>
    /* Dashboard Modern Visual Theme */
    :root {
        --dash-primary: #10b981;
        --dash-primary-hover: #059669;
        --dash-primary-light: #ecfdf5;
        --dash-accent: #6366f1;
        --dash-warning: #f59e0b;
        --dash-danger: #ef4444;
        --dash-info: #3b82f6;
        --dash-dark: #0f172a;
        --dash-slate: #475569;
        --dash-muted: #94a3b8;
        --dash-bg-card: #ffffff;
        --dash-border: #e2e8f0;
        --dash-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05);
        --dash-shadow-md: 0 4px 12px -2px rgba(15, 23, 42, 0.08);
        --dash-shadow-lg: 0 10px 25px -3px rgba(15, 23, 42, 0.1);
    }

    .dash-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .dash-greeting h2 {
        font-size: 26px;
        font-weight: 800;
        color: var(--dash-dark);
        letter-spacing: -0.02em;
        margin-bottom: 4px;
    }

    .dash-greeting p {
        font-size: 14px;
        color: var(--dash-slate);
        margin: 0;
    }

    .dash-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dash-date-badge {
        background: #ffffff;
        border: 1px solid var(--dash-border);
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        color: var(--dash-slate);
        box-shadow: var(--dash-shadow-sm);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dash-btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        padding: 9px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .dash-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        color: #ffffff;
    }

    /* Alert Banner */
    .alert-banner {
        background: linear-gradient(135deg, #fffbe6 0%, #fef3c7 100%);
        border: 1px solid #fde68a;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: var(--dash-shadow-sm);
    }

    .alert-banner-content {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .alert-banner-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #f59e0b;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .alert-banner-title {
        font-size: 14px;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 2px;
    }

    .alert-banner-desc {
        font-size: 13px;
        color: #b45309;
        margin: 0;
    }

    .alert-actions {
        display: flex;
        gap: 8px;
    }

    .btn-alert-action {
        background: #ffffff;
        color: #b45309;
        border: 1px solid #fcd34d;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-alert-action:hover {
        background: #fef3c7;
        color: #78350f;
    }

    /* Primary KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--dash-bg-card);
        border: 1px solid var(--dash-border);
        border-radius: 20px;
        padding: 22px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: var(--dash-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 150px;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--dash-shadow-md);
        border-color: #cbd5e1;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .kpi-revenue::before { background: linear-gradient(90deg, #10b981, #059669); }
    .kpi-gmv::before { background: linear-gradient(90deg, #6366f1, #4f46e5); }
    .kpi-bookings::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }
    .kpi-users::before { background: linear-gradient(90deg, #f59e0b, #d97706); }

    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .kpi-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .kpi-revenue .kpi-icon-box { background: #ecfdf5; color: #10b981; }
    .kpi-gmv .kpi-icon-box { background: #e0e7ff; color: #6366f1; }
    .kpi-bookings .kpi-icon-box { background: #dbeafe; color: #3b82f6; }
    .kpi-users .kpi-icon-box { background: #fef3c7; color: #f59e0b; }

    .kpi-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .kpi-badge-up { background: #ecfdf5; color: #059669; }
    .kpi-badge-down { background: #fef2f2; color: #dc2626; }
    .kpi-badge-neutral { background: #f1f5f9; color: #475569; }

    .kpi-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--dash-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        color: var(--dash-dark);
        letter-spacing: -0.02em;
        line-height: 1.1;
    }

    .kpi-subtext {
        font-size: 12px;
        color: var(--dash-slate);
        margin-top: 6px;
    }

    /* Secondary Metric Grid */
    .secondary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .sub-kpi-card {
        background: var(--dash-bg-card);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--dash-shadow-sm);
        transition: all 0.2s ease;
    }

    .sub-kpi-card:hover {
        border-color: #cbd5e1;
        box-shadow: var(--dash-shadow-md);
    }

    .sub-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .icon-today-booking { background: #e0f2fe; color: #0284c7; }
    .icon-today-users { background: #fae8ff; color: #c026d3; }
    .icon-today-venues { background: #ede9fe; color: #7c3aed; }
    .icon-rating { background: #fef9c3; color: #ca8a04; }

    .sub-kpi-info h5 {
        font-size: 20px;
        font-weight: 800;
        color: var(--dash-dark);
        margin: 0 0 2px 0;
        line-height: 1;
    }

    .sub-kpi-info p {
        font-size: 12px;
        color: var(--dash-slate);
        font-weight: 500;
        margin: 0;
    }

    /* Layout Grids for Charts */
    .grid-2-1 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .grid-1-1 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .grid-full {
        margin-bottom: 24px;
    }

    /* Card Panels */
    .panel-card {
        background: var(--dash-bg-card);
        border: 1px solid var(--dash-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--dash-shadow-sm);
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .panel-title h4 {
        font-size: 16px;
        font-weight: 700;
        color: var(--dash-dark);
        margin: 0 0 4px 0;
    }

    .panel-title p {
        font-size: 12px;
        color: var(--dash-slate);
        margin: 0;
    }

    .chart-box {
        position: relative;
        width: 100%;
        min-height: 260px;
    }

    /* Sport Doughnut Legend */
    .sports-legend-list {
        list-style: none;
        padding: 0;
        margin: 18px 0 0 0;
    }

    .sports-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #f1f5f9;
        font-size: 13px;
    }

    .sports-legend-item:last-child {
        border-bottom: none;
    }

    .sports-name {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--dash-dark);
    }

    .sports-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .sports-val {
        font-weight: 700;
        color: var(--dash-slate);
    }

    /* Leaderboards */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--dash-muted);
        letter-spacing: 0.05em;
        padding: 12px 14px;
        border-bottom: 1px solid var(--dash-border);
        background: #f8fafc;
    }

    .table-modern th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .table-modern th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    .table-modern td {
        padding: 14px;
        font-size: 13px;
        color: var(--dash-dark);
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .table-modern tr:last-child td {
        border-bottom: none;
    }

    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
    }

    .rank-1 { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
    .rank-2 { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .rank-3 { background: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
    .rank-other { background: #f8fafc; color: #94a3b8; }

    .owner-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .owner-row:last-child {
        border-bottom: none;
    }

    .owner-avatar-img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: var(--dash-shadow-sm);
    }

    .owner-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--dash-dark);
        margin-bottom: 2px;
    }

    .owner-sub {
        font-size: 12px;
        color: var(--dash-slate);
    }

    .owner-money {
        font-size: 14px;
        font-weight: 800;
        color: var(--dash-primary);
    }

    /* Status Badges */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-dot-icon {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .pill-confirmed { background: #dbeafe; color: #1e40af; }
    .pill-confirmed .status-dot-icon { background: #2563eb; }

    .pill-completed { background: #ecfdf5; color: #065f46; }
    .pill-completed .status-dot-icon { background: #10b981; }

    .pill-pending { background: #fef3c7; color: #92400e; }
    .pill-pending .status-dot-icon { background: #f59e0b; }

    .pill-cancelled { background: #fef2f2; color: #991b1b; }
    .pill-cancelled .status-dot-icon { background: #ef4444; }

    /* Density Bars */
    .density-item-row {
        margin-bottom: 14px;
    }

    .density-meta {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .density-city { font-weight: 600; color: var(--dash-dark); }
    .density-num { font-weight: 700; color: var(--dash-primary); }

    .density-progress-bg {
        width: 100%;
        height: 8px;
        background: #f1f5f9;
        border-radius: 4px;
        overflow: hidden;
    }

    .density-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    /* Filter Nav for Table */
    .table-filter-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .table-filter-btn {
        background: #f1f5f9;
        color: var(--dash-slate);
        border: none;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .table-filter-btn.active, .table-filter-btn:hover {
        background: var(--dash-primary);
        color: #ffffff;
    }

    @media (max-width: 1200px) {
        .kpi-grid, .secondary-grid { grid-template-columns: repeat(2, 1fr); }
        .grid-2-1, .grid-1-1 { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .kpi-grid, .secondary-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
    <!-- Dashboard Header -->
    <div class="dash-header">
        <div class="dash-greeting">
            <h2>Xin chào Admin 👋</h2>
            <p>Tổng quan hệ thống SportHub & báo cáo hiệu năng hôm nay.</p>
        </div>
        <div class="dash-header-actions">
            <div class="dash-date-badge">
                <i class="fa-regular fa-calendar"></i>
                <span>Năm {{ $currentYear }}</span>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="dash-btn-primary">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Xem Báo Cáo Chi Tiết</span>
            </a>
        </div>
    </div>

    <!-- Alert Banner (If Pending Approvals Exist) -->
    @if(($pendingVenuesCount ?? 0) > 0 || ($pendingBookingsCount ?? 0) > 0)
        <div class="alert-banner">
            <div class="alert-banner-content">
                <div class="alert-banner-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <div class="alert-banner-title">Cần xử lý phê duyệt từ Admin</div>
                    <div class="alert-banner-desc">
                        @if(($pendingVenuesCount ?? 0) > 0)
                            Hiện có <strong>{{ $pendingVenuesCount }}</strong> cơ sở sân thể thao mới đang chờ bạn duyệt.
                        @endif
                        @if(($pendingBookingsCount ?? 0) > 0)
                            Có <strong>{{ $pendingBookingsCount }}</strong> lịch đặt sân ở trạng thái chờ duyệt.
                        @endif
                    </div>
                </div>
            </div>
            <div class="alert-actions">
                @if(($pendingVenuesCount ?? 0) > 0)
                    <a href="{{ route('admin.venues.index') }}" class="btn-alert-action">
                        Duyệt Sân Nhanh
                    </a>
                @endif
                @if(($pendingBookingsCount ?? 0) > 0)
                    <a href="{{ route('admin.bookings.index') }}" class="btn-alert-action">
                        Quản Lý Booking
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Row 1: Primary KPI Metrics -->
    <div class="kpi-grid">
        <div class="kpi-card kpi-revenue">
            <div class="kpi-top">
                <div class="kpi-icon-box">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="kpi-badge kpi-badge-up">
                    <i class="fa-solid fa-circle-check"></i> Settled
                </div>
            </div>
            <div>
                <div class="kpi-label">Doanh Thu Nền Tảng</div>
                <div class="kpi-value">{{ number_format($totalRevenue) }}<small style="font-size:16px;">đ</small></div>
                <div class="kpi-subtext">Thu từ phí hoa hồng hệ thống</div>
            </div>
        </div>

        <div class="kpi-card kpi-gmv">
            <div class="kpi-top">
                <div class="kpi-icon-box">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="kpi-badge kpi-badge-neutral">
                    GMV
                </div>
            </div>
            <div>
                <div class="kpi-label">Tổng Giá Trị Giao Dịch</div>
                <div class="kpi-value">{{ number_format($gmv) }}<small style="font-size:16px;">đ</small></div>
                <div class="kpi-subtext">Tổng tiền khách hàng thanh toán</div>
            </div>
        </div>

        <div class="kpi-card kpi-bookings">
            <div class="kpi-top">
                <div class="kpi-icon-box">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <div class="kpi-badge {{ ($bookingGrowth ?? 0) >= 0 ? 'kpi-badge-up' : 'kpi-badge-down' }}">
                    <i class="fa-solid {{ ($bookingGrowth ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                    {{ abs($bookingGrowth ?? 0) }}%
                </div>
            </div>
            <div>
                <div class="kpi-label">Tổng Booking Hợp Lệ</div>
                <div class="kpi-value">{{ number_format(($singleBookingsCount ?? 0) + ($packageBookingsCount ?? 0)) }}</div>
                <div class="kpi-subtext mb-2">Tăng trưởng so với tháng trước</div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="font-size: 12px; color: var(--dash-slate);">
                    <span><i class="fa-regular fa-circle-dot text-emerald-500 me-1"></i> Đặt lẻ: <strong class="text-dark">{{ number_format($singleBookingsCount ?? 0) }}</strong></span>
                    <span><i class="fa-solid fa-cubes text-indigo-500 me-1"></i> Số gói: <strong class="text-dark">{{ number_format($packageBookingsCount ?? 0) }}</strong></span>
                </div>
            </div>
        </div>

        <div class="kpi-card kpi-users">
            <div class="kpi-top">
                <div class="kpi-icon-box">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="kpi-badge kpi-badge-neutral">
                    Hệ thống
                </div>
            </div>
            <div>
                <div class="kpi-label">Tổng Người Dùng</div>
                <div class="kpi-value">{{ number_format($totalUsers) }}</div>
                <div class="kpi-subtext">{{ number_format($totalVenues) }} cơ sở sân hoạt động</div>
            </div>
        </div>
    </div>

    <!-- Row 2: Secondary Quick Stats -->
    <div class="secondary-grid">
        <div class="sub-kpi-card">
            <div class="sub-kpi-icon icon-today-booking">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="sub-kpi-info">
                <h5>{{ number_format($bookingsToday) }}</h5>
                <p>Booking Hôm Nay</p>
            </div>
        </div>

        <div class="sub-kpi-card">
            <div class="sub-kpi-icon icon-today-users">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="sub-kpi-info">
                <h5>{{ number_format($usersToday) }}</h5>
                <p>Khách Hàng Mới Hôm Nay</p>
            </div>
        </div>

        <div class="sub-kpi-card">
            <div class="sub-kpi-icon icon-today-venues">
                <i class="fa-solid fa-store"></i>
            </div>
            <div class="sub-kpi-info">
                <h5>{{ number_format($venuesToday) }}</h5>
                <p>Sân Mới Đăng Ký Hôm Nay</p>
            </div>
        </div>

        <div class="sub-kpi-card">
            <div class="sub-kpi-icon icon-rating">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="sub-kpi-info">
                <h5>{{ number_format($avgRating, 1) }} / 5.0</h5>
                <p>Đánh Giá Hệ Thống</p>
            </div>
        </div>
    </div>

    <!-- Row 3: Charts Section -->
    <div class="grid-2-1">
        <!-- Revenue & GMV Trend Chart -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Biểu đồ Doanh Thu & Giá Trị Giao Dịch</h4>
                    <p>Xu hướng hoa hồng nền tảng và GMV theo từng tháng năm {{ $currentYear }}</p>
                </div>
            </div>
            <div class="chart-box" style="height: 280px;">
                <canvas id="lineChartRevenueGmv"></canvas>
            </div>
        </div>

        <!-- Sport Types Breakdown Doughnut Chart -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Thống Kê Theo Môn Thể Thao</h4>
                    <p>Tỷ lệ lượt đặt sân phân bổ</p>
                </div>
            </div>
            <div class="chart-box" style="height: 200px; display:flex; align-items:center; justify-content:center;">
                <canvas id="donutChartSports"></canvas>
            </div>
            <ul class="sports-legend-list">
                @php
                    $totalSportsBookings = array_sum($chartSports);
                    $colors = ['#10b981', '#6366f1', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6', '#14b8a6', '#64748b'];
                    $idx = 0;
                @endphp
                @foreach($chartSports as $sport => $count)
                    @php
                        $percentage = $totalSportsBookings > 0 ? round(($count / $totalSportsBookings) * 100) : 0;
                        $color = $colors[$idx % count($colors)];
                        $idx++;
                    @endphp
                    <li class="sports-legend-item">
                        <span class="sports-name">
                            <span class="sports-dot" style="background: {{ $color }};"></span>
                            {{ $sport }}
                        </span>
                        <span class="sports-val">{{ $percentage }}% ({{ number_format($count) }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Row 4: Monthly Bookings Bar Chart & Geographic Breakdown -->
    <div class="grid-2-1">
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Tổng Lượt Đặt Sân Theo Tháng</h4>
                    <p>Số lượng đơn booking hợp lệ được tạo từng tháng</p>
                </div>
            </div>
            <div class="chart-box" style="height: 240px;">
                <canvas id="barChartBookings"></canvas>
            </div>
        </div>

        <!-- Geographic Density -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Mật Độ Sân Theo Thành Phố</h4>
                    <p>Phân bổ các cơ sở sân trên toàn quốc</p>
                </div>
            </div>
            <div>
                @php
                    $maxDensity = max(array_merge([1], array_values($regionDensity)));
                @endphp
                @foreach($regionDensity as $region => $value)
                    @php
                        $percent = round(($value / $maxDensity) * 100);
                    @endphp
                    <div class="density-item-row">
                        <div class="density-meta">
                            <span class="density-city"><i class="fa-solid fa-location-dot text-emerald-500 me-1"></i> {{ $region }}</span>
                            <span class="density-num">{{ $value }} sân</span>
                        </div>
                        <div class="density-progress-bg">
                            <div class="density-progress-fill" style="width: {{ max(6, $percent) }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Row 5: Leaderboards (Top Venues & Top Owners) -->
    <div class="grid-2-1">
        <!-- Top Venues -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Cơ Sở Sân Thể Thao Nổi Bật</h4>
                    <p>Top sân có số lượng booking & doanh thu cao nhất</p>
                </div>
                <a href="{{ route('admin.venues.index') }}" style="font-size:13px; color:var(--dash-primary); font-weight:700; text-decoration:none;">
                    Xem tất cả <i class="fa-solid fa-chevron-right fs-xs"></i>
                </a>
            </div>
            <div style="overflow-x: auto;">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Hạng</th>
                            <th>Tên Sân Thể Thao</th>
                            <th>Bộ Môn</th>
                            <th>Lượt Đặt</th>
                            <th>Tổng Doanh Thu</th>
                            <th>Đánh Giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topVenues as $venue)
                            <tr>
                                <td>
                                    <div class="rank-badge {{ $venue->rank == 1 ? 'rank-1' : ($venue->rank == 2 ? 'rank-2' : ($venue->rank == 3 ? 'rank-3' : 'rank-other')) }}">
                                        {{ $venue->rank }}
                                    </div>
                                </td>
                                <td><strong>{{ $venue->name }}</strong></td>
                                <td><span class="badge bg-light text-dark border">{{ $venue->type }}</span></td>
                                <td><strong>{{ $venue->bookings }}</strong></td>
                                <td><strong style="color:var(--dash-primary);">{{ $venue->revenue }}</strong></td>
                                <td><span style="color:#f59e0b; font-weight:700;"><i class="fa-solid fa-star"></i> {{ $venue->rating }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Chưa có dữ liệu cơ sở sân.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Owners -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Top Chủ Sân Xuất Sắc</h4>
                    <p>Chủ sân sở hữu nhiều cơ sở & doanh thu hàng đầu</p>
                </div>
            </div>
            <div>
                @forelse($topOwners as $owner)
                    <div class="owner-row">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $owner->avatar }}" class="owner-avatar-img" alt="Avatar">
                            <div>
                                <div class="owner-name">{{ $owner->name }}</div>
                                <div class="owner-sub">{{ $owner->stats }}</div>
                            </div>
                        </div>
                        <div class="owner-money">{{ $owner->revenue }}</div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">Chưa có dữ liệu chủ sân.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Row 6: Recent Bookings Table -->
    <div class="grid-full">
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h4>Danh Sách Booking Gần Đây</h4>
                    <p>Theo dõi toàn bộ giao dịch đặt sân mới nhất trên hệ thống</p>
                </div>
                <div>
                    <a href="{{ route('admin.bookings.index') }}" class="dash-btn-primary">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Quản Lý Toàn Bộ Booking</span>
                    </a>
                </div>
            </div>

            <!-- Booking Filter Bar -->
            <div class="table-filter-bar">
                <button class="table-filter-btn active" onclick="filterBookingRows('all', this)">Tất Cả</button>
                <button class="table-filter-btn" onclick="filterBookingRows('pending', this)">Chờ Duyệt</button>
                <button class="table-filter-btn" onclick="filterBookingRows('confirmed', this)">Đã Xác Nhận</button>
                <button class="table-filter-btn" onclick="filterBookingRows('completed', this)">Hoàn Thành</button>
                <button class="table-filter-btn" onclick="filterBookingRows('cancelled', this)">Đã Hủy</button>
            </div>

            <div style="overflow-x: auto;">
                <table class="table-modern" id="adminBookingsTable">
                    <thead>
                        <tr>
                            <th>Mã Booking</th>
                            <th>Khách Hàng</th>
                            <th>Sân Thể Thao</th>
                            <th>Ngày Đặt</th>
                            <th>Khung Giờ</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allBookings as $booking)
                            @php
                                $statusClass = 'pill-pending';
                                $statusText = 'Chờ duyệt';
                                $statusKey = $booking->status ?? 'pending';

                                switch($booking->status) {
                                    case 'confirmed':
                                        $statusClass = 'pill-confirmed';
                                        $statusText = 'Đã xác nhận';
                                        break;
                                    case 'completed':
                                        $statusClass = 'pill-completed';
                                        $statusText = 'Đã hoàn thành';
                                        break;
                                    case 'cancelled':
                                    case 'rejected':
                                        $statusClass = 'pill-cancelled';
                                        $statusText = 'Đã hủy';
                                        $statusKey = 'cancelled';
                                        break;
                                    case 'pending':
                                    default:
                                        $statusClass = 'pill-pending';
                                        $statusText = 'Chờ duyệt';
                                        $statusKey = 'pending';
                                        break;
                                }
                            @endphp
                            <tr class="booking-row" data-status="{{ $statusKey }}">
                                <td>
                                    <strong style="color:var(--dash-accent);">#BK-{{ sprintf('%04d', $booking->id) }}</strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $booking->user ? $booking->user->name : 'Khách lẻ' }}</div>
                                    <div style="font-size:11px; color:var(--dash-slate);">{{ $booking->user ? $booking->user->email : '' }}</div>
                                </td>
                                <td>
                                    <strong>{{ $booking->court && $booking->court->venue ? $booking->court->venue->name : 'N/A' }}</strong>
                                    @if($booking->court)
                                        <div style="font-size:11px; color:var(--dash-slate);">{{ $booking->court->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $booking->slot_date ? \Carbon\Carbon::parse($booking->slot_date)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:var(--dash-dark);">{{ number_format($booking->total_price) }} VNĐ</strong>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        <span class="status-dot-icon"></span>
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có booking nào trên hệ thống.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allBookings->hasPages())
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    {{ $allBookings->links('vendor.pagination.admin') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Chart 1: Revenue & GMV Dual Line Chart
    const lineCtx = document.getElementById('lineChartRevenueGmv').getContext('2d');
    
    let gradRev = lineCtx.createLinearGradient(0, 0, 0, 250);
    gradRev.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
    gradRev.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    let gradGmv = lineCtx.createLinearGradient(0, 0, 0, 250);
    gradGmv.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
    gradGmv.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [
                {
                    label: 'Doanh Thu Hoa Hồng (VNĐ)',
                    data: @json($chartRevenueTrend),
                    borderColor: '#10b981',
                    borderWidth: 3,
                    backgroundColor: gradRev,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981'
                },
                {
                    label: 'Tổng GMV (VNĐ)',
                    data: @json($chartGmvTrend ?? []),
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    backgroundColor: gradGmv,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#6366f1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        font: { family: 'Inter', size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    border: { dash: [4, 4] },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000) + 'M';
                            if (value >= 1000) return (value / 1000) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // Chart 2: Sports Distribution Doughnut
    const donutCtx = document.getElementById('donutChartSports').getContext('2d');
    const sportsData = @json($chartSports);
    const sportsLabels = Object.keys(sportsData);
    const sportsValues = Object.values(sportsData);
    const totalSportsCount = sportsValues.reduce((a, b) => a + b, 0);
    const donutValues = totalSportsCount > 0 ? sportsValues : [1, 1, 1, 1];
    const donutPalette = ['#10b981', '#6366f1', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6', '#14b8a6', '#64748b'];

    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: sportsLabels.length > 0 ? sportsLabels : ['Bóng đá', 'Cầu lông', 'Tennis', 'Bóng rổ'],
            datasets: [{
                data: donutValues,
                backgroundColor: donutPalette.slice(0, Math.max(4, sportsLabels.length)),
                borderWidth: 0,
                cutout: '76%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Center text plugin for doughnut
    Chart.register({
        id: 'centerTextDoughnut',
        beforeDraw: function(chart) {
            if (chart.config.type !== 'doughnut') return;
            var width = chart.width, height = chart.height, ctx = chart.ctx;
            ctx.restore();
            var fontSize = (height / 120).toFixed(2);
            ctx.font = "800 " + fontSize + "em Inter";
            ctx.textBaseline = "middle";
            ctx.textAlign = "center";
            var text = totalSportsCount.toString();
            var textX = Math.round(width / 2);
            var textY = Math.round(height / 2) - 6;
            ctx.fillStyle = "#0f172a";
            ctx.fillText(text, textX, textY);
            
            ctx.font = "600 " + (fontSize / 2.4).toFixed(2) + "em Inter";
            ctx.fillStyle = "#64748b";
            ctx.fillText("LƯỢT ĐẶT", textX, textY + 22);
            ctx.save();
        }
    });

    // Chart 3: Monthly Bookings Bar Chart
    const barCtx = document.getElementById('barChartBookings').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                label: 'Lượt đặt sân',
                data: @json($chartBookingsMonthly),
                backgroundColor: '#10b981',
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: '#059669'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    border: { dash: [4, 4] },
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // Booking Table Filter Logic
    function filterBookingRows(status, btn) {
        document.querySelectorAll('.table-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#adminBookingsTable tbody .booking-row');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
