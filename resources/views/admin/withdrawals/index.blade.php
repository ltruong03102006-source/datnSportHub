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
    .alert-success { background: #eafaf1; color: #2ecc71; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
    .alert-error { background: #fdedec; color: #e74c3c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }

    /* Custom File Upload */
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 32px 24px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--primary, #10b981);
        background: #f0fdf4;
    }
    .upload-icon {
        font-size: 32px;
        color: #64748b;
        margin-bottom: 12px;
    }
    .upload-text {
        font-size: 14px;
        color: #334155;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .upload-hint {
        font-size: 12px;
        color: #94a3b8;
    }
    .upload-zone input[type="file"] {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
    }
    
    .image-preview-container {
        display: none;
        margin-top: 12px;
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .image-preview-container img {
        width: 100%;
        max-height: 200px;
        object-fit: contain;
        display: block;
        background: #f1f5f9;
    }
    .remove-image-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(0,0,0,0.6);
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }
    .remove-image-btn:hover {
        background: rgba(220, 38, 38, 0.9);
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
                            <input type="radio" name="status" value="approved" checked onchange="toggleProofUpload()">
                            <span style="color: #2ecc71; font-weight: 500;">Đã chuyển khoản (Duyệt)</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="status" value="rejected" onchange="toggleProofUpload()">
                            <span style="color: #e74c3c; font-weight: 500;">Từ chối (Hoàn tiền)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="proofUploadGroup">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-camera" style="color: #475569;"></i> 
                        Ảnh minh chứng <span style="color: #e74c3c;">*</span> 
                        <span style="color: #94a3b8; font-weight: normal;">(Bắt buộc khi duyệt)</span>
                    </label>
                    
                    <div class="upload-zone" id="uploadZone">
                        <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                        <div class="upload-text">Kéo thả ảnh vào đây <br>hoặc <span style="color: var(--primary);">Chọn tệp</span> từ máy tính</div>
                        <div class="upload-hint">PNG, JPG, JPEG, WEBP • Tối đa 5MB</div>
                        <input type="file" id="proof_image" name="proof_image" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="handleFileSelect(this)">
                    </div>

                    <div class="image-preview-container" id="imagePreviewContainer">
                        <img id="imagePreview" src="" alt="Preview">
                        <button type="button" class="remove-image-btn" onclick="removeImage()" title="Xóa ảnh">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>
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
        // Reset form khi đóng
        document.getElementById('processForm').reset();
        removeImage();
    }

    function toggleProofUpload() {
        const isApproved = document.querySelector('input[name="status"][value="approved"]').checked;
        const proofGroup = document.getElementById('proofUploadGroup');
        const proofInput = document.getElementById('proof_image');
        
        if (isApproved) {
            proofGroup.style.display = 'block';
            proofInput.required = true;
        } else {
            proofGroup.style.display = 'none';
            proofInput.required = false;
        }
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').style.display = 'block';
                document.getElementById('uploadZone').style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage() {
        document.getElementById('proof_image').value = '';
        document.getElementById('imagePreview').src = '';
        document.getElementById('imagePreviewContainer').style.display = 'none';
        document.getElementById('uploadZone').style.display = 'block';
    }

    // Xử lý kéo thả file
    const uploadZone = document.getElementById('uploadZone');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => {
            uploadZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => {
            uploadZone.classList.remove('dragover');
        }, false);
    });

    uploadZone.addEventListener('drop', (e) => {
        let dt = e.dataTransfer;
        let files = dt.files;
        
        if (files.length) {
            document.getElementById('proof_image').files = files;
            handleFileSelect(document.getElementById('proof_image'));
        }
    }, false);

    document.addEventListener('DOMContentLoaded', function() {
        toggleProofUpload();
    });
</script>
@endpush
