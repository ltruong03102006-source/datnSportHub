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
    <button class="btn-add">
        <i class="fa-solid fa-circle-plus"></i> Thêm sân mới
    </button>
</div>

<!-- Stat Cards -->
<div class="grid-4">
    <div class="stat-card c-total">
        <div class="stat-card-top">
            <div class="stat-icon"><i class="fa-regular fa-building"></i></div>
            <div class="badge-custom">+12%</div>
        </div>
        <div>
            <div class="stat-label">TỔNG SỐ SÂN</div>
            <div class="stat-number">{{ $totalVenues }}</div>
        </div>
    </div>

    <div class="stat-card c-active">
        <div class="stat-card-top">
            <div class="stat-icon"><i class="fa-regular fa-circle-check"></i></div>
            <div class="badge-custom">Hoạt động</div>
        </div>
        <div>
            <div class="stat-label">SÂN SẴN SÀNG</div>
            <div class="stat-number">{{ $activeVenues }}</div>
        </div>
    </div>

    <div class="stat-card c-maintenance">
        <div class="stat-card-top">
            <div class="stat-icon"><i class="fa-solid fa-wrench"></i></div>
            <div class="badge-custom">Bảo trì</div>
        </div>
        <div>
            <div class="stat-label">ĐANG SỬA CHỮA</div>
            <div class="stat-number">{{ $maintenanceVenues }}</div>
        </div>
    </div>

    <div class="stat-card c-locked">
        <div class="stat-card-top">
            <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
            <div class="badge-custom">Vô hiệu</div>
        </div>
        <div>
            <div class="stat-label">ĐÃ KHÓA</div>
            <div class="stat-number">{{ $lockedVenues }}</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar-wrapper">
    <form action="{{ route('admin.venues.index') }}" method="GET" style="display: flex; width: 100%; gap: 24px; align-items: center;">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Tìm kiếm theo tên sân hoặc chủ sân..." value="{{ request('search') }}">
        </div>
        
        <div class="filter-group">
            <span class="filter-label">LOẠI MÔN:</span>
            <select class="filter-select" name="sport_id" onchange="this.form.submit()">
                <option value="">Tất cả môn thể thao</option>
                @foreach($sports as $sport)
                    <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                        {{ $sport->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <span class="filter-label">TRẠNG THÁI:</span>
            <select class="filter-select" name="status" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Sẵn sàng</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Đang sửa chữa</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Đã khóa</option>
            </select>
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
                <th>TÊN SÂN</th>
                <th>LOẠI SÂN</th>
                <th>CHỦ SÂN</th>
                <th>ĐỊA CHỈ</th>
                <th>GIÁ THUÊ</th>
                <th>TRẠNG THÁI</th>
                <th style="text-align: right;">HÀNH ĐỘNG</th>
            </tr>
        </thead>
        <tbody>
            @forelse($venues as $venue)
            <tr>
                <td>
                    <div class="venue-info">
                        <!-- Xử lý ảnh sân an toàn hơn -->
                        @php
                            $imgSrc = asset('images/default-venue.jpg');
                            
                            // Danh sách ảnh phụ trợ nếu lỗi
                            $mockImages = [
                                'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=100&h=100&fit=crop',
                                'https://images.unsplash.com/photo-1628260412297-a3377e45006f?w=100&h=100&fit=crop',
                                'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=100&h=100&fit=crop'
                            ];
                            $fallbackImg = $mockImages[array_rand($mockImages)];

                            if($venue->images && $venue->images->count() > 0) {
                                $path = $venue->images->first()->image_path;
                                $imgSrc = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
                            } elseif ($venue->banner) {
                                $imgSrc = str_starts_with($venue->banner, 'http') ? $venue->banner : asset('storage/' . $venue->banner);
                            } else {
                                $imgSrc = $fallbackImg;
                            }
                        @endphp
                        <img src="{{ $imgSrc }}" class="venue-img" alt="Sân" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                        <div class="venue-name" style="width: 120px; white-space: normal;">{{ $venue->name }}</div>
                    </div>
                </td>
                <td>
                    @php
                        $sportName = $venue->sport ? $venue->sport->name : 'N/A';
                        $sportClass = 'sport-default';
                        if (stripos($sportName, 'bóng đá') !== false || stripos($sportName, 'football') !== false) $sportClass = 'sport-football';
                        if (stripos($sportName, 'cầu lông') !== false || stripos($sportName, 'badminton') !== false) $sportClass = 'sport-badminton';
                        if (stripos($sportName, 'tennis') !== false) $sportClass = 'sport-tennis';
                        if (stripos($sportName, 'bóng rổ') !== false || stripos($sportName, 'basketball') !== false) $sportClass = 'sport-basketball';
                    @endphp
                    <span class="badge-sport {{ $sportClass }}">{{ ucfirst($sportName) }}</span>
                </td>
                <td>
                    <div class="owner-name">{{ $venue->owner ? $venue->owner->name : 'Không có' }}</div>
                </td>
                <td title="{{ $venue->address }}">
                    <div class="address-text">{{ $venue->address }}</div>
                </td>
                <td>
                    <div class="price-text">
                        @if($venue->min_price && $venue->max_price)
                            @if($venue->min_price == $venue->max_price)
                                {{ number_format($venue->min_price, 0, ',', '.') }}đ/h
                            @else
                                {{ number_format($venue->min_price, 0, ',', '.') }}đ - {{ number_format($venue->max_price, 0, ',', '.') }}đ
                            @endif
                        @else
                            N/A
                        @endif
                    </div>
                </td>
                <td>
                    @if($venue->status === 'active')
                        <div class="status-badge status-active"><span class="status-dot"></span> Sẵn sàng</div>
                    @elseif($venue->status === 'pending')
                        <div class="status-badge status-maintenance"><span class="status-dot"></span> Đang sửa chữa</div>
                    @else
                        <div class="status-badge status-locked"><span class="status-dot"></span> Đã khóa</div>
                    @endif
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    <button type="button" class="btn-action-icon" title="Sửa thông tin" 
                            onclick="openEditModal({
                                id: {{ $venue->id }},
                                name: '{{ addslashes($venue->name) }}',
                                sport_id: {{ $venue->sport_id }},
                                address: '{{ addslashes($venue->address) }}',
                                status: '{{ $venue->status }}'
                            })">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <form action="{{ route('admin.venues.destroy', $venue->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cơ sở sân này? Tất cả các sân con và lịch đặt liên quan sẽ bị xóa vĩnh viễn!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action-icon btn-delete-icon" title="Xóa sân">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">Không tìm thấy cơ sở sân nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Hiển thị {{ $venues->firstItem() ?? 0 }}-{{ $venues->lastItem() ?? 0 }} trên tổng số {{ $totalVenues }} sân
        </div>
        <div class="pagination-links">
            {{ $venues->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Edit Venue Modal -->
<div id="editVenueModal" class="custom-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div class="modal-content" style="background-color: #fff; padding: 30px; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); position: relative; animation: slideDown 0.3s ease-out;">
        <span class="close-modal" onclick="closeEditModal()" style="position: absolute; right: 20px; top: 20px; font-size: 20px; color: var(--text-muted); cursor: pointer; font-weight: bold;">&times;</span>
        <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 700; color: var(--text-dark);">Chỉnh sửa Cơ sở sân</h3>
        
        <form id="editVenueForm" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-dark);">Tên sân</label>
                <input type="text" id="modalVenueName" name="name" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; color: var(--text-dark);">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-dark);">Loại môn thể thao</label>
                <select id="modalVenueSport" name="sport_id" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%237f8c8d%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px top 50%; background-size: 10px auto; color: var(--text-dark);">
                    @foreach($sports as $sport)
                        <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-dark);">Địa chỉ</label>
                <input type="text" id="modalVenueAddress" name="address" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; color: var(--text-dark);">
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-dark);">Trạng thái</label>
                <select id="modalVenueStatus" name="status" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%237f8c8d%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px top 50%; background-size: 10px auto; color: var(--text-dark);">
                    <option value="active">Sẵn sàng (Active)</option>
                    <option value="pending">Đang sửa chữa (Pending)</option>
                    <option value="inactive">Đã khóa (Inactive)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeEditModal()" style="padding: 10px 18px; border: 1px solid var(--border-color); background-color: #fff; color: var(--text-dark); border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 10px 18px; border: none; background-color: var(--primary); color: white; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditModal(venue) {
        document.getElementById('modalVenueName').value = venue.name;
        document.getElementById('modalVenueSport').value = venue.sport_id;
        document.getElementById('modalVenueAddress').value = venue.address;
        document.getElementById('modalVenueStatus').value = venue.status;
        
        const form = document.getElementById('editVenueForm');
        form.action = `/admin/venues/${venue.id}`;
        
        const modal = document.getElementById('editVenueModal');
        modal.style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editVenueModal').style.display = 'none';
    }

    // Close when clicking outside modal
    window.onclick = function(event) {
        const modal = document.getElementById('editVenueModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
@endpush
