@extends('admin.layouts.app')

@push('styles')
<style>
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .header-section h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .data-card {
        padding: 0;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }
    .table-custom th {
        text-align: left;
        padding: 16px 24px;
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .table-custom td {
        padding: 16px 24px;
        font-size: 13px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-custom tr:last-child td {
        border-bottom: none;
    }

    .filter-bar {
        display: flex;
        gap: 16px;
        align-items: center;
    }
    .d-flex { display: flex; }
    .gap-3 { gap: 12px; }

    .filter-select {
        padding: 10px 36px 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 13px;
        color: var(--text-dark);
        outline: none;
        appearance: none;
        background: #fff url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%237f8c8d%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E") no-repeat right 16px top 50%;
        background-size: 10px auto;
    }

    .btn-action {
        border: 1px solid var(--border-color);
        background: transparent;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-action:hover {
        background: #f8f9fa;
        color: var(--text-dark);
        border-color: #bdc3c7;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
    }
    .user-details h4 {
        font-size: 13px;
        font-weight: 600;
        margin: 0 0 2px 0;
        color: var(--text-dark);
    }
    .user-details p {
        font-size: 11px;
        color: var(--text-muted);
        margin: 0;
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: #f1f2f6;
        color: var(--text-dark);
        display: inline-block;
    }
    .status-pending { background: #fef9e7; color: #f39c12; }
    .status-approved { background: #eafaf1; color: #2ecc71; }
    .status-rejected { background: #fdedec; color: #e74c3c; }

    /* Modal styling */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: #fff;
        border-radius: 12px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .modal-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: var(--text-muted);
    }
    .modal-body {
        padding: 24px;
    }
    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
    }
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 13px;
        resize: vertical;
        outline: none;
    }
    .form-group textarea:focus {
        border-color: var(--primary);
    }
    .radio-group {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }
    .radio-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        cursor: pointer;
    }
    .alert-success { background: #eafaf1; color: #2ecc71; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
    .alert-error { background: #fdedec; color: #e74c3c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div class="header-section">
    <h2>Yêu cầu rút tiền</h2>
    <div class="filter-bar">
        <form action="{{ route('admin.withdrawals.index') }}" method="GET" class="d-flex gap-3">
            <select class="filter-select" name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
            </select>
            <button type="submit" class="btn-action" style="padding: 10px 16px; background: var(--primary); color: white; border: none; font-weight: 600;">Lọc</button>
        </form>
    </div>
</div>

<div class="card-custom data-card">
    <table class="table-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người Yêu Cầu</th>
                <th>Số Tiền</th>
                <th>Ngân Hàng</th>
                <th>Trạng Thái</th>
                <th>Thời Gian</th>
                <th style="text-align: right;">Hành Động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $w)
            <tr>
                <td style="color: var(--text-muted); font-weight: 500;">#{{ $w->id }}</td>
                <td>
                    <div class="user-info">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($w->user->name ?? 'User') }}&background=random" class="user-avatar" alt="Avatar">
                        <div class="user-details">
                            <h4>{{ $w->user->name ?? 'N/A' }}</h4>
                            <p>{{ $w->user->email ?? '' }}</p>
                            <span style="color: #2ecc71; font-weight: 600; font-size: 11px;">Số dư ví: {{ number_format($w->user->balance ?? 0) }}đ</span>
                        </div>
                    </div>
                </td>
                <td style="font-weight: 600; color: #e74c3c;">{{ number_format($w->amount) }}đ</td>
                <td>
                    <div class="user-details">
                        <h4>{{ $w->bank_name }}</h4>
                        <p>STK: <strong>{{ $w->bank_account_no }}</strong></p>
                        <p>Tên: {{ $w->bank_account_name }}</p>
                    </div>
                </td>
                <td>
                    @if($w->status === 'pending')
                        <span class="badge-status status-pending">Đang chờ</span>
                    @elseif($w->status === 'approved')
                        <span class="badge-status status-approved">Đã duyệt</span>
                    @else
                        <span class="badge-status status-rejected">Từ chối</span>
                    @endif
                    @if($w->admin_note)
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;" title="{{ $w->admin_note }}">Có ghi chú admin</div>
                    @endif
                </td>
                <td style="color: var(--text-muted); font-size: 12px;">{{ $w->created_at->format('H:i d/m/Y') }}</td>
                <td style="text-align: right;">
                    @if($w->status === 'pending')
                    <button onclick="openProcessModal({{ $w->id }}, {{ $w->amount }}, '{{ $w->user->name ?? '' }}')" class="btn-action" style="background: var(--primary); color: white; border: none; font-weight: 600;">Xử lý</button>
                    @else
                    <span style="font-size: 12px; color: var(--text-muted);">Đã xử lý</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">Không có dữ liệu</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $withdrawals->links() }}
</div>

<!-- Modal xử lý -->
<div id="processModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Xử lý rút tiền</h3>
            <button type="button" onclick="closeProcessModal()" class="modal-close">&times;</button>
        </div>
        <form id="processForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 13px; color: var(--text-dark); margin-bottom: 4px;">Thao tác cho yêu cầu của <strong id="modalUser"></strong>:</p>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Số tiền rút: <strong style="color: #e74c3c; font-size: 16px;" id="modalAmount"></strong></p>
                    
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="status" value="approved" checked onchange="toggleProofInput()">
                            <span style="color: #2ecc71; font-weight: 500;">Đã chuyển khoản (Duyệt)</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="status" value="rejected" onchange="toggleProofInput()">
                            <span style="color: #e74c3c; font-weight: 500;">Từ chối (Hoàn tiền)</span>
                        </label>
                    </div>
                </div>
                <div class="form-group" id="proofImageGroup">
    <label for="proof_image" class="form-label">
        <span class="label-icon"></span>
        Ảnh minh chứng <span class="required-star">*</span>
        <span class="label-sub">(Bắt buộc khi duyệt)</span>
    </label>
    
    <div class="upload-wrapper">
        <div class="upload-area" id="uploadArea">
            <input type="file" 
                   id="proof_image" 
                   name="proof_image" 
                   accept="image/*"
                   class="upload-input">
            
            <div class="upload-content">
                <div class="upload-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <path d="M12 16V8M12 8L9 11M12 8L15 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 16.7428C21.2215 15.7349 22 14.2079 22 12.5C22 9.46243 19.5376 7 16.5 7H16.1973C14.8916 5.33437 13.0327 4 10.8571 4C7.14873 4 4 6.98716 4 10.5C4 11.0831 4.08347 11.6471 4.23853 12.1811" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M15 16L16 17L19 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 16V21M12 21L10 19M12 21L14 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="upload-text">
                    <span class="main-text">Kéo thả ảnh vào đây</span>
                    <span class="sub-text">hoặc <strong>Chọn tệp</strong> từ máy tính</span>
                </div>
                <div class="upload-formats">
                    <span>PNG, JPG, JPEG, WEBP</span>
                    <span>•</span>
                    <span>Tối đa 5MB</span>
                </div>
            </div>
        </div>
        
        <!-- Preview ảnh -->
        <div class="preview-container" id="previewContainer" style="display: none;">
            <div class="preview-wrapper">
                <img id="imagePreview" src="#" alt="Preview">
                <button type="button" class="remove-image" id="removeImage">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <span class="file-name" id="fileName">image.jpg</span>
        </div>
    </div>
</div>

<style>
/* CSS cho upload đẹp - Đồng bộ màu sắc */
:root {
    --primary-color: #4F46E5;
    --primary-hover: #4338CA;
    --primary-light: #EEF2FF;
    --border-color: #E5E7EB;
    --text-color: #111827;
    --text-secondary: #6B7280;
    --bg-white: #FFFFFF;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
    --radius-md: 8px;
    --radius-lg: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Form Group */
.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 14px;
    color: var(--text-color);
    margin-bottom: 8px;
}

.label-icon {
    font-size: 16px;
}

.required-star {
    color: #EF4444;
    margin-left: 2px;
}

.label-sub {
    font-weight: 400;
    font-size: 12px;
    color: var(--text-secondary);
    margin-left: 4px;
}

/* Upload Wrapper */
.upload-wrapper {
    position: relative;
}

/* Upload Area */
.upload-area {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-lg);
    padding: 40px 20px;
    background: #FAFBFC;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-area:hover {
    border-color: var(--primary-color);
    background: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.upload-area.dragover {
    border-color: var(--primary-color);
    background: var(--primary-light);
    transform: scale(1.01);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.upload-area.has-file {
    border-color: #10B981;
    background: #F0FDF4;
}

/* Input file ẩn */
.upload-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 10;
}

/* Upload Content */
.upload-content {
    text-align: center;
    pointer-events: none;
}

.upload-icon {
    color: var(--text-secondary);
    margin-bottom: 12px;
    transition: var(--transition);
}

.upload-area:hover .upload-icon {
    color: var(--primary-color);
    transform: translateY(-4px);
}

.upload-text .main-text {
    display: block;
    font-size: 16px;
    font-weight: 500;
    color: var(--text-color);
    margin-bottom: 4px;
}

.upload-text .sub-text {
    font-size: 14px;
    color: var(--text-secondary);
}

.upload-text .sub-text strong {
    color: var(--primary-color);
    font-weight: 600;
    cursor: pointer;
}

.upload-formats {
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-secondary);
}

/* Preview Container */
.preview-container {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--bg-white);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    animation: slideDown 0.3s ease;
}

.preview-wrapper {
    position: relative;
    width: 64px;
    height: 64px;
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--border-color);
}

.preview-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #EF4444;
    color: white;
    border: 2px solid white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.remove-image:hover {
    background: #DC2626;
    transform: scale(1.1);
}

.file-name {
    font-size: 14px;
    color: var(--text-color);
    font-weight: 500;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 640px) {
    .upload-area {
        padding: 24px 16px;
        min-height: 150px;
    }
    
    .upload-icon svg {
        width: 36px;
        height: 36px;
    }
    
    .upload-text .main-text {
        font-size: 14px;
    }
}
</style>
                <div class="form-group">
                    <label for="admin_note">Ghi chú (Tùy chọn)</label>
                    <textarea id="admin_note" name="admin_note" rows="3" placeholder="Lý do từ chối hoặc mã giao dịch ngân hàng..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeProcessModal()" class="btn-action">Hủy</button>
                <button type="submit" class="btn-action" style="background: var(--primary); color: white; border: none; font-weight: 600; padding: 8px 16px;">Xác nhận</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openProcessModal(id, amount, user) {
        document.getElementById('modalTitle').innerText = 'Xử lý rút tiền #' + id;
        document.getElementById('modalAmount').innerText = new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        document.getElementById('modalUser').innerText = user;
        document.getElementById('processForm').action = `/admin/withdrawals/${id}/status`;
        document.getElementById('processModal').style.display = 'flex';
    }

    function closeProcessModal() {
        document.getElementById('processModal').style.display = 'none';
    }

    function toggleProofInput() {
        const isApproved = document.querySelector('input[name="status"][value="approved"]').checked;
        const proofGroup = document.getElementById('proofImageGroup');
        const proofInput = document.getElementById('proof_image');
        
        if (isApproved) {
            proofGroup.style.display = 'block';
            proofInput.required = true;
        } else {
            proofGroup.style.display = 'none';
            proofInput.required = false;
        }
    }

    // Khởi tạo trạng thái ban đầu khi load trang
    document.addEventListener('DOMContentLoaded', function() {
        toggleProofInput();
    });
</script>
@endpush
