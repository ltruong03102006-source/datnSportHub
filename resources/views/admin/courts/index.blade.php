@extends('admin.layouts.app')

@section('title', 'Quản lý Sân')

@push('styles')
<style>
    /* Header & Layout */
    .page-header { margin-bottom: 24px; }
    .page-title { font-size: 24px; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
    
    /* Stats Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .stat-card { background: var(--card-bg); border-radius: var(--radius-md); padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; flex-direction: column; }
    .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .stat-value { font-size: 28px; font-weight: 700; color: var(--text-dark); }
    
    /* Cards */
    .admin-card { background: var(--card-bg); border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border-color); background: #fbfcfc; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; gap: 10px; font-size: 15px; }
    .card-body { padding: 20px; }
    
    /* Forms & Filters */
    .filter-grid { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto; gap: 16px; align-items: end; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .form-control, .form-select { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--text-dark); background-color: #fff; outline: none; transition: all 0.2s; }
    .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
    
    /* Tables */
    .table-responsive { width: 100%; overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .admin-table th { background-color: #fbfcfc; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--border-color); }
    .admin-table td { padding: 14px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-dark); vertical-align: middle; }
    .admin-table tbody tr:hover { background-color: #fafbfc; }
    
    /* Badges */
    .custom-badge { padding: 6px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    .badge-gray { background-color: #f1f3f5; color: #495057; }
    .badge-success { background-color: var(--primary-light); color: var(--primary); }
    .badge-danger { background-color: #fdeaea; color: #e74c3c; }
    
    /* Buttons */
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
    .btn-primary { background-color: var(--primary); color: white; }
    .btn-primary:hover { background-color: #27ae60; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(46, 204, 113, 0.2); }
    .btn-primary:disabled { background-color: #95a5a6; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-secondary { background-color: #f1f3f5; color: var(--text-dark); }
    .btn-secondary:hover { background-color: #e2e8f0; }
    
    /* Table Action Buttons */
    .action-group { display: flex; gap: 6px; }
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all 0.2s; color: white; text-decoration: none; font-size: 12px; }
    .btn-icon.info { background-color: #3498db; }
    .btn-icon.info:hover { background-color: #2980b9; }
    .btn-icon.warning { background-color: #f1c40f; color: #333; }
    .btn-icon.warning:hover { background-color: #f39c12; }
    .btn-icon.success { background-color: var(--primary); }
    .btn-icon.success:hover { background-color: #27ae60; }
    .btn-icon.primary { background-color: #34495e; }
    .btn-icon.primary:hover { background-color: #2c3e50; }
    .btn-icon.danger { background-color: #e74c3c; }
    .btn-icon.danger:hover { background-color: #c0392b; }

    /* Custom Checkbox */
    .custom-checkbox { width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary); }
    
    /* Batch Actions Bar */
    .batch-actions { padding: 16px 20px; background: #fbfcfc; border-top: 1px solid var(--border-color); display: flex; gap: 10px; align-items: center; }
    
    /* Custom Modal */
    .custom-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s; }
    .custom-modal-overlay.active { opacity: 1; visibility: visible; }
    .custom-modal { background: #fff; width: 100%; max-width: 450px; border-radius: var(--radius-lg); box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: translateY(20px); transition: all 0.3s; }
    .custom-modal-overlay.active .custom-modal { transform: translateY(0); }
    .modal-header { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0; }
    .modal-close { background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer; }
    .modal-body { padding: 20px; }
    .modal-footer { padding: 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px; background: #fbfcfc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); }

    /* Pagination container */
    .pagination-container { padding: 16px 20px; border-top: 1px solid var(--border-color); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Quản lý Danh sách Sân</h1>
</div>

<div class="stats-grid">
    <div class="stat-card" style="border-left: 4px solid #3498db;">
        <div class="stat-label" style="color: #3498db;">Tổng số sân</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--primary);">
        <div class="stat-label" style="color: var(--primary);">Đang hoạt động</div>
        <div class="stat-value">{{ $stats['active'] }}</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #e74c3c;">
        <div class="stat-label" style="color: #e74c3c;">Sân đã ẩn</div>
        <div class="stat-value">{{ $stats['inactive'] }}</div>
    </div>
</div>

@if ($errors->any())
<div style="background-color: #fdeaea; border: 1px solid #e74c3c; color: #e74c3c; padding: 16px; border-radius: var(--radius-md); margin-bottom: 24px; font-size: 14px;">
    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><i class="fa-solid fa-circle-exclamation"></i> Có lỗi xảy ra</strong>
    <ul style="margin: 0; padding-left: 24px;">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

@if (session('success'))
<div style="background-color: var(--primary-light); border: 1px solid var(--primary); color: var(--primary); padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 500; font-size: 14px;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<div class="admin-card">
    <div class="card-header"><i class="fa-solid fa-filter" style="color: var(--text-muted);"></i> Tìm kiếm & Lọc</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.courts.index') }}" class="filter-grid">
            <div class="form-group">
                <label for="search" class="form-label">Tên sân con</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Nhập tên sân..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Trạng thái</label>
                <select id="status" name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>✓ Hoạt động</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>✗ Đã ẩn</option>
                </select>
            </div>
            <div class="form-group">
                <label for="venue_id" class="form-label">Cơ sở sân</label>
                <select id="venue_id" name="venue_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach ($venues as $venue)
                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Lọc</button>
                <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header"><i class="fa-solid fa-table-list" style="color: var(--text-muted);"></i> Danh sách Sân</div>
    
    @if ($courts->count() > 0)
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"><input type="checkbox" id="selectAll" class="custom-checkbox"></th>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Tên Sân</th>
                        <th style="width: 20%;">Cơ sở / Chủ sân</th>
                        <th style="width: 20%;">Khu vực</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 15%; text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courts as $court)
                        <tr>
                            <td><input type="checkbox" class="custom-checkbox court-checkbox" value="{{ $court->id }}"></td>
                            <td><span class="custom-badge badge-gray">#{{ $court->id }}</span></td>
                            <td><strong style="color: var(--text-dark);">{{ $court->name }}</strong></td>
                            <td>
                                <div style="font-weight: 600; color: var(--primary); margin-bottom: 4px;">{{ $court->venue->name ?? 'N/A' }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-user" style="margin-right: 4px;"></i> {{ $court->venue?->owner->name ?? 'N/A' }}</div>
                            </td>
                            <td style="font-size: 13px; color: var(--text-muted);">{{ $court->venue->address ?? 'N/A' }}</td>
                            <td>
                                @if ($court->status === 'active')
                                    <span class="custom-badge badge-success"><i class="fa-solid fa-circle-check"></i> Hoạt động</span>
                                @else
                                    <span class="custom-badge badge-danger"><i class="fa-solid fa-ban"></i> Đã ẩn</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-group" style="justify-content: flex-end;">
                                    <a href="{{ route('admin.courts.show', $court) }}" class="btn-icon info" title="Xem chi tiết"><i class="fa-regular fa-eye"></i></a>
                                    
                                    <form method="POST" action="{{ route('admin.courts.toggle-status', $court) }}" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-icon {{ $court->status === 'active' ? 'warning' : 'success' }}" onclick="return confirm('Bạn chắc chắn muốn thay đổi trạng thái sân này?')" title="{{ $court->status === 'active' ? 'Tạm ẩn' : 'Kích hoạt' }}">
                                            @if ($court->status === 'active') <i class="fa-solid fa-eye-slash"></i> @else <i class="fa-solid fa-check"></i> @endif
                                        </button>
                                    </form>

                                    <button type="button" class="btn-icon primary" onclick="openEditModal({{ $court->id }}, '{{ addslashes($court->name) }}', '{{ $court->status }}')" title="Chỉnh sửa"><i class="fa-solid fa-pen-to-square"></i></button>

                                    <form method="POST" action="{{ route('admin.courts.destroy', $court) }}" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon danger" onclick="return confirm('Bạn chắc chắn muốn xóa sân này? Hành động này không thể hoàn tác!')" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="batch-actions">
            <form method="POST" action="{{ route('admin.courts.batch-update-status') }}" style="display: flex; gap: 10px; width: 100%; align-items: center; margin: 0;">
                @csrf
                <i class="fa-solid fa-check-double" style="color: var(--text-muted);"></i>
                <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Đã chọn:</span>
                <input type="hidden" id="batchCourtIds" name="court_ids" value="">
                <select name="status" id="batchStatus" class="form-select" style="width: auto; padding: 6px 12px; height: 36px; min-width: 150px;">
                    <option value="">-- Trạng thái --</option>
                    <option value="active">Kích hoạt</option>
                    <option value="inactive">Ẩn sân</option>
                </select>
                <button type="submit" class="btn btn-primary" id="batchApplyBtn" disabled style="padding: 6px 16px; height: 36px;">Áp dụng</button>
            </form>
        </div>

        <div class="pagination-container">
            {{ $courts->links() }}
        </div>
    @else
        <div style="padding: 60px 20px; text-align: center;">
            <i class="fa-solid fa-folder-open" style="font-size: 48px; color: #bdc3c7; margin-bottom: 16px;"></i>
            <h5 style="font-size: 18px; color: var(--text-dark); margin-bottom: 8px;">Không có dữ liệu</h5>
            <p style="color: var(--text-muted); font-size: 14px;">Không tìm thấy sân nào khớp với điều kiện lọc của bạn.</p>
        </div>
    @endif
</div>

<div id="editCourtModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <div class="modal-header">
            <h5 class="modal-title">Chỉnh sửa sân</h5>
            <button type="button" class="modal-close" onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editCourtForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="editCourtName" class="form-label">Tên sân</label>
                    <input type="text" id="editCourtName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editCourtStatus" class="form-label">Trạng thái</label>
                    <select id="editCourtStatus" name="status" class="form-select" required>
                        <option value="active">✓ Hoạt động</option>
                        <option value="inactive">✗ Đã ẩn</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Xử lý Modal bằng Javascript thuần
    const modal = document.getElementById('editCourtModal');
    
    function openEditModal(id, name, status) {
        document.getElementById('editCourtForm').action = `/admin/courts/${id}`;
        document.getElementById('editCourtName').value = name;
        document.getElementById('editCourtStatus').value = status;
        modal.classList.add('active');
    }

    function closeEditModal() {
        modal.classList.remove('active');
    }

    // Đóng modal khi click ra ngoài vùng xám
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeEditModal();
    });

    // Xử lý Checkbox Batch Action
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.court-checkbox');
    const batchApplyBtn = document.getElementById('batchApplyBtn');
    const batchCourtIds = document.getElementById('batchCourtIds');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBatchButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            // Nếu có 1 ô bị bỏ chọn, tắt luôn dấu check ở ô Select All
            if (!this.checked) selectAll.checked = false;
            
            // Nếu tất cả ô con đều được check, bật ô Select All lên
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            if (allChecked) selectAll.checked = true;
            
            updateBatchButton();
        });
    });

    function updateBatchButton() {
        const checkedBoxes = document.querySelectorAll('.court-checkbox:checked');
        const courtIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');
        batchCourtIds.value = courtIds;
        batchApplyBtn.disabled = checkedBoxes.length === 0;
    }
</script>
@endsection