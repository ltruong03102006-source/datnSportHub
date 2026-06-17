@extends('admin.layouts.app')

@push('styles')
<style>
    .welcome-header {
        margin-bottom: 30px;
    }
    .welcome-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
    }
    .welcome-header p {
        font-size: 14px;
        color: var(--text-muted);
    }

    /* Grid Layouts */
    .grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    .grid-3-1 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    .grid-1 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Stat Cards */
    .stat-card {
        padding: 20px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
    }
    
    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .badge-custom {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .stat-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
    }

    /* Card Variations */
    .c-users .stat-icon { background: #eafaf1; color: #2ecc71; }
    .c-users .badge-custom { background: #eafaf1; color: #2ecc71; }
    
    .c-venues .stat-icon { background: #e8f8f5; color: #1abc9c; }
    .c-venues .badge-custom { background: #e8f8f5; color: #1abc9c; }
    
    .c-bookings .stat-icon { background: #f2f3f4; color: #95a5a6; }
    .c-bookings .badge-custom { background: #f2f3f4; color: #95a5a6; }
    
    .c-revenue { border: 1px solid #2ecc71; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.1); }
    .c-revenue .stat-icon { background: #27ae60; color: white; }
    .c-revenue .badge-custom { background: #eafaf1; color: #27ae60; }
    .c-revenue .stat-value { color: #27ae60; }

    .c-today .stat-icon { background: #fdf2e9; color: #e67e22; }
    .c-today .badge-custom { background: #fdf2e9; color: #e67e22; }
    
    .c-newusers .stat-icon { background: #ebf5fb; color: #3498db; }
    .c-newusers .badge-custom { background: #ebf5fb; color: #3498db; }
    
    .c-newvenues .stat-icon { background: #f5eef8; color: #9b59b6; }
    .c-newvenues .badge-custom { background: #f5eef8; color: #9b59b6; }
    
    .c-rating .stat-icon { background: #fef9e7; color: #f1c40f; }
    .c-rating .badge-custom { background: #fef9e7; color: #f1c40f; }

    /* Chart Cards */
    .chart-card {
        padding: 24px;
        display: flex;
        flex-direction: column;
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .chart-title h3 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .chart-title p {
        font-size: 12px;
        color: var(--text-muted);
    }
    .chart-filter select {
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        font-size: 12px;
        outline: none;
    }
    .chart-container {
        position: relative;
        flex: 1;
        min-height: 250px;
        width: 100%;
    }

    /* Tables & Lists */
    .data-card {
        padding: 24px;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    .table-custom th {
        text-align: left;
        padding: 12px 0;
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
    }
    .table-custom td {
        padding: 16px 0;
        font-size: 13px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-custom tr:last-child td {
        border-bottom: none;
    }

    .top-owner-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 0;
        border-bottom: 1px solid var(--border-color);
    }
    .top-owner-item:last-child { border-bottom: none; }
    
    .owner-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .owner-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
    .owner-details h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .owner-details p {
        font-size: 12px;
        color: var(--text-muted);
    }
    .owner-revenue {
        font-size: 14px;
        font-weight: 700;
        color: #2ecc71;
    }

    /* Region Density */
    .density-wrapper {
        display: flex;
        gap: 40px;
        align-items: center;
    }
    .density-list {
        flex: 1;
    }
    .density-item {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }
    .density-name {
        width: 80px;
        font-size: 13px;
        font-weight: 500;
    }
    .density-bar-wrapper {
        flex: 1;
        height: 6px;
        background: #f1f2f6;
        border-radius: 3px;
        margin: 0 16px;
        overflow: hidden;
    }
    .density-bar {
        height: 100%;
        background: #27ae60;
        border-radius: 3px;
    }
    .density-value {
        width: 40px;
        text-align: right;
        font-size: 13px;
        font-weight: 600;
    }
    .density-map {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f4f6f6;
        border-radius: 16px;
        padding: 40px;
        position: relative;
    }
    /* Simple CSS Globe representation */
    .globe {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: #d4efdf;
        position: relative;
        overflow: hidden;
    }
    .globe::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 160px;
        background: #abebc6;
        border-radius: 50%;
        top: 20px;
        left: 20px;
        transform: rotate(30deg);
    }
    .globe::before {
        content: '';
        position: absolute;
        width: 80px;
        height: 100px;
        background: #abebc6;
        border-radius: 50%;
        bottom: 10px;
        right: 10px;
        transform: rotate(-20deg);
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-paid { background: #eafaf1; color: #2ecc71; }
    .status-pending { background: #ebf5fb; color: #3498db; }
    .status-cancelled { background: #fdedec; color: #e74c3c; }
    .table-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .btn-primary-custom {
        background: #27ae60;
        color: #fff;
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .pagination-wrapper {
        padding-top: 18px;
        border-top: 1px solid var(--border-color);
        margin-top: 4px;
    }
    .pagination-wrapper nav {
        display: flex;
        justify-content: flex-end;
    }

</style>
@endpush

@section('content')
    <div class="welcome-header">
        <h2>Xin chào Admin 👋</h2>
        <p>Tổng quan hoạt động hệ thống hôm nay.</p>
    </div>

    <!-- Row 1: Metrics -->
    <div class="grid-4">
        <div class="card-custom stat-card c-users">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
                <div class="badge-custom">+15%</div>
            </div>
            <div>
                <div class="stat-title">Tổng người dùng</div>
                <div class="stat-value">{{ number_format($totalUsers) }}</div>
            </div>
        </div>

        <div class="card-custom stat-card c-venues">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-solid fa-building"></i></div>
                <div class="badge-custom">+8 mới</div>
            </div>
            <div>
                <div class="stat-title">Tổng số sân</div>
                <div class="stat-value">{{ number_format($totalVenues) }}</div>
            </div>
        </div>

        <div class="card-custom stat-card c-bookings">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-regular fa-calendar"></i></div>
                <div class="badge-custom">+12%</div>
            </div>
            <div>
                <div class="stat-title">Tổng lượt đặt sân</div>
                <div class="stat-value">{{ number_format($totalBookings) }}</div>
            </div>
        </div>

        <div class="card-custom stat-card c-revenue">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                <div class="badge-custom">Tháng này</div>
            </div>
            <div>
                <div class="stat-title">Tổng doanh thu (VNĐ)</div>
                <div class="stat-value">{{ number_format($totalRevenue) }}</div>
            </div>
        </div>
    </div>

    <!-- Row 2: Secondary Metrics -->
    <div class="grid-4">
        <div class="card-custom stat-card c-today">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-regular fa-clock"></i></div>
                <div class="badge-custom">Hôm nay</div>
            </div>
            <div>
                <div class="stat-title">Booking hôm nay</div>
                <div class="stat-value">{{ number_format($bookingsToday) }}</div>
            </div>
        </div>

        <div class="card-custom stat-card c-newusers">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-solid fa-user-plus"></i></div>
                <div class="badge-custom">+12% vs trước</div>
            </div>
            <div>
                <div class="stat-title">Người dùng mới</div>
                <div class="stat-value">{{ number_format($usersToday) }}</div>
            </div>
        </div>

        <div class="card-custom stat-card c-newvenues">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div class="badge-custom">Tháng này</div>
            </div>
            <div>
                <div class="stat-title">Sân mới được tạo</div>
                <div class="stat-value">{{ number_format($venuesToday) }}</div>
            </div>
        </div>

        <div class="card-custom stat-card c-rating">
            <div class="stat-card-header">
                <div class="stat-icon"><i class="fa-regular fa-star"></i></div>
                <div class="badge-custom">Tin cậy</div>
            </div>
            <div>
                <div class="stat-title">Đánh giá trung bình</div>
                <div class="stat-value">{{ number_format($avgRating, 1) }}/5</div>
            </div>
        </div>
    </div>

    <!-- Row 3: Charts -->
    <div class="grid-3-1">
        <div class="card-custom chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <h3>Lượt đặt sân theo tháng</h3>
                    <p>Dữ liệu tổng hợp năm 2024</p>
                </div>
                <div class="chart-filter">
                    <select>
                        <option>Năm 2024</option>
                    </select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <div class="card-custom chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <h3>Thống kê theo môn</h3>
                </div>
            </div>
            <div class="chart-container" style="display:flex; align-items:center; justify-content:center;">
                <canvas id="donutChart"></canvas>
            </div>
            <div style="margin-top: 20px;">
                <ul style="font-size: 13px; font-weight: 500;">
                    @php
                        $totalSportsBookings = array_sum($chartSports);
                        $colors = ['#2ecc71', '#27ae60', '#bdc3c7', '#34495e', '#9b59b6', '#e67e22', '#3498db', '#f1c40f'];
                        $idx = 0;
                    @endphp
                    @foreach($chartSports as $sport => $count)
                        @php
                            $percentage = $totalSportsBookings > 0 ? round(($count / $totalSportsBookings) * 100) : 0;
                            $color = $colors[$idx % count($colors)];
                            $idx++;
                        @endphp
                        <li style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:{{ $color }};">● {{ $sport }}</span> 
                            <span>{{ $percentage }}% ({{ $count }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Row 4: Line Chart -->
    <div class="grid-1">
        <div class="card-custom chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <h3>Xu hướng Doanh thu</h3>
                    <p>Tăng trưởng ổn định hàng quý</p>
                </div>
                <div class="chart-filter">
                    <div style="background:#f1f2f6; border-radius:20px; padding:4px;">
                        <button style="border:none; background:transparent; padding:4px 12px; font-size:11px; border-radius:16px;">Ngày</button>
                        <button style="border:none; background:transparent; padding:4px 12px; font-size:11px; border-radius:16px;">Tuần</button>
                        <button style="border:none; background:#27ae60; color:white; padding:4px 12px; font-size:11px; border-radius:16px;">Tháng</button>
                    </div>
                </div>
            </div>
            <div class="chart-container" style="height: 200px;">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 5: Top Lists -->
    <div class="grid-3-1">
        <div class="card-custom data-card">
            <div class="chart-header">
                <div class="chart-title"><h3>Sân thể thao hàng đầu</h3></div>
                <a href="#" style="font-size:12px; color:#2ecc71; font-weight:600;">Xem tất cả</a>
            </div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Hạng</th>
                        <th>Tên Sân</th>
                        <th>Loại Sân & Lượt Đặt</th>
                        <th>Doanh Thu</th>
                        <th>Đánh Giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topVenues as $venue)
                    <tr>
                        <td style="color: #2ecc71; font-weight: 700;">#{{ $venue->rank }}</td>
                        <td style="font-weight: 500;">{{ $venue->name }}</td>
                        <td>{{ $venue->type }} <span style="color:var(--text-muted);">{{ $venue->bookings }}</span></td>
                        <td style="font-weight: 600;">{{ $venue->revenue }}</td>
                        <td style="color: #f1c40f;"><i class="fa-solid fa-star"></i> {{ $venue->rating }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-custom data-card">
            <div class="chart-header">
                <div class="chart-title"><h3>Top Chủ sân tiêu biểu</h3></div>
            </div>
            <div>
                @foreach($topOwners as $owner)
                <div class="top-owner-item">
                    <div class="owner-info">
                        <img src="{{ $owner->avatar }}" class="owner-avatar" alt="Avatar">
                        <div class="owner-details">
                            <h4>{{ $owner->name }}</h4>
                            <p>{{ $owner->stats }}</p>
                        </div>
                    </div>
                    <div class="owner-revenue">{{ $owner->revenue }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Row 6: All Bookings -->
    <div class="grid-1">
        <div class="card-custom data-card">
            <div class="chart-header">
                <div class="chart-title">
                    <h3>Tất cả booking</h3>
                    <p>Admin có thể xem toàn bộ lịch đặt trong hệ thống.</p>
                </div>
                <div class="table-actions">
                    <a href="{{ route('admin.bookings.index') }}" class="btn-primary-custom">
                        <i class="fa-regular fa-calendar-check"></i> Quản lý booking
                    </a>
                </div>
            </div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Mã Booking</th>
                        <th>Khách Hàng</th>
                        <th>Tên Sân</th>
                        <th>Ngày</th>
                        <th>Khung Giờ</th>
                        <th>Số Tiền</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allBookings as $booking)
                        @php
                            $statusClass = 'status-pending';
                            $statusText = 'Chờ duyệt';
                            switch($booking->status) {
                                case 'confirmed':
                                    $statusClass = 'status-paid';
                                    $statusText = 'Đã xác nhận';
                                    break;
                                case 'completed':
                                    $statusClass = 'status-paid';
                                    $statusText = 'Đã hoàn thành';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-cancelled';
                                    $statusText = 'Đã hủy';
                                    break;
                                case 'rejected':
                                    $statusClass = 'status-cancelled';
                                    $statusText = 'Đã từ chối';
                                    break;
                                case 'pending':
                                default:
                                    $statusClass = 'status-pending';
                                    $statusText = 'Chờ duyệt';
                                    break;
                            }
                        @endphp
                        <tr>
                            <td style="color:var(--text-muted);">#BK-{{ sprintf('%03d', $booking->id) }}</td>
                            <td style="font-weight:500;">{{ $booking->user ? $booking->user->name : 'N/A' }}</td>
                            <td>{{ $booking->court && $booking->court->venue ? $booking->court->venue->name : 'N/A' }}</td>
                            <td>{{ $booking->slot_date ? \Carbon\Carbon::parse($booking->slot_date)->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                            <td style="font-weight:600;">{{ number_format($booking->total_price) }} VNĐ</td>
                            <td><span class="status-badge {{ $statusClass }}">{{ $statusText }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">Chưa có booking nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($allBookings->hasPages())
                <div class="pagination-wrapper">
                    {{ $allBookings->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Row 7: Map -->
    <div class="grid-1">
        <div class="card-custom data-card">
            <div class="chart-header mb-4">
                <div class="chart-title">
                    <h3>Mật độ sân theo khu vực</h3>
                    <p>Phân bổ hệ thống tại các thành phố lớn trên toàn quốc Việt Nam.</p>
                </div>
            </div>
            <div class="density-wrapper">
                <div class="density-list">
                    @php $maxDensity = 210; @endphp
                    @foreach($regionDensity as $region => $value)
                    <div class="density-item">
                        <div class="density-name">{{ $region }}</div>
                        <div class="density-bar-wrapper">
                            <div class="density-bar" style="width: {{ ($value / $maxDensity) * 100 }}%"></div>
                        </div>
                        <div class="density-value">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
                <div class="density-map">
                    <div class="globe"></div>
                    <div style="position:absolute; bottom:20px; left:20px; background:white; padding:10px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                        <h6 style="font-size:11px; font-weight:700; color:#2ecc71; margin-bottom:2px;">Việt Nam Coverage</h6>
                        <p style="font-size:10px; color:var(--text-muted); margin:0;">Tỉ lệ phủ sóng 85% các đô thị loại 1</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                label: 'Lượt đặt sân',
                data: @json($chartBookingsMonthly),
                backgroundColor: 'rgba(46, 204, 113, 0.4)',
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { display: false, grid: { display: false } }
            }
        }
    });

    const donutCtx = document.getElementById('donutChart').getContext('2d');
    const sportsData = @json($chartSports);
    const sportsLabels = Object.keys(sportsData);
    const sportsValues = Object.values(sportsData);
    const totalCount = sportsValues.reduce((a, b) => a + b, 0);
    const chartValues = totalCount > 0 ? sportsValues : [1, 1, 1, 1];
    const donutColors = ['#2ecc71', '#27ae60', '#bdc3c7', '#34495e', '#9b59b6', '#e67e22', '#3498db', '#f1c40f'];

    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: sportsLabels.length > 0 ? sportsLabels : ['Bóng đá', 'Cầu lông', 'Tennis', 'Bóng rổ'],
            datasets: [{
                data: chartValues,
                backgroundColor: donutColors.slice(0, Math.max(4, sportsLabels.length)),
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Custom plugin to draw text inside doughnut
    Chart.register({
        id: 'centerText',
        beforeDraw: function(chart) {
            if (chart.config.type !== 'doughnut') return;
            var width = chart.width,
                height = chart.height,
                ctx = chart.ctx;
            ctx.restore();
            var fontSize = (height / 114).toFixed(2);
            ctx.font = "bold " + fontSize + "em Inter";
            ctx.textBaseline = "middle";
            ctx.textAlign = "center";
            var text = totalCount.toString(),
                textX = Math.round(width / 2),
                textY = Math.round(height / 2) - 5;
            ctx.fillStyle = "#2c3e50";
            ctx.fillText(text, textX, textY);
            
            ctx.font = "500 " + (fontSize/2.5).toFixed(2) + "em Inter";
            ctx.fillStyle = "#7f8c8d";
            ctx.fillText("LƯỢT ĐẶT", textX, textY + 20);
            ctx.save();
        }
    });

    const lineCtx = document.getElementById('lineChart').getContext('2d');
    let gradient = lineCtx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(46, 204, 113, 0.2)');
    gradient.addColorStop(1, 'rgba(46, 204, 113, 0)');

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
            datasets: [{
                data: @json($chartRevenueTrend),
                borderColor: '#27ae60',
                borderWidth: 2,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false }
            }
        }
    });
</script>
@endpush
