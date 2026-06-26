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

    /* Missing global table classes */
    .data-card {
        padding: 24px;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }
    .table-custom th {
        text-align: left;
        padding: 16px 12px;
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .table-custom td {
        padding: 16px 12px;
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

    .search-box {
        position: relative;
        width: 300px;
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
        background-color: #fff;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        color: var(--text-dark);
        transition: border-color 0.2s;
    }
    .search-box input:focus {
        border-color: var(--primary);
    }

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
    .btn-delete { color: #e74c3c; border-color: #fadbd8; }
    .btn-delete:hover { background: #fdedec; border-color: #e74c3c; color: #c0392b; }

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

    .badge-role {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: #f1f2f6;
        color: var(--text-dark);
    }
    .role-admin { background: #fdedec; color: #e74c3c; }
    .role-owner { background: #eafaf1; color: #2ecc71; }
    
    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .status-active .status-dot { background: #2ecc71; }
    .status-locked .status-dot { background: #e74c3c; }
</style>
@endpush

@section('content')

<div class="header-section">
    <h2>Danh sách người dùng</h2>
    <div class="filter-bar">
        <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-3">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Tìm kiếm tên, email..." value="{{ request('search') }}">
            </div>
            <!-- Role filter placeholder (Chưa xử lý query, chỉ làm UI) -->
            <select class="filter-select" name="role">
                <option value="">Tất cả vai trò</option>
                <option value="user">Người dùng (User)</option>
                <option value="owner">Chủ sân (Owner)</option>
                <option value="admin">Quản trị (Admin)</option>
            </select>
            <button type="submit" class="btn-action" style="padding: 10px 16px; background: var(--primary); color: white; border: none; font-weight: 600;">Lọc</button>
        </form>
    </div>
</div>

<div class="card-custom data-card" style="padding: 0;">
    <table class="table-custom">
        <thead>
            <tr>
                <th style="padding-left: 24px;">ID</th>
                <th>Người Dùng</th>
                <th>Số Điện Thoại</th>
                <th>Vai Trò (Role)</th>
                <th>Trạng Thái</th>
                <th>Ngày Tạo</th>
                <th style="padding-right: 24px; text-align: right;">Hành Động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="padding-left: 24px; color: var(--text-muted); font-weight: 500;">#{{ $user->id }}</td>
                <td>
                    <div class="user-info">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" class="user-avatar" alt="Avatar">
                        <div class="user-details">
                            <h4>{{ $user->name }}</h4>
                            <p>{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td style="color: var(--text-muted);">
                    <!-- Fallback nếu User không có cột phone thì để N/A -->
                    {{ $user->phone ?? 'N/A' }}
                </td>
                <td>
                    @php $roleClass = ''; @endphp
                    @if(strtolower($user->role) === 'admin') @php $roleClass = 'role-admin'; @endphp
                    @elseif(strtolower($user->role) === 'owner') @php $roleClass = 'role-owner'; @endphp
                    @endif
                    <span class="badge-role {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                </td>
                <td>
                    @if($user->status === 'active')
                        <div class="status-active" style="font-size: 12px; font-weight: 500; color: var(--text-dark);"><span class="status-dot"></span>Hoạt động</div>
                    @else
                        <div class="status-locked" style="font-size: 12px; font-weight: 500; color: var(--text-muted);"><span class="status-dot"></span>Bị khóa</div>
                    @endif
                </td>
                <td style="font-size: 12px; color: var(--text-muted);">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
                <td style="padding-right: 24px; text-align: right;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button class="btn-action" title="Chỉnh sửa"><i class="fa-regular fa-pen-to-square"></i></button>
                        <button class="btn-action btn-delete" title="Khóa/Xóa"><i class="fa-solid fa-lock"></i></button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">Không tìm thấy người dùng nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($users->hasPages())
    <div style="padding: 24px; border-top: 1px solid var(--border-color);">
        {{ $users->links('vendor.pagination.admin') }}
    </div>
    @endif
</div>

@endsection
