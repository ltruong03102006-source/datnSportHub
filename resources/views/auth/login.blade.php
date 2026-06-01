@extends('layouts.auth')

@section('title', 'Đăng nhập | SportHub')

@section('content')
    <div class="form-heading">
        <p>Chào mừng trở lại</p>
        <h1>Đăng nhập tài khoản</h1>
        <span>Nhập email và mật khẩu để tiếp tục sử dụng SportHub.</span>
    </div>

    <div id="auth-alert" class="auth-alert"></div>

    <form id="login-form" class="auth-form">
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
            <div class="field-row">
                <label for="password">Mật khẩu</label>
                <button type="button" data-toggle-password="password" class="text-button">
                    Hiện
                </button>
            </div>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="field-input"
                placeholder="Nhập mật khẩu"
            >
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

        document.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.togglePassword);
                const isPassword = input.type === 'password';

                input.type = isPassword ? 'text' : 'password';
                toggle.textContent = isPassword ? 'Ẩn' : 'Hiện';
            });
        });

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
                return;
            } catch (error) {
                setAlert('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
            } finally {
                button.disabled = false;
                button.textContent = 'Đăng nhập';
            }
        });
    </script>
@endpush
