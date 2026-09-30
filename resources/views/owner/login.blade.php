@extends('layouts.auth')

@section('title', 'Đăng nhập Chủ sân | SportHub')

@section('content')
    <!-- Role Switcher -->
    <div class="role-switcher-container">
        <span class="role-switcher-label">Phân loại tài khoản:</span>
        <div class="role-switcher" role="tablist">
            <a 
                href="{{ route('login') }}" 
                class="role-tab-btn" 
                role="tab"
            >
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Khách Hàng
            </a>
            
            <a 
                href="{{ route('owner.login.page') }}" 
                class="role-tab-btn active-owner" 
                role="tab"
            >
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4"/>
                </svg>
                Chủ Sân / Đối Tác
            </a>
        </div>
    </div>

    <div class="form-heading">
        <span class="role-badge-tag owner">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
            Quản lý Cụm Sân & Cho Thuê
        </span>
        <h1>Đăng nhập Chủ sân</h1>
        <span>Đăng nhập dành riêng cho đối tác quản lý điểm sân thể thao trên SportHub.</span>
    </div>

    @if (session('owner_login_error'))
        <div class="auth-alert is-error" style="display: flex;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('owner_login_error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('owner.login.store') }}" class="auth-form" novalidate>
        @csrf

        <div class="field-group">
            <label for="email" class="field-label">Email Chủ sân</label>
            <div class="input-wrapper owner-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    class="field-input owner-input"
                    value="{{ old('email') }}"
                    placeholder="owner@domain.com"
                >
            </div>
            @error('email')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <label for="password" class="field-label">Mật khẩu</label>
            <div class="input-wrapper owner-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="field-input owner-input"
                    placeholder="Nhập mật khẩu chủ sân"
                    style="padding-right: 2.75rem;"
                >
                <button type="button" class="toggle-password-btn" onclick="togglePassword('password', this)" tabindex="-1">
                    <svg class="eye-closed-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    <svg class="eye-open-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="submit-btn-owner">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Đăng nhập Chủ sân
        </button>
    </form>

    <!-- Prominent Owner Registration Callout Box -->
    <div class="owner-reg-card">
        <span class="reg-badge">✨ Đăng ký Đối Tác Mới</span>
        <h3>Bạn muốn hợp tác cho thuê sân bóng, cầu lông, tennis?</h3>
        <p>Trở thành đối tác của SportHub để tiếp cận hàng nghìn khách hàng đặt sân mỗi ngày, tối ưu lịch và tăng lợi nhuận kinh doanh!</p>
        <a href="{{ route('owner.register.page') }}" class="owner-reg-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Đăng ký tài khoản Chủ sân ngay
        </a>
    </div>

    <p class="auth-switch">
        Bạn là người chơi đặt sân?
        <a href="{{ route('login') }}" class="owner-link">Đăng nhập tài khoản Khách</a>
    </p>
@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeClosed = btn.querySelector('.eye-closed-icon');
            const eyeOpen = btn.querySelector('.eye-open-icon');

            if (input.type === 'password') {
                input.type = 'text';
                eyeClosed.style.display = 'none';
                eyeOpen.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            }
        }
    </script>
@endpush
