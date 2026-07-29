@extends('admin.layouts.app')

@push('styles')
<style>
    .page-head {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-end;
        margin-bottom: 24px;
    }

    .page-title {
        margin: 0;
        color: var(--text-dark);
        font-size: 24px;
        font-weight: 800;
    }

    .page-subtitle {
        margin: 6px 0 0;
        color: var(--text-muted);
        font-size: 14px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .02);
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .stat-value {
        margin-top: 8px;
        color: var(--text-dark);
        font-size: 22px;
        font-weight: 900;
    }

    .toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .toolbar form {
        display: flex;
        gap: 10px;
        width: 100%;
    }

    .form-control-soft {
        height: 42px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0 14px;
        color: var(--text-dark);
        font-size: 13px;
        outline: none;
        background: #fff;
    }

    .form-control-soft:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(46, 204, 113, .12);
    }

    .search-input {
        min-width: 280px;
        flex: 1;
    }

    .btn-primary-soft,
    .btn-outline-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        border-radius: 10px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .btn-primary-soft {
        background: var(--primary);
        color: #fff;
    }

    .btn-outline-soft {
        background: #fff;
        border-color: var(--border-color);
        color: var(--text-dark);
    }

    .table-wrap {
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .02);
    }

    .data-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        padding: 15px 18px;
        text-align: left;
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
    }

    .data-table td {
        color: var(--text-dark);
        font-size: 13px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .data-table tr:last-child td {
        border-bottom: 0;
    }

    .muted {
        color: var(--text-muted);
        font-size: 12px;
    }

    .money {
        color: #047857;
        font-weight: 900;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 11px;
        font-size: 12px;
        font-weight: 800;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fee2e2; color: #b91c1c; }
    .status-cancelled { background: #f1f5f9; color: #475569; }

    .alert-success,
    .alert-error {
        border-radius: 12px;
        margin-bottom: 18px;
        padding: 13px 16px;
        font-size: 13px;
        font-weight: 700;
    }

    .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    @media (max-width: 980px) {
        .page-head,
        .toolbar form {
            flex-direction: column;
            align-items: stretch;
        }

        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
@php
    $statusLabels = [
        'pending' => ['Chờ duyệt', 'status-pending'],
        'approved' => ['Đã duyệt', 'status-approved'],
        'rejected' => ['Từ chối', 'status-rejected'],
        'cancelled' => ['Đã hủy', 'status-cancelled'],
    ];
@endphp

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div class="page-head">
    <div>
        <h2 class="page-title">Quản lý yêu cầu rút tiền</h2>
        <p class="page-subtitle">Xem, lọc và kiểm tra các yêu cầu rút tiền từ ví chủ sân.</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Đang chờ duyệt</div>
        <div class="stat-value">{{ $pendingCount }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tổng tiền chờ duyệt</div>
        <div class="stat-value money">{{ number_format($totalPendingAmount, 0, ',', '.') }}đ</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Đã duyệt</div>
        <div class="stat-value">{{ $approvedCount }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Đã từ chối</div>
        <div class="stat-value">{{ $rejectedCount }}</div>
    </div>
</div>

<div class="toolbar">
    <form method="GET" action="{{ route('admin.withdrawals.index') }}">
        <input class="form-control-soft search-input"
               type="search"
               name="search"
               value="{{ $search }}"
               placeholder="Tìm theo mã yêu cầu, tên hoặc email chủ sân">

        <select class="form-control-soft" name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="pending" @selected($status === 'pending')>Chờ duyệt</option>
            <option value="approved" @selected($status === 'approved')>Đã duyệt</option>
            <option value="rejected" @selected($status === 'rejected')>Từ chối</option>
            <option value="cancelled" @selected($status === 'cancelled')>Đã hủy</option>
        </select>

        <button class="btn-primary-soft" type="submit">Lọc</button>
        <a class="btn-outline-soft" href="{{ route('admin.withdrawals.index') }}">Xóa lọc</a>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã yêu cầu</th>
                <th>Chủ sân</th>
                <th>Số tiền</th>
                <th>Ngân hàng</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $withdrawal)
                @php
                    $statusValue = $withdrawal->status instanceof \BackedEnum ? $withdrawal->status->value : $withdrawal->status;
                    [$statusText, $statusClass] = $statusLabels[$statusValue] ?? [$statusValue, 'status-cancelled'];
                @endphp
                <tr>
                    <td>
                        <strong>{{ $withdrawal->code }}</strong>
                        <div class="muted">#{{ $withdrawal->id }}</div>
                    </td>
                    <td>
                        <strong>{{ $withdrawal->owner?->name ?? 'Không rõ' }}</strong>
                        <div class="muted">{{ $withdrawal->owner?->email }}</div>
                    </td>
                    <td class="money">{{ number_format($withdrawal->amount, 0, ',', '.') }}đ</td>
                    <td>
                        <strong>{{ $withdrawal->bank_name }}</strong>
                        <div class="muted">{{ $withdrawal->bank_account_number ?? $withdrawal->bank_account_no }}</div>
                    </td>
                    <td>
                        <span class="badge-status {{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                    <td>
                        <strong>{{ $withdrawal->created_at?->format('d/m/Y') }}</strong>
                        <div class="muted">{{ $withdrawal->created_at?->format('H:i') }}</div>
                    </td>
                    <td>
                        <a class="btn-outline-soft" href="{{ route('admin.withdrawals.show', $withdrawal) }}">
                            Xem chi tiết
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding: 42px; text-align: center;">
                        <strong>Chưa có yêu cầu rút tiền nào.</strong>
                        <div class="muted" style="margin-top: 6px;">Các yêu cầu từ chủ sân sẽ hiển thị tại đây.</div>
                    </td>
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
