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
    .btn-back {
        padding: 8px 16px;
        background: #f8f9fa;
        color: var(--text-dark);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #e9ecef;
    }
    .profile-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 32px;
        display: flex;
        gap: 40px;
    }
    .profile-avatar-section {
        text-align: center;
        width: 200px;
    }
    .profile-avatar-section img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f1f2f6;
        margin-bottom: 16px;
    }
    .profile-details {
        flex: 1;
    }
    .info-group {
        margin-bottom: 24px;
    }
    .info-label {
        font-size: 13px;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }
    .info-value {
        font-size: 15px;
        color: var(--text-dark);
        font-weight: 500;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .badge-role {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        background: #f1f2f6;
        color: var(--text-dark);
        display: inline-block;
    }
    .role-admin { background: #fdedec; color: #e74c3c; }
    .role-owner { background: #eafaf1; color: #2ecc71; }
    .status-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
    }
    .status-active-badge { background: #eafaf1; color: #2ecc71; }
    .status-locked-badge { background: #fdedec; color: #e74c3c; }
</style>
@endpush

@section('content')

<div class="header-section">
    <h2>Chi tiết người dùng: {{ $user->name }}</h2>
    <a href="{{ route('admin.users.index') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="profile-card">
    <div class="profile-avatar-section">
        <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random&size=150' }}" alt="Avatar">
        
        @php $roleClass = ''; @endphp
        @if(strtolower($user->role) === 'admin') @php $roleClass = 'role-admin'; @endphp
        @elseif(strtolower($user->role) === 'owner') @php $roleClass = 'role-owner'; @endphp
        @endif
        <div class="badge-role {{ $roleClass }} mt-2 mb-3">Vai trò: {{ ucfirst($user->role) }}</div>
        
        @if($user->status === 'active')
            <div class="status-badge status-active-badge">Trạng thái: Hoạt động</div>
        @else
            <div class="status-badge status-locked-badge">Trạng thái: Bị khóa</div>
        @endif
    </div>

    <div class="profile-details">
        <div class="grid-2">
            <div class="info-group">
                <span class="info-label">ID Người dùng</span>
                <div class="info-value">#{{ $user->id }}</div>
            </div>
            
            <div class="info-group">
                <span class="info-label">Họ và tên</span>
                <div class="info-value">{{ $user->name }}</div>
            </div>

            <div class="info-group">
                <span class="info-label">Email</span>
                <div class="info-value">{{ $user->email }}</div>
            </div>

            <div class="info-group">
                <span class="info-label">Số điện thoại</span>
                <div class="info-value">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
            </div>

            <div class="info-group">
                <span class="info-label">Địa chỉ</span>
                <div class="info-value">{{ $user->address ?? 'Chưa cập nhật' }}</div>
            </div>
            
            <div class="info-group">
                <span class="info-label">Xác thực Email</span>
                <div class="info-value">
                    @if($user->email_verified_at)
                        <span style="color: #2ecc71;"><i class="fa-solid fa-check-circle"></i> Đã xác thực ({{ $user->email_verified_at->format('d/m/Y H:i') }})</span>
                    @else
                        <span style="color: #e74c3c;"><i class="fa-solid fa-times-circle"></i> Chưa xác thực</span>
                    @endif
                </div>
            </div>

            <div class="info-group">
                <span class="info-label">Ngày đăng ký</span>
                <div class="info-value">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A' }}</div>
            </div>
            
            <div class="info-group">
                <span class="info-label">Cập nhật lần cuối</span>
                <div class="info-value">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
