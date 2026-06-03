@extends('layouts.auth')

@section('title', 'Đăng nhập | SportHub')

@section('content')
    <div class="form-heading">
        <p>Chào mừng trở lại</p>
        <h1>Đăng nhập tài khoản</h1>
        <span>Nhập email và mật khẩu để tiếp tục sử dụng SportHub.</span>
    </div>

    <div id="auth-alert" class="auth-alert"></div>

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
                const response = await fetch('{{ url('/api/login') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
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
                setAlert('Đăng nhập thành công. Đang chuyển đến trang tìm sân…', 'success');
                form.reset();
                window.location.href = '{{ route('home') }}';
            } catch (error) {
                setAlert('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
            } finally {
                button.disabled = false;
                button.textContent = 'Đăng nhập';
            }
        });
    </script>
@endpush