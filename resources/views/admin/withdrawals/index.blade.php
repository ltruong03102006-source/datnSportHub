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
                <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                    @if($w->status === 'pending')
                    <button onclick="openProcessModal({{ $w->id }}, {{ $w->amount }}, '{{ $w->user->name ?? '' }}')" class="btn-action" style="background: var(--primary); color: white; border: none; font-weight: 600;">Xử lý</button>
                    @else
                    <button onclick="openDetailModal({
                        id: {{ $w->id }},
                        amount: '{{ number_format($w->amount) }}đ',
                        user: '{{ $w->user->name ?? '' }}',
                        bankName: '{{ $w->bank_name }}',
                        bankAccount: '{{ $w->bank_account_no }}',
                        accountName: '{{ $w->bank_account_name }}',
                        status: '{{ $w->status }}',
                        note: '{{ e(str_replace(array("\r", "\n"), '', $w->admin_note)) }}',
                        proof: '{{ $w->proof_image ? asset('storage/' . $w->proof_image) : '' }}'
                    })" class="btn-action">Chi tiết</button>
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
        <form id="processForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 13px; color: var(--text-dark); margin-bottom: 4px;">Thao tác cho yêu cầu của <strong id="modalUser"></strong>:</p>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Số tiền rút: <strong style="color: #e74c3c; font-size: 16px;" id="modalAmount"></strong></p>
                    
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="status" value="approved" checked>
                            <span style="color: #2ecc71; font-weight: 500;">Đã chuyển khoản (Duyệt)</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="status" value="rejected">
                            <span style="color: #e74c3c; font-weight: 500;">Từ chối (Hoàn tiền)</span>
                        </label>
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

<!-- Modal xem chi tiết -->
<div id="detailModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="detailTitle">Chi tiết rút tiền</h3>
            <button type="button" onclick="closeDetailModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body" style="font-size: 13px; line-height: 1.6;">
            <div style="margin-bottom: 12px;"><strong>Người yêu cầu:</strong> <span id="detailUser"></span></div>
            <div style="margin-bottom: 12px;"><strong>Số tiền:</strong> <span id="detailAmount" style="color: #e74c3c; font-weight: bold;"></span></div>
            <div style="margin-bottom: 12px;"><strong>Ngân hàng:</strong> <span id="detailBank"></span></div>
            <div style="margin-bottom: 12px;"><strong>Trạng thái:</strong> <span id="detailStatus"></span></div>
            <div style="margin-bottom: 12px;"><strong>Ghi chú Admin:</strong> <span id="detailNote" style="color: #64748b;"></span></div>
            
            <div id="detailProofContainer" style="display: none; margin-top: 16px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <strong style="display: block; margin-bottom: 8px;">Ảnh minh chứng:</strong>
                <img id="detailProofImage" src="" alt="Minh chứng" style="max-width: 100%; border-radius: 8px; border: 1px solid #e2e8f0; max-height: 250px; object-fit: contain;">
                <a id="detailProofDownload" href="" download class="btn-action" style="display: inline-block; margin-top: 8px; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-download"></i> Tải ảnh về</a>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDetailModal()" class="btn-action">Đóng</button>
        </div>
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

    function openDetailModal(data) {
        document.getElementById('detailTitle').innerText = 'Chi tiết rút tiền #' + data.id;
        document.getElementById('detailUser').innerText = data.user;
        document.getElementById('detailAmount').innerText = data.amount;
        document.getElementById('detailBank').innerText = `${data.bankName} - ${data.bankAccount} (${data.accountName})`;
        document.getElementById('detailNote').innerText = data.note || 'Không có ghi chú';
        
        let statusHtml = '';
        if (data.status === 'approved') statusHtml = '<span class="badge-status status-approved">Đã duyệt</span>';
        else if (data.status === 'rejected') statusHtml = '<span class="badge-status status-rejected">Từ chối</span>';
        document.getElementById('detailStatus').innerHTML = statusHtml;

        const proofContainer = document.getElementById('detailProofContainer');
        if (data.proof) {
            document.getElementById('detailProofImage').src = data.proof;
            document.getElementById('detailProofDownload').href = data.proof;
            proofContainer.style.display = 'block';
        } else {
            proofContainer.style.display = 'none';
        }

        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
</script>
@endpush
