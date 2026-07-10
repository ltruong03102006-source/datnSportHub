@extends('layouts.auth')

@section('title', 'Đăng nhập | SportHub')

@section('content')
    <div class="form-heading">
        <p>Chào mừng trở lại</p>
        <h1>Đăng nhập tài khoản</h1>
        <span>Nhập email và mật khẩu để tiếp tục sử dụng SportHub.</span>
    </div>

    <div id="auth-alert" class="auth-alert"></div>

    @if (session('error'))
        <div style="margin-bottom: 14px; padding: 11px 14px; border: 1px solid #fecaca; border-radius: 10px; background: #fef2f2; color: #b91c1c; font-size: 14px;">
            {{ session('error') }}
        </div>
    @endif

    <form id="login-form" class="auth-form" novalidate>
        <div>
            <label for="email" class="field-label">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                class="field-input"
                placeholder="you@example.com"
            >
            <p data-error-for="email" class="field-error"></p>
        </div>

        <div>
            <label for="password" class="field-label">Mật khẩu</label>
            <div style="position: relative;">
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="field-input"
                    placeholder="Nhập mật khẩu"
                    style="padding-right: 2.5rem;"
                >
                <button type="button" id="toggle-password-btn" tabindex="-1" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b7280; display: flex; align-items: center; justify-content: center; padding: 0.25rem;">
                    <svg id="icon-eye-closed" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    <svg id="icon-eye-open" style="width: 1.25rem; height: 1.25rem; display: none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
            <p data-error-for="password" class="field-error"></p>
        </div>

        <button id="submit-button" type="submit" class="submit-button">
            Đăng nhập
        </button>
    </form>

    <div style="display:flex; align-items:center; gap:12px; margin:18px 0;">
        <span style="flex:1; height:1px; background:#e5e7eb;"></span>
        <span style="color:#9ca3af; font-size:13px;">hoặc</span>
        <span style="flex:1; height:1px; background:#e5e7eb;"></span>
    </div>

    <div style="display:flex; flex-direction:column; gap:10px;">
        <a href="{{ route('social.redirect', 'google') }}"
           style="display:flex; align-items:center; justify-content:center; gap:10px; padding:11px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; color:#374151; font-weight:600; font-size:14px; text-decoration:none;">
            <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
            Tiếp tục với Google
        </a>
        <a href="{{ route('social.redirect', 'facebook') }}"
           style="display:flex; align-items:center; justify-content:center; gap:10px; padding:11px; border:1px solid #1877f2; border-radius:10px; background:#1877f2; color:#fff; font-weight:600; font-size:14px; text-decoration:none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073c0 6.018 4.388 11.008 10.125 11.927v-8.437H7.078v-3.49h3.047V9.41c0-3.017 1.792-4.684 4.533-4.684 1.312 0 2.686.235 2.686.235v2.965h-1.513c-1.49 0-1.955.928-1.955 1.879v2.256h3.328l-.532 3.49h-2.796v8.437C19.612 23.081 24 18.091 24 12.073z"/></svg>
            Tiếp tục với Facebook
        </a>
    </div>

    <div style="margin-top: 16px; padding: 12px 14px; border: 1px solid #dbeafe; border-radius: 10px; background: #eff6ff; color: #1d4ed8; font-size: 14px; line-height: 1.5;">
        Bạn là chủ sân?
        <a href="{{ route('owner.login.page') }}" style="font-weight: 700; color: #1d4ed8; text-decoration: underline;">Đăng nhập cho chủ sân</a>
    </div>

    <p class="auth-switch">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay</a>
    </p>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('#login-form');
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
            alertBox.textContent = message;
            alertBox.className = type === 'success' ? 'auth-alert is-success' : 'auth-alert is-error';
        }

        function clearErrors() {
            document.querySelectorAll('[data-error-for]').forEach((node) => {
                node.textContent = '';
                node.classList.remove('is-visible');
            });
            alertBox.className = 'auth-alert';
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

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.type === 'password';
                pwdInput.type = isPassword ? 'text' : 'password';
                
                if (isPassword) {
                    iconClosed.style.display = 'none';
                    iconOpen.style.display = 'block';
                } else {
                    iconOpen.style.display = 'none';
                    iconClosed.style.display = 'block';
                }
            });
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors();
            button.disabled = true;
            button.textContent = 'Đang đăng nhập...';

            try {
                const response = await fetch('{{ route('web.login') }}', {
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
                    setAlert(data.message || 'Đăng nhập không thành công.');
                    return;
                }

                localStorage.setItem('sporthub_token', data.token);
                localStorage.setItem('sporthub_user', JSON.stringify(data.user));
                setAlert('Đăng nhập thành công. Đang quay lại trang đặt sân…', 'success');
                form.reset();
                window.location.href = postAuthDestination();
            } catch (error) {
                setAlert('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
            } finally {
                button.disabled = false;
                button.textContent = 'Đăng nhập';
            }
        });
    </script>
@endpush
