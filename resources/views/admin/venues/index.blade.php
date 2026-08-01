@extends('admin.layouts.app')

@push('styles')
<style>
    /* Header Section */
    .breadcrumb-custom {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 8px;
        font-weight: 500;
    }
    .breadcrumb-custom span {
        color: var(--primary);
        font-weight: 600;
    }
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }
    .header-title-box h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 6px 0;
    }
    .header-title-box p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
    }
    .btn-add {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .btn-add:hover {
        background-color: #27ae60;
    }

    /* Stat Cards */
    .grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    .stat-card {
        padding: 20px;
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 130px;
    }
    .stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .badge-custom {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }
    .stat-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.5px;
    }
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1;
    }

    /* Card Variants */
    .c-total .stat-icon { background: #eafaf1; color: #2ecc71; }
    .c-total .badge-custom { background: #eafaf1; color: #2ecc71; }
    
    .c-active .stat-icon { background: #eafaf1; color: #2ecc71; }
    .c-active .badge-custom { background: #eafaf1; color: #2ecc71; }
    
    .c-maintenance .stat-icon { background: #fdedec; color: #e74c3c; }
    .c-maintenance .badge-custom { background: #fdedec; color: #e74c3c; }
    
    .c-locked .stat-icon { background: #f2f3f4; color: #7f8c8d; }
    .c-locked .badge-custom { background: #f2f3f4; color: #7f8c8d; }

    /* Filter Bar */
    .filter-bar-wrapper {
        background: var(--card-bg);
        padding: 16px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        display: flex;
        gap: 16px;
        align-items: center;
        margin-bottom: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .search-box {
        position: relative;
        flex: 1;
        max-width: 350px;
    }
    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 14px;
    }
    .search-box input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid var(--border-color);
        background-color: #f8f9fa;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        color: var(--text-dark);
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-dark);
        text-transform: uppercase;
    }
    .filter-select {
        padding: 10px 32px 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 13px;
        color: var(--text-muted);
        outline: none;
        appearance: none;
        background: #fff url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%237f8c8d%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E") no-repeat right 12px top 50%;
        background-size: 10px auto;
        min-width: 180px;
    }
    .btn-filter-icon {
        background: #f1f2f6;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        cursor: pointer;
    }

    /* Table Styles */
    .data-card {
        padding: 0;
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }
    .table-custom th {
        text-align: left;
        padding: 16px 20px;
        font-size: 12px;
        font-weight: 700;
        color: var(--text-dark);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
        background: #fafbfc;
    }
    .table-custom td {
        padding: 20px;
        font-size: 13px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-custom tr:last-child td {
        border-bottom: none;
    }

    /* Table Content Specifics */
    .venue-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .venue-img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background: #f1f2f6;
    }
    .venue-name {
        font-weight: 700;
        color: var(--text-dark);
        font-size: 13px;
        line-height: 1.4;
    }

    .badge-sport {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .sport-football { background: #eafaf1; color: #2ecc71; }
    .sport-badminton { background: #f4ecf7; color: #9b59b6; }
    .sport-tennis { background: #eafaf1; color: #2ecc71; }
    .sport-basketball { background: #ebf5fb; color: #3498db; }
    .sport-default { background: #f2f3f4; color: #7f8c8d; }

    .owner-name {
        font-size: 13px;
        color: var(--text-dark);
    }

    .address-text {
        font-size: 13px;
        color: var(--text-muted);
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .price-text {
        font-weight: 700;
        color: var(--text-dark);
    }

    .status-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .status-active .status-dot { background: #2ecc71; }
    .status-active { color: #2ecc71; }
    
    .status-maintenance .status-dot { background: #e74c3c; }
    .status-maintenance { color: #e74c3c; }
    
    .status-locked .status-dot { background: #7f8c8d; }
    .status-locked { color: #7f8c8d; }

    .btn-action-icon {
        background: transparent;
        border: none;
        color: var(--text-dark);
        font-size: 14px;
        cursor: pointer;
        padding: 4px 8px;
        transition: color 0.2s;
    }
    .btn-action-icon:hover {
        color: var(--primary);
    }
    .btn-delete-icon:hover {
        color: #e74c3c;
    }

    .venue-actions {
        display: inline-flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .venue-actions form { margin: 0; }
    .venue-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 32px;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .venue-action-btn:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(15, 23, 42, .12); }
    .venue-action-docs { color: #0369a1; background: #f0f9ff; border-color: #bae6fd; }
    .venue-action-approve { color: #fff; background: #16a34a; }
    .venue-action-reject { color: #dc2626; background: #fef2f2; border-color: #fecaca; }

    .pagination-wrapper {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border-color);
        background: #fff;
        border-bottom-left-radius: var(--radius-lg);
        border-bottom-right-radius: var(--radius-lg);
    }
    .pagination-info {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Modal Animation */
    @keyframes slideDown {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
@endpush

@section('content')

@if(session('success'))
    <div style="background-color: #eafaf1; color: #2ecc71; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #d4efdf; font-size: 14px; font-weight: 600;">
        <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background-color: #fdedec; color: #e74c3c; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fadbd8; font-size: 14px; font-weight: 600;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> {{ session('error') }}
    </div>
@endif

<!-- Header Section -->
<div class="breadcrumb-custom">
    Cơ sở hạ tầng > <span>Quản lý sân thể thao</span>
</div>
<div class="header-section">
    <div class="header-title-box">
        <h2>Quản lý sân thể thao</h2>
        <p>Theo dõi, điều chỉnh và cập nhật trạng thái các sân thể thao trong hệ thống.</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar-wrapper">
    <form action="{{ route('admin.venues.index') }}" method="GET" style="display: flex; width: 100%; gap: 24px; align-items: center;">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Tìm theo tên sân, địa chỉ, tên/email chủ sân..." value="{{ request('search') }}">
        </div>
        

        <button type="submit" class="btn-filter-icon">
            <i class="fa-solid fa-filter"></i>
        </button>
    </form>
</div>

<!-- Data Table -->
<div class="data-card">
    <table class="table-custom">
        <thead>
            <tr>
                <th>TÊN CƠ SỞ</th>
                <th>CHỦ SỞ HỮU</th>
                <th>ĐỊA CHỈ</th>
                <th>TRẠNG THÁI</th>
                <th>NGÀY TẠO</th>
                <th style="text-align: right;">HÀNH ĐỘNG</th>
            </tr>
        </thead>
        <tbody>
            @forelse($venues as $venue)
            
           {{-- KIỂM TRA XEM CƠ SỞ NÀY CÓ HỢP ĐỒNG NÀO ĐANG NHÁP/GỬI/CHỜ HIỆU LỰC KHÔNG --}}
            {{-- KIỂM TRA XEM CƠ SỞ NÀY CÓ HỢP ĐỒNG NÀO ĐANG NHÁP/GỬI/CHỜ HIỆU LỰC KHÔNG --}}
            @php
                $activeContract = \App\Models\Contract::where('venue_id', $venue->id)
                    // PHẢI THÊM 'terminated' VÀO ĐÚNG DÒNG NÀY THÌ ADMIN MỚI THẤY
                    ->whereIn('status', ['draft', 'sent', 'accepted', 'terminated']) 
                    ->orderBy('id', 'desc')
                    ->first();
            @endphp
            
            <tr>
                <td>
                    <div class="venue-info">
                        @php
                            $imgSrc = asset('images/default-venue.jpg');
                            $fallbackImg = 'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=100&h=100&fit=crop';

                            if($venue->images && $venue->images->count() > 0) {
                                $path = $venue->images->first()->image_path;
                                $imgSrc = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
                            } elseif ($venue->banner) {
                                $imgSrc = str_starts_with($venue->banner, 'http') ? $venue->banner : asset('storage/' . $venue->banner);
                            }
                        @endphp
                        <img src="{{ $imgSrc }}" class="venue-img" alt="Sân" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                        <div>
                            <div class="venue-name">{{ $venue->name }}</div>
                            <div class="text-muted small">{{ $venue->sport?->name ?? 'Chưa có môn' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="owner-name">{{ $venue->owner ? $venue->owner->name : 'Không có' }}</div>
                    @if($venue->owner?->email)
                        <div class="text-muted small">{{ $venue->owner->email }}</div>
                    @endif
                </td>
                <td title="{{ $venue->address }}">
                    <div class="address-text">{{ $venue->address }}</div>
                </td>
               <td>
                    @if($venue->status === 'active')
                        <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check"></i> Hoạt động</span>
                    @elseif($venue->status === 'approved')
                        
                        {{-- PHÂN LOẠI TRẠNG THÁI THEO TIẾN ĐỘ HỢP ĐỒNG --}}
                        @if($activeContract && $activeContract->status === 'accepted')
                            <!-- Lấy ngày tháng động từ CSDL -->
                            <span class="badge" style="background-color: #ccfbf1; color: #0f766e;" title="Chờ đến ngày {{ \Carbon\Carbon::parse($activeContract->start_date)->format('d/m/Y') }} để tự động kích hoạt">
                                <i class="fa-solid fa-clock"></i> Chờ hiệu lực ({{ \Carbon\Carbon::parse($activeContract->start_date)->format('d/m') }})
                            </span>
                        @elseif($activeContract && $activeContract->status === 'sent')
                            <span class="badge" style="background-color: #f3e8ff; color: #7e22ce;"><i class="fa-solid fa-pen-nib"></i> Chờ ký HĐ</span>
                        @elseif($activeContract && $activeContract->status === 'draft')
                            <span class="badge" style="background-color: #e2e8f0; color: #475569;"><i class="fa-solid fa-file-lines"></i> Đang nháp HĐ</span>
                        @else
                            <span class="badge" style="background-color: #e0f2fe; color: #0284c7;"><i class="fa-solid fa-check-double"></i> Đã duyệt</span>
                        @endif

                    @elseif($venue->status === 'pending')
                        <span class="badge bg-warning-subtle text-warning">Chờ duyệt</span>
                    @elseif($venue->status === 'rejected')
                        <span class="badge bg-danger-subtle text-danger">Từ chối</span>
                    @elseif($venue->status === 'inactive')
                        <span class="badge bg-secondary-subtle text-secondary">Ngừng HĐ</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($venue->status) }}</span>
                    @endif
                </td>
                <td>
                    <div class="text-muted">{{ $venue->created_at ? $venue->created_at->format('d/m/Y') : '-' }}</div>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    <div class="venue-actions">
                        
                        {{-- THÊM MỚI: NÚT BÁO HIỆU CHỦ SÂN VỪA CẬP NHẬT THÔNG TIN --}}
                        @if($venue->pendingUpdateRequest)
                            <a href="{{ route('admin.venues.documents', $venue->id) }}" 
                               class="venue-action-btn" 
                               style="background-color: #f59e0b; color: white; border: none; box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);">
                                <i class="fa-solid fa-bell fa-shake"></i> Có thay đổi mới
                            </a>
                        @endif

                        <a href="{{ route('admin.venues.documents', $venue->id) }}"
                           class="venue-action-btn venue-action-docs">
                            <i class="fa-regular fa-folder-open"></i> Hồ sơ
                        </a>
                        
                        {{-- (Các nút form Duyệt/Từ chối của bạn ở dưới giữ nguyên) --}}
                    @if($venue->status === 'pending')
                            <form action="{{ route('admin.venues.approve', $venue->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="venue-action-btn venue-action-approve"><i class="fa-solid fa-check"></i> Duyệt</button>
                            </form>

                            <form action="{{ route('admin.venues.reject', $venue->id) }}"
                                  method="POST"
                                  onsubmit="return rejectVenueConfirm(this);">
                                @csrf
                                <input type="hidden" name="reject_reason" class="reject-reason-input">
                                <button type="submit" class="venue-action-btn venue-action-reject">
                                    <i class="fa-solid fa-xmark"></i> Từ chối
                                </button>
                            </form>
                        @endif

                        {{-- THÊM MỚI ĐOẠN NÀY: Nút Tạo hợp đồng khi vừa duyệt xong --}}
                       {{-- HIỂN THỊ NÚT DỰA VÀO TIẾN ĐỘ HỢP ĐỒNG --}}
                        {{-- HIỂN THỊ NÚT DỰA VÀO TIẾN ĐỘ HỢP ĐỒNG --}}
                       {{-- XỬ LÝ CÁC NÚT LIÊN QUAN ĐẾN HỢP ĐỒNG --}}
                        @if(in_array($venue->status, ['approved', 'active', 'inactive']))
                            @if(!$activeContract)
                                <!-- Chưa có HĐ -> Hiện nút TẠO HỢP ĐỒNG -->
                                <a href="{{ route('admin.contracts.create', ['owner_id' => $venue->owner_id, 'venue_id' => $venue->id]) }}" 
                                   class="venue-action-btn" 
                                   style="background-color: #0ea5e9; color: white; border: none; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);">
                                    <i class="fa-solid fa-file-contract"></i> Tạo hợp đồng
                                </a>
                            @elseif($activeContract->status === 'draft')
                                <!-- Đang nháp -> Hiện nút SỬA HĐ NHÁP -->
                                <a href="{{ route('admin.contracts.edit', $activeContract->id) }}" 
                                   class="venue-action-btn" 
                                   style="background-color: #64748b; color: white; border: none;">
                                    <i class="fa-solid fa-pen-to-square"></i> Sửa HĐ Nháp
                                </a>
                            @elseif(in_array($activeContract->status, ['sent', 'accepted']))
                                <!-- Đang chờ ký hoặc Đã ký -> Hiện nút XEM HĐ -->
                                <a href="{{ route('admin.contracts.show', $activeContract->id) }}" 
                                   class="venue-action-btn" 
                                   style="background-color: #10b981; color: white; border: none; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">
                                    <i class="fa-solid fa-eye"></i> Xem HĐ
                                </a>
                            @elseif($activeContract->status === 'terminated')
                                <!-- Hợp đồng đã chấm dứt -> Hiện nút XEM HĐ CŨ (Màu xám) VÀ nút KÝ HĐ MỚI (Màu xanh) -->
                                <a href="{{ route('admin.contracts.show', $activeContract->id) }}" 
                                   class="venue-action-btn" 
                                   style="background-color: #64748b; color: white; border: none;">
                                    <i class="fa-solid fa-file-pdf"></i> Xem HĐ cũ
                                </a>
                                <a href="{{ route('admin.contracts.create', ['owner_id' => $venue->owner_id, 'venue_id' => $venue->id]) }}" 
                                   class="venue-action-btn" 
                                   style="background-color: #0ea5e9; color: white; border: none; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);">
                                    <i class="fa-solid fa-file-contract"></i> Ký HĐ mới
                                </a>
                            @endif
                        @endif
                        
                    </div>
                </td>
            </tr>
           {{-- <!-- Modal Cấu Hình Hoa Hồng Riêng cho từng Venue -->
<div class="modal fade" id="commissionModal{{ $venue->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <form action="{{ route('admin.venues.commission.update', $venue->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background-color: #fffbeb; border-bottom: 1px solid #fde68a; border-radius: 12px 12px 0 0; padding: 16px 24px;">
                    <h5 class="modal-title" style="color: #d97706; font-weight: 700; font-size: 16px;">
                        <i class="fa-solid fa-percent me-2"></i> Hoa Hồng Riêng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body text-start" style="padding: 24px;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 13px; font-weight: 700; color: #495057; text-transform: uppercase;">Cơ sở áp dụng</label>
                        <div style="padding: 10px 16px; background: #f8f9fa; border-radius: 8px; font-weight: 600; color: #212529; border: 1px solid #e9ecef;">
                            {{ $venue->name }}
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="form-label" style="font-size: 13px; font-weight: 700; color: #495057; text-transform: uppercase;">Tỷ lệ hoa hồng (%)</label>
                        <input type="number" step="0.01" class="form-control" name="commission_rate" 
                               value="{{ $venue->commission_rate }}" placeholder="VD: 15.5" 
                               style="border-radius: 8px; padding: 12px 16px; font-size: 16px; font-weight: 600; border: 2px solid #fde68a;">
                        
                        <div class="alert alert-warning mt-3 mb-0" style="background-color: #fffbeb; border-color: #fde68a; color: #92400e; font-size: 13px; padding: 12px; border-radius: 8px;">
                            <i class="fa-solid fa-circle-info me-1"></i> <strong>Lưu ý:</strong><br>
                            - Nhập số để áp dụng mức phí đặc biệt cho cơ sở này.<br>
                            - <strong>Xóa trắng ô nhập</strong> nếu muốn cơ sở này sử dụng lại mức phí mặc định của hệ thống.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-weight: 600; border-radius: 8px; padding: 8px 20px;">Đóng</button>
                    <button type="submit" class="btn" style="background-color: #f59e0b; color: white; font-weight: 600; border-radius: 8px; padding: 8px 20px; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);">
                        Lưu Cấu Hình
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">Không tìm thấy cơ sở sân nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Hiển thị {{ $venues->firstItem() ?? 0 }}-{{ $venues->lastItem() ?? 0 }} trên tổng số {{ $venues->total() }} sân
        </div>
        <div class="pagination-links">
            {{ $venues->links('vendor.pagination.admin') }}
        </div>
    </div>
</div>

{{-- <!-- Reject venue modal -->
<div class="modal fade" id="rejectVenueModal" tabindex="-1" aria-labelledby="rejectVenueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="rejectVenueForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="rejectVenueModalLabel">Từ chối cơ sở sân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <label for="reject_reason" class="form-label">Lý do từ chối</label>
                <textarea id="reject_reason" name="reject_reason" class="form-control" rows="4" minlength="5" required placeholder="Nhập lý do từ chối (ít nhất 5 ký tự)..."></textarea>
                <div class="form-text">Lý do này sẽ được lưu cùng hồ sơ của chủ sân.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
            </div>
        </form>
    </div>
</div> --}}
<!-- ================= KHU VỰC CHỨA MODAL (NẰM NGOÀI BẢNG) ================= -->
@foreach($venues as $venue)
    <!-- Modal Cấu Hình Hoa Hồng Riêng cho từng Venue -->
    <div class="modal fade" id="commissionModal{{ $venue->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <form action="{{ route('admin.venues.commission.update', $venue->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header" style="background-color: #fffbeb; border-bottom: 1px solid #fde68a; border-radius: 12px 12px 0 0; padding: 16px 24px;">
                        <h5 class="modal-title" style="color: #d97706; font-weight: 700; font-size: 16px;">
                            <i class="fa-solid fa-percent me-2"></i> Hoa Hồng Riêng
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body text-start" style="padding: 24px;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 13px; font-weight: 700; color: #495057; text-transform: uppercase;">Cơ sở áp dụng</label>
                            <div style="padding: 10px 16px; background: #f8f9fa; border-radius: 8px; font-weight: 600; color: #212529; border: 1px solid #e9ecef;">
                                {{ $venue->name }}
                            </div>
                        </div>
                        
                        <div class="mt-4">
                        <label class="form-label" style="font-size: 13px; font-weight: 700; color: #495057; text-transform: uppercase;">Tỷ lệ hoa hồng (%)</label>
                        
                        <!-- TRƯỜNG ẨN ĐỂ NHẬN DIỆN ĐANG SỬA SÂN NÀO -->
                        <input type="hidden" name="error_venue_id" value="{{ $venue->id }}">

                        <input type="number" step="0.01" 
                               class="form-control @if($errors->has('commission_rate') && old('error_venue_id') == $venue->id) is-invalid @endif" 
                               name="commission_rate" 
                               value="{{ old('error_venue_id') == $venue->id ? old('commission_rate') : $venue->commission_rate }}" 
                               placeholder="VD: 15.5" 
                               style="border-radius: 8px; padding: 12px 16px; font-size: 16px; font-weight: 600; border: 2px solid #fde68a;">
                        
                        <!-- HIỂN THỊ LỖI MÀU ĐỎ -->
                        @if($errors->has('commission_rate') && old('error_venue_id') == $venue->id)
                            <small class="text-danger d-block mt-2 fw-bold">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('commission_rate') }}
                            </small>
                        @endif
                        
                        <div class="alert alert-warning mt-3 mb-0" style="background-color: #fffbeb; border-color: #fde68a; color: #92400e; font-size: 13px; padding: 12px; border-radius: 8px;">
                            <i class="fa-solid fa-circle-info me-1"></i> <strong>Lưu ý:</strong><br>
                            - Nhập số để áp dụng mức phí đặc biệt cho cơ sở này.<br>
                            - <strong>Xóa trắng ô nhập</strong> nếu muốn cơ sở này sử dụng lại mức phí mặc định của hệ thống.
                        </div>
                    </div>
                    </div>
                    
                    <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 16px 24px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-weight: 600; border-radius: 8px; padding: 8px 20px;">Đóng</button>
                        <button type="submit" class="btn" style="background-color: #f59e0b; color: white; font-weight: 600; border-radius: 8px; padding: 8px 20px; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);">
                            Lưu Cấu Hình
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
    function loadVenueDocs(id, docs) {
        const content = document.getElementById('venueDocsContent');
        if (!docs || !docs.owner_name) {
            content.innerHTML = '<p class="text-muted mb-0">Không có hồ sơ pháp lý.</p>';
            return;
        }

        const files = [
            ['CCCD mặt trước', docs.citizen_front_image],
            ['CCCD mặt sau', docs.citizen_back_image],
            ['Giấy phép kinh doanh', docs.business_license_file],
            ['Hợp đồng thuê mặt bằng', docs.rental_contract_file],
            ['Giấy chứng nhận quyền sử dụng đất', docs.land_certificate_file],
        ];

        const fileHtml = files.map(([label, path]) => {
            if (!path) return '';
            const isImage = /\.(png|jpe?g|webp|gif)$/i.test(path);
            const url = path.startsWith('http') ? path : `{{ asset('storage') }}/${path}`;
            return `
                <div class="mb-3">
                    <h6 class="fw-semibold">${label}</h6>
                    ${isImage ? `<img src="${url}" class="img-fluid rounded border" alt="${label}">` : `<a href="${url}" target="_blank" class="btn btn-outline-primary btn-sm">Xem tài liệu</a>`}
                </div>
            `;
        }).join('');

        content.innerHTML = `
            <div class="row g-3">
                <div class="col-12 col-md-6"><strong>Chủ sở hữu:</strong> ${docs.owner_name || '-'}</div>
                <div class="col-12 col-md-6"><strong>CCCD:</strong> ${docs.citizen_id || '-'}</div>
                <div class="col-12 col-md-6"><strong>Số giấy phép:</strong> ${docs.business_license_number || '-'}</div>
                <div class="col-12 col-md-6"><strong>Ngân hàng:</strong> ${docs.bank_name || '-'}</div>
                <div class="col-12 col-md-6"><strong>Số tài khoản:</strong> ${docs.bank_account_number || '-'}</div>
                <div class="col-12 col-md-6"><strong>Chủ tài khoản:</strong> ${docs.bank_account_holder || '-'}</div>
                ${docs.reject_reason ? `<div class="col-12"><div class="alert alert-danger mb-0">Lý do từ chối: ${docs.reject_reason}</div></div>` : ''}
            </div>
            <div class="mt-3">${fileHtml}</div>
        `;
    }
    function rejectVenueConfirm(form) {
    const reason = prompt('Nhập lý do từ chối cơ sở sân:');

    if (reason === null) {
        return false;
    }

    if (reason.trim().length < 5) {
        alert('Lý do từ chối phải có ít nhất 5 ký tự.');
        return false;
    }

    form.querySelector('.reject-reason-input').value = reason.trim();

    return confirm('Bạn có chắc chắn muốn từ chối cơ sở sân này không?');
}
@if($errors->any() && old('error_venue_id'))
        document.addEventListener('DOMContentLoaded', function () {
            // Tự động tìm đúng ID của Modal vừa bị lỗi và bật nó lên lại
            var modalId = 'commissionModal{{ old("error_venue_id") }}';
            var modalElement = document.getElementById(modalId);
            if(modalElement) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        });
    @endif
</script>
@endpush
