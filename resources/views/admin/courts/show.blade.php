@extends('admin.layouts.app')

@section('title', 'Chi tiết Sân: ' . $court->name)

@push('styles')
<style>
    /* Header Styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* Grid Layouts */
    .admin-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 992px) {
        .admin-grid { grid-template-columns: 1fr; }
    }
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px 16px;
    }

    /* Card Styles */
    .admin-card {
        background: var(--card-bg);
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        background: #fbfcfc;
        font-weight: 600;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
    }
    .card-body { padding: 20px; }

    /* Info Items & Stats */
    .info-item label {
        display: block;
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .info-item .value {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 500;
        line-height: 1.5;
    }
    .stat-item { margin-bottom: 20px; }
    .stat-item:last-child { margin-bottom: 0; }
    .stat-label { font-size: 13px; color: var(--text-muted); margin-bottom: 4px; font-weight: 500; }
    .stat-value { font-size: 28px; font-weight: 700; color: var(--text-dark); }
    .stat-value.warning { color: #f39c12; }

    /* Buttons & Badges */
    .btn-action {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-action:hover { background-color: #27ae60; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(46, 204, 113, 0.2); }
    .btn-secondary { background-color: #f1f3f5; color: var(--text-dark); }
    .btn-secondary:hover { background-color: #e2e8f0; transform: none; box-shadow: none; }
    .btn-danger { background-color: #e74c3c; }
    .btn-danger:hover { background-color: #c0392b; box-shadow: 0 4px 6px rgba(231, 76, 60, 0.2); }
    .btn-warning { background-color: #f1c40f; color: #333; }
    .btn-warning:hover { background-color: #f39c12; box-shadow: 0 4px 6px rgba(241, 196, 15, 0.2); }
    .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-dark); width: 100%; margin-bottom: 10px; }
    .btn-outline:hover { background: #f8f9fa; border-color: #bdc3c7; }

    .custom-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-success { background-color: var(--primary-light); color: var(--primary); }
    .badge-danger { background-color: #fdeaea; color: #e74c3c; }
    .badge-secondary { background-color: #f1f3f5; color: #7f8c8d; }
    .badge-warning { background-color: #fff3cd; color: #856404; }

    /* Tables */
    .table-responsive { width: 100%; overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .admin-table th { background-color: #fbfcfc; color: var(--text-muted); font-size: 11px; font-weight: 600; text-transform: uppercase; padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--border-color); letter-spacing: 0.5px; }
    .admin-table td { padding: 14px 20px; border-bottom: 1px solid var(--border-color); font-size: 13px; color: var(--text-dark); vertical-align: middle; }
    .admin-table tbody tr:hover { background-color: #fafbfc; }
    .text-link { color: var(--primary); font-weight: 600; text-decoration: none; transition: color 0.2s; }
    .text-link:hover { color: #27ae60; text-decoration: underline; }
    .table-footer { padding: 12px 20px; font-size: 12px; color: var(--text-muted); background: #fbfcfc; border-top: 1px solid var(--border-color); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="fa-solid fa-eye" style="color: var(--primary);"></i> Chi tiết Sân: {{ $court->name }}
    </h1>
    <a href="{{ route('admin.courts.index') }}" class="btn-action btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

@if ($errors->any())
<div style="background-color: #fdeaea; border: 1px solid #e74c3c; color: #e74c3c; padding: 16px; border-radius: var(--radius-md); margin-bottom: 24px; font-size: 14px;">
    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><i class="fa-solid fa-circle-exclamation"></i> Có lỗi xảy ra</strong>
    <ul style="margin: 0; padding-left: 24px;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="admin-grid">
    <div class="left-col">
        
        <div class="admin-card">
            <div class="card-header"><i class="fa-solid fa-circle-info" style="color: var(--text-muted);"></i> Thông tin cơ bản</div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>ID Hệ thống</label>
                        <div class="value">#{{ $court->id }}</div>
                    </div>
                    <div class="info-item">
                        <label>Tên sân con</label>
                        <div class="value" style="font-weight: 700; color: var(--primary);">{{ $court->name }}</div>
                    </div>
                    
                    <div class="info-item">
                        <label>Thuộc Cơ sở sân</label>
                        <div class="value">
                            <a href="{{ route('admin.venues.index') }}" class="text-link">
                                {{ $court->venue->name }}
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Chủ sân (Owner)</label>
                        <div class="value">{{ $court->venue->owner->name }}</div>
                    </div>
                    
                    <div class="info-item">
                        <label>Môn thể thao</label>
                        <div class="value">{{ $court->venue->sport->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <label>Địa chỉ</label>
                        <div class="value">{{ $court->venue->address }}</div>
                    </div>

                    <div class="info-item">
                        <label>Trạng thái hiển thị</label>
                        <div class="value">
                            @if ($court->status === 'active')
                                <span class="custom-badge badge-success"><i class="fa-solid fa-circle-check"></i> Đang hoạt động</span>
                            @else
                                <span class="custom-badge badge-danger"><i class="fa-solid fa-ban"></i> Đã bị ẩn</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Nhận đặt Online?</label>
                        <div class="value">
                            @if ($court->is_bookable_online)
                                <span class="custom-badge badge-success">Có nhận</span>
                            @else
                                <span class="custom-badge badge-secondary">Không nhận</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <label>Ngày khởi tạo</label>
                        <div class="value">{{ $court->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <label>Cập nhật lần cuối</label>
                        <div class="value">{{ $court->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header"><i class="fa-solid fa-sliders" style="color: var(--text-muted);"></i> Bảng điều khiển</div>
            <div class="card-body" style="display: flex; gap: 12px; flex-wrap: wrap;">
                <form method="POST" action="{{ route('admin.courts.toggle-status', $court) }}" style="margin: 0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-action {{ $court->status === 'active' ? 'btn-warning' : 'btn-success' }}" onclick="return confirm('Bạn chắc chắn muốn thay đổi trạng thái sân này?');">
                        @if ($court->status === 'active')
                            <i class="fa-solid fa-eye-slash"></i> Tạm ẩn sân này
                        @else
                            <i class="fa-solid fa-check"></i> Kích hoạt sân
                        @endif
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.courts.destroy', $court) }}" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-danger" onclick="return confirm('Hành động này không thể hoàn tác! Bạn chắc chắn muốn xóa sân này?');">
                        <i class="fa-solid fa-trash-can"></i> Xóa sân vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="right-col">
        <div class="admin-card">
            <div class="card-header"><i class="fa-solid fa-chart-pie" style="color: var(--text-muted);"></i> Thống kê dữ liệu</div>
            <div class="card-body">
                <div class="stat-item">
                    <div class="stat-label">Tổng số Ca/Giờ thiết lập</div>
                    <div class="stat-value">{{ $court->timeSlots->count() }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">ca</span></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Tổng Lượt đặt sân (All time)</div>
                    <div class="stat-value">{{ $court->bookings->count() }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">lượt</span></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Lịch đặt Đang chờ/Chưa đá</div>
                    <div class="stat-value warning">
                        {{ $court->bookings->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->count() }}
                        <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">đơn</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header"><i class="fa-solid fa-link" style="color: var(--text-muted);"></i> Liên kết nhanh</div>
            <div class="card-body" style="padding-bottom: 10px;">
                <a href="{{ route('admin.courts.index', ['venue_id' => $court->venue_id]) }}" class="btn-action btn-outline">
                    <i class="fa-solid fa-list-ul"></i> Xem các sân khác cùng Cơ sở
                </a>
                <a href="{{ route('admin.venues.index') }}" class="btn-action btn-outline">
                    <i class="fa-solid fa-building"></i> Đi tới Quản lý Cơ sở sân
                </a>
            </div>
        </div>
    </div>
</div>

@if ($court->bookings->count() > 0)
<div class="admin-card">
    <div class="card-header">
        <i class="fa-solid fa-calendar-check" style="color: var(--text-muted);"></i> Lịch đặt gần đây (10 đơn mới nhất)
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Mã Đơn</th>
                    <th style="width: 25%;">Khách hàng</th>
                    <th style="width: 15%;">Ngày đá</th>
                    <th style="width: 25%;">Khung giờ</th>
                    <th style="width: 15%; text-align: right;">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($court->bookings->sortByDesc('created_at')->take(10) as $booking)
                <tr>
                    <td><span style="color: var(--text-muted); font-weight: 600;">#{{ $booking->id }}</span></td>
                    <td style="font-weight: 500;">{{ $booking->user->name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}</td>
                    <td><i class="fa-regular fa-clock" style="color: var(--text-muted); font-size: 11px; margin-right: 4px;"></i> {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                    <td style="text-align: right;">
                        @if($booking->status === 'confirmed')
                            <span class="custom-badge badge-success">Đã chốt</span>
                        @elseif($booking->status === 'pending')
                            <span class="custom-badge badge-warning">Đang chờ</span>
                        @elseif($booking->status === 'cancelled' || $booking->status === 'rejected')
                            <span class="custom-badge badge-danger">Đã hủy</span>
                        @else
                            <span class="custom-badge badge-secondary">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($court->bookings->count() > 10)
        <div class="table-footer">
            <i class="fa-solid fa-ellipsis"></i> Và {{ $court->bookings->count() - 10 }} đơn đặt sân cũ hơn...
        </div>
    @endif
</div>
@endif

@if ($court->timeSlots->count() > 0)
<div class="admin-card">
    <div class="card-header">
        <i class="fa-solid fa-clock" style="color: var(--text-muted);"></i> Danh sách Ca giờ hoạt động
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Áp dụng ngày</th>
                    <th style="width: 35%;">Khung thời gian</th>
                    <th style="width: 40%;">Mức giá hiện tại</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($court->timeSlots->take(10) as $slot)
                <tr>
                    <td>
                        @if(is_null($slot->day_of_week))
                            <span class="custom-badge badge-success">Mọi ngày trong tuần</span>
                        @else
                            <span class="custom-badge badge-secondary">Thứ {{ $slot->day_of_week + 1 }}</span>
                        @endif
                    </td>
                    <td style="font-weight: 500;">
                        {{ substr($slot->start_time, 0, 5) }} <i class="fa-solid fa-arrow-right-long" style="color: var(--border-color); margin: 0 8px;"></i> {{ substr($slot->end_time, 0, 5) }}
                    </td>
                    <td>
                        @php $price = $slot->prices->first(); @endphp
                        <span style="color: var(--primary); font-weight: 700; font-size: 14px;">{{ number_format($price?->price ?? 0, 0, '.', ',') }} ₫</span>
                        @if($price?->price_type === 'peak')
                            <span class="custom-badge badge-warning" style="margin-left: 8px; padding: 2px 8px; font-size: 10px;">Giờ cao điểm</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($court->timeSlots->count() > 10)
        <div class="table-footer">
            <i class="fa-solid fa-ellipsis"></i> Và {{ $court->timeSlots->count() - 10 }} thiết lập ca giờ khác...
        </div>
    @endif
</div>
@endif

@endsection