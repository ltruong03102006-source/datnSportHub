@extends('admin.layouts.app')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-header h2 {
        font-size: 22px;
        font-weight: 700;
        margin: 0;
    }

    .card-custom {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 20px rgba(0,0,0,0.04);
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    .table-custom th,
    .table-custom td {
        padding: 16px 12px;
        border-bottom: 1px solid var(--border-color);
        text-align: left;
        vertical-align: middle;
    }

    .table-custom th {
        font-size: 12px;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.02em;
    }

    .table-custom td {
        font-size: 13px;
        color: var(--text-dark);
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-status.pending { background: #fff7e6; color: #b58100; }
    .badge-status.waiting { background: #fff7e6; color: #b58100; }
    .badge-status.active,
    .badge-status.approved { background: #ebfff1; color: #219653; }
    .badge-status.rejected { background: #fdecea; color: #d32f2f; }

    .btn-action {
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        font-weight: 600;
    }

    .btn-approve { background: #2ecc71; color: white; }
    .btn-reject { background: #e74c3c; color: white; }

    .status-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-form {
        display: grid;
        gap: 12px;
        grid-template-columns: 1fr auto;
        align-items: center;
    }

    .filter-form select,
    .filter-form button {
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: #fff;
        font-size: 13px;
    }

    .filter-form button {
        background: var(--primary);
        color: white;
        border-color: transparent;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2>Quản lý đăng ký chủ sân</h2>
    <form action="{{ route('admin.owner-registrations.index') }}" method="GET" class="filter-form">
        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đã duyệt</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
        </select>
        <button type="submit">Lọc</button>
    </form>
</div>

<div class="card-custom">
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 16px; border-radius: 12px; background: #ebfff1; color: #0f5132;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 20px; padding: 16px; border-radius: 12px; background: #f8d7da; color: #842029;">
            {{ session('error') }}
        </div>
    @endif
    @if(session('owner_password_setup_url'))
        <div style="margin-bottom: 20px; padding: 16px; border-radius: 12px; background: #e8f1ff; color: #1e40af;">
            <strong>Liên kết đặt mật khẩu (có hiệu lực 24 giờ):</strong><br>
            <a href="{{ session('owner_password_setup_url') }}" target="_blank">{{ session('owner_password_setup_url') }}</a>
        </div>
    @endif

    <table class="table-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th style="text-align: right;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $registration)
                <tr>
                    <td>#{{ $registration->id }}</td>
                    <td>{{ $registration->name }}</td>
                    <td>{{ $registration->email }}</td>
                    <td>{{ $registration->phone }}</td>
                    <td>
                        <div class="status-row">
                            <span class="badge-status {{ $registration->status }}">{{ ucfirst($registration->status) }}</span>
                        </div>
                        <div style="font-size: 12px; color: #8a8f98; margin-top: 6px;">Debug: {{ $registration->status }}</div>
                    </td>
                    <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align: right; min-width: 240px;">
                        @php
                            $status = strtolower($registration->status);
                        @endphp

                        @if(in_array($status, ['pending', 'waiting'], true))
                            <form action="{{ route('admin.owner-registrations.approve', ['id' => $registration->id]) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="btn-action btn-approve" onclick="return confirm('Bạn có chắc muốn duyệt tài khoản chủ sân này?');">Duyệt</button>
                            </form>
                            <button class="btn-action btn-reject" onclick="openRejectModal({{ $registration->id }})">Từ chối</button>
                        @elseif(in_array($status, ['active', 'approved'], true))
                            <span class="badge-status active">Đã duyệt</span>
                        @elseif($status === 'rejected')
                            <span class="badge-status rejected">Đã từ chối</span>
                        @else
                            <span style="color: var(--text-muted);">Không có hành động</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">Không có yêu cầu nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $registrations->links('pagination::bootstrap-5') }}
    </div>
</div>

<div id="reject-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, .5); align-items: center; justify-content: center; padding: 24px;">
    <div style="background: white; border-radius: 20px; max-width: 520px; width: 100%; padding: 28px; position: relative;">
        <button type="button" onclick="closeRejectModal()" style="position: absolute; right: 18px; top: 18px; border: none; background: transparent; font-size: 18px; cursor: pointer;">×</button>
        <h3 style="margin-top: 0; margin-bottom: 16px;">Từ chối đăng ký</h3>
        <form id="reject-form" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label for="reason" style="display: block; margin-bottom: 8px; font-weight: 600;">Lý do từ chối</label>
                <textarea id="reason" name="reason" rows="4" required style="width: 100%; border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; font-size: 13px;"></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()" class="btn-action" style="background: #f0f0f0; color: var(--text-dark);">Hủy</button>
                <button type="submit" class="btn-action btn-reject">Gửi từ chối</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal(id) {
        const modal = document.getElementById('reject-modal');
        const form = document.getElementById('reject-form');
        form.action = `/admin/owner-registrations/${id}/reject`;
        modal.style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('reject-modal').style.display = 'none';
    }
</script>
@endpush
@endsection
