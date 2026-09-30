@extends('layouts.auth')

@section('title', 'Đăng ký | SportHub')

@section('content')
    <div class="form-heading">
        <span class="role-badge-tag customer">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
            Tạo tài khoản Người chơi
        </span>
        <h1>Đăng ký SportHub</h1>
        <span>Hoàn tất thông tin bên dưới để bắt đầu tìm sân và đặt lịch chơi ngay.</span>
    </div>

    <div id="auth-alert" class="auth-alert"></div>

    <form id="register-form" class="auth-form" novalidate>
        <div class="field-group">
            <label for="name" class="field-label">Họ và tên</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input
                    id="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    required
                    class="field-input"
                    placeholder="Nguyễn Văn A"
                >
            </div>
            <p data-error-for="name" class="field-error"></p>
        </div>

        <div class="field-group">
            <label for="email" class="field-label">Địa chỉ Email</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    class="field-input"
                    placeholder="you@example.com"
                >
            </div>
            <p data-error-for="email" class="field-error"></p>
        </div>

        <div class="field-group">
            <label for="password" class="field-label">Mật khẩu</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="field-input"
                    placeholder="Tối thiểu 8 ký tự"
                    style="padding-right: 2.75rem;"
                >
                <button type="button" class="toggle-password-btn" id="toggle-password-btn" tabindex="-1">
                    <svg id="icon-eye-closed" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    <svg id="icon-eye-open" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
            <p data-error-for="password" class="field-error"></p>
        </div>

        <div class="field-group">
            <label for="password_confirmation" class="field-label">Xác nhận mật khẩu</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="field-input"
                    placeholder="Nhập lại mật khẩu"
                >
            </div>
        </div>

        <button id="submit-button" type="submit" class="submit-btn-customer" style="margin-top: 4px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Đăng ký Khách hàng
        </button>
    </form>

    <div class="divider">
        <span class="divider-line"></span>
        <span class="divider-text">Hoặc đăng ký nhanh với</span>
        <span class="divider-line"></span>
    </div>

    <div class="social-buttons">
        <a href="{{ route('social.redirect', 'google') }}" class="social-btn">
            <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
            Google
        </a>
        <a href="{{ route('social.redirect', 'facebook') }}" class="social-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877f2"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073c0 6.018 4.388 11.008 10.125 11.927v-8.437H7.078v-3.49h3.047V9.41c0-3.017 1.792-4.684 4.533-4.684 1.312 0 2.686.235 2.686.235v2.965h-1.513c-1.49 0-1.955.928-1.955 1.879v2.256h3.328l-.532 3.49h-2.796v8.437C19.612 23.081 24 18.091 24 12.073z"/></svg>
            Facebook
        </a>
    </div>

    <!-- Owner Registration Option Box -->
    <div class="owner-reg-card">
        <span class="reg-badge">✨ Dành cho Đối Tác</span>
        <h3>Bạn muốn đăng ký mở tài khoản Chủ sân?</h3>
        <p>Đăng ký đối tác để đưa cụm sân của bạn lên hệ thống SportHub và tiếp cận đông đảo người chơi.</p>
        <a href="{{ route('owner.register.page') }}" class="owner-reg-btn">
            Form đăng ký Chủ sân
        </a>
    </div>

    <p class="auth-switch">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập ngay</a>
    </p>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('#register-form');
        const button = document.querySelector('#submit-button');
        const alertBox = document.querySelector('#auth-alert');
        const bookingContinuationKey = 'sporthub_pending_booking';

        function postAuthDestination() {
            try {
                const continuation = JSON.parse(sessionStorage.getItem(bookingContinuationKey) || 'null');
                const destination = new URL(continuation?.returnUrl || window.location.origin, window.location.origin);

                if (destination.origin === window.location.origin && destination.pathname.startsWith('/courts/')) {
                    return destination.toString();
                }
            } catch (error) {
                sessionStorage.removeItem(bookingContinuationKey);
            }

            return '{{ route('home') }}';
        }

        function setAlert(message, type = 'error') {
            alertBox.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/></svg>
                <span>${message}</span>
            `;
            alertBox.className = type === 'success' ? 'auth-alert is-success' : 'auth-alert is-error';
            alertBox.style.display = 'flex';
        }

        function clearErrors() {
            document.querySelectorAll('[data-error-for]').forEach((node) => {
                node.textContent = '';
                node.classList.remove('is-visible');
            });
            alertBox.style.display = 'none';
        }

        function showErrors(errors = {}) {
            Object.entries(errors).forEach(([field, messages]) => {
                const node = document.querySelector(`[data-error-for="${field}"]`);
                if (!node) return;

                node.textContent = Array.isArray(messages) ? messages[0] : messages;
                node.classList.add('is-visible');
            });
        }

        // Logic ẩn/hiện mật khẩu
        const toggleBtn = document.getElementById('toggle-password-btn');
        const iconOpen = document.getElementById('icon-eye-open');
        const iconClosed = document.getElementById('icon-eye-closed');
        const pwdInput = document.getElementById('password');
        const pwdConfirm = document.getElementById('password_confirmation');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.type === 'password';
                
                pwdInput.type = isPassword ? 'text' : 'password';
                pwdConfirm.type = isPassword ? 'text' : 'password';
                
                if (isPassword) {
                    iconClosed.style.display = 'none';
                    iconOpen.style.display = 'block';
                } else {
                    iconOpen.style.display = 'none';
                    iconClosed.style.display = 'block';
                }
            });
        }

        // Form Submit Handler
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors();
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Đang đăng ký...
            `;

            try {
                const response = await fetch('{{ route('web.register') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(Object.fromEntries(new FormData(form))),
                });

                const data = await response.json();

                if (!response.ok) {
                    showErrors(data.errors);
                    setAlert(data.message || 'Đăng ký không thành công.');
                    return;
                }

                localStorage.setItem('sporthub_token', data.token);
                localStorage.setItem('sporthub_user', JSON.stringify(data.user));
                setAlert('Đăng ký thành công. Đang chuyển hướng...', 'success');
                form.reset();
                window.location.href = postAuthDestination();
            } catch (error) {
                setAlert('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
            } finally {
                button.disabled = false;
                button.innerHTML = `
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Đăng ký Khách hàng
                `;
            }
        });
    </script>
@endpush
