@extends('admin.layouts.app')

@section('title', 'Quản Lý Lịch Đặt Sân')

@push('styles')
<style>
    /* Header & Layout */
    .page-header { margin-bottom: 24px; }
    .page-title { font-size: 24px; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
    .page-desc { color: var(--text-muted); font-size: 14px; margin-top: 4px; }
    
    /* Cards */
    .admin-card { background: var(--card-bg); border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: #fbfcfc; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; gap: 10px; font-size: 15px; }
    .card-body { padding: 20px; }
    
    /* Forms & Filters */
    .filter-grid { display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .form-control, .form-select { padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--text-dark); background-color: #fff; outline: none; transition: all 0.2s; height: 42px;}
    .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
    
    /* Tables */
    .table-responsive { width: 100%; overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; min-width: 950px; }
    .admin-table th { background-color: #fbfcfc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--border-color); }
    .admin-table td { padding: 14px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-dark); vertical-align: middle; }
    .admin-table tbody tr:hover { background-color: #fafbfc; }
    
    /* Badges */
    .custom-badge { padding: 6px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .badge-gray { background-color: #f1f3f5; color: #495057; }
    .badge-warning { background-color: #fff3cd; color: #856404; }
    .badge-info { background-color: #e0f2fe; color: #0284c7; }
    .badge-success { background-color: var(--primary-light); color: var(--primary); }
    .badge-danger { background-color: #fdeaea; color: #e74c3c; }
    
    /* Buttons */
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; height: 42px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { background-color: #27ae60; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(46, 204, 113, 0.2); }
    .btn-secondary { background-color: #f1f3f5; color: var(--text-dark); }
    .btn-secondary:hover { background-color: #e2e8f0; }
    
    /* Action Icons */
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all 0.2s; color: white; text-decoration: none; font-size: 12px; }
    .btn-icon.info { background-color: #3498db; }
    .btn-icon.info:hover { background-color: #2980b9; transform: translateY(-1px); }

    /* Pagination container */
    .pagination-container { padding: 16px 20px; border-top: 1px solid var(--border-color); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i> Lịch Đặt Toàn Hệ Thống</h1>
    <p class="page-desc">Quản lý và theo dõi toàn bộ các yêu cầu đặt sân từ khách hàng.</p>
</div>

<div class="admin-card">
    <div class="card-header"><i class="fa-solid fa-filter" style="color: var(--text-muted);"></i> Tìm kiếm & Lọc đơn</div>
    <div class="card-body">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="filter-grid">
            <div class="form-group" style="width: 200px;">
                <label class="form-label">Trạng thái đơn</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 250px;">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tìm tên khách hàng, email, hoặc tên sân..." value="{{ request('search') }}">
            </div>
            
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Lọc dữ liệu</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header"><i class="fa-solid fa-table-list" style="color: var(--text-muted);"></i> Danh sách Đơn đặt sân (Bookings)</div>
    
    @if($bookings->count() > 0)
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Mã Đơn</th>
                        <th style="width: 20%;">Khách Đặt</th>
                        <th style="width: 20%;">Cơ Sở / Sân Con</th>
                        <th style="width: 17%;">Lịch Đá</th>
                        <th style="width: 12%;">Tổng Tiền</th>
                        <th style="width: 13%;">Trạng Thái</th>
                        <th style="width: 10%; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td><span class="custom-badge badge-gray" style="font-size: 12px;">#{{ $booking->id }}</span></td>
                            
                            <td>
                                @if($booking->user)
                                    <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 2px;">{{ $booking->user->name }}</div>
                                    <div style="font-size: 12px; color: var(--text-muted);">{{ $booking->user->email }}</div>
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">Khách ẩn danh</span>
                                @endif
                            </td>
                            
                            <td>
                                @if($booking->court && $booking->court->venue)
                                    <div style="font-weight: 600; color: var(--primary); margin-bottom: 2px;">{{ $booking->court->venue->name }}</div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><i class="fa-solid fa-layer-group" style="font-size: 10px;"></i> Sân: {{ $booking->court->name }}</div>
                                @else
                                    <span style="color: #e74c3c; font-style: italic; font-size: 12px;">Sân không tồn tại</span>
                                @endif
                            </td>
                            
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: var(--text-dark); margin-bottom: 2px;">
                                    <i class="fa-regular fa-calendar" style="color: var(--text-muted); width: 14px;"></i> 
                                    {{ $booking->slot_date ? \Carbon\Carbon::parse($booking->slot_date)->format('d/m/Y') : 'N/A' }}
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    <i class="fa-regular fa-clock" style="width: 14px;"></i> 
                                    {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                                </div>
                            </td>
                            
                            <td>
                                <span style="font-weight: 700; color: #27ae60; font-size: 15px;">{{ number_format($booking->total_price, 0, ',', '.') }} ₫</span>
                            </td>
                            
                            <td>
                                @php
                                    $statusClass = 'badge-gray';
                                    $statusText = $booking->status;
                                    $statusIcon = 'fa-circle-info';
                                    
                                    switch($booking->status) {
                                        case 'pending': 
                                            $statusClass = 'badge-warning'; 
                                            $statusText = 'Chờ duyệt'; 
                                            $statusIcon = 'fa-clock'; 
                                            break;
                                        case 'confirmed': 
                                            $statusClass = 'badge-info'; 
                                            $statusText = 'Đã chốt'; 
                                            $statusIcon = 'fa-check'; 
                                            break;
                                        case 'completed': 
                                            $statusClass = 'badge-success'; 
                                            $statusText = 'Đã đá xong'; 
                                            $statusIcon = 'fa-check-double'; 
                                            break;
                                        case 'cancelled': 
                                            $statusClass = 'badge-danger'; 
                                            $statusText = 'Đã hủy'; 
                                            $statusIcon = 'fa-xmark'; 
                                            break;
                                    }
                                @endphp
                                <span class="custom-badge {{ $statusClass }}">
                                    <i class="fa-solid {{ $statusIcon }}"></i> {{ $statusText }}
                                </span>
                            </td>
                            
                            <td style="text-align: right;">
                                <button type="button" class="btn-icon info" title="Xem chi tiết đơn">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($bookings->hasPages())
        <div class="pagination-container">
            {{ $bookings->links() }}
        </div>
        @endif
        
    @else
        <div style="padding: 60px 20px; text-align: center;">
            <i class="fa-regular fa-calendar-xmark" style="font-size: 48px; color: #bdc3c7; margin-bottom: 16px;"></i>
            <h5 style="font-size: 18px; color: var(--text-dark); margin-bottom: 8px;">Không tìm thấy lịch đặt nào</h5>
            <p style="color: var(--text-muted); font-size: 14px;">Chưa có dữ liệu hoặc không có đơn đặt sân nào khớp với bộ lọc.</p>
        </div>
    @endif
</div>
@endsection