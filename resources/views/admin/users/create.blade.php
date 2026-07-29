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
    .form-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 32px;
        max-width: 800px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-dark);
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    .form-select {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-dark);
        background: #fff;
    }
    .btn-submit {
        padding: 12px 24px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
    }
    .btn-submit:hover {
        opacity: 0.9;
    }
    .text-danger {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }
    .text-danger-inline {
        color: #e74c3c;
    }
</style>
@endpush

@section('content')

<div class="header-section">
    <h2>Thêm người dùng mới</h2>
    <a href="{{ route('admin.users.index') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="form-card">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Họ và tên <span class="text-danger-inline">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nhập họ và tên..." required>
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Địa chỉ Email <span class="text-danger-inline">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Nhập email đăng nhập..." required>
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Mật khẩu <span class="text-danger-inline">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu (ít nhất 8 ký tự)..." required>
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Nhập số điện thoại...">
            @error('phone')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Phân quyền (Vai trò) <span class="text-danger-inline">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Người dùng (User)</option>
                    <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Chủ sân (Owner)</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                </select>
                @error('role')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái <span class="text-danger-inline">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Khóa tài khoản</option>
                </select>
                @error('status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group" style="margin-top: 32px; margin-bottom: 0;">
            <button type="submit" class="btn-submit">Tạo người dùng</button>
        </div>
    </form>
</div>

@endsection
