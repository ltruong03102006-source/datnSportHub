@extends('layouts.auth')

@section('title', 'Đăng ký | SportHub')

@section('content')
    <div class="form-heading">
        <p>Tạo tài khoản mới</p>
        <h1>Đăng ký SportHub</h1>
        <span>Hoàn tất thông tin bên dưới để bắt đầu đặt sân và quản lý lịch chơi.</span>
    </div>

    <div id="auth-alert" class="auth-alert"></div>

    <form id="register-form" class="auth-form">
        <div>
            <label for="name" class="field-label">Họ và tên</label>
            <input
                id="name"
                name="name"
                type="text"
                autocomplete="name"
                required
                class="field-input"
                placeholder="Nguyễn Văn A"
            >
            <p data-error-for="name" class="field-error"></p>
        </div>

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
                autocomplete="new-password"
                required
                minlength="8"
                class="field-input"
                placeholder="Tối thiểu 8 ký tự"
            >
            <p data-error-for="password" class="field-error"></p>
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Xác nhận mật khẩu</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                minlength="8"
                class="field-input"
                placeholder="Nhập lại mật khẩu"
            >
        </div>

        <button id="submit-button" type="submit" class="submit-button">
            Đăng ký
        </button>
    </form>

    <p class="auth-switch">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập</a>
    </p>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('#register-form');
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
                const confirmation = document.getElementById('password_confirmation');
                const isPassword = input.type === 'password';

                input.type = isPassword ? 'text' : 'password';
                confirmation.type = isPassword ? 'text' : 'password';
                toggle.textContent = isPassword ? 'Ẩn' : 'Hiện';
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors();
            button.disabled = true;
            button.textContent = 'Đang đăng ký...';

            try {
                const response = await fetch('{{ url('/api/register') }}', {
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
                    setAlert(data.message || 'Đăng ký không thành công.');
                    return;
                }

                localStorage.setItem('sporthub_token', data.token);
                localStorage.setItem('sporthub_user', JSON.stringify(data.user));
                setAlert('Đăng ký thành công. Token đã được lưu trong trình duyệt.', 'success');
                form.reset();
            } catch (error) {
                setAlert('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
            } finally {
                button.disabled = false;
                button.textContent = 'Đăng ký';
            }
        });
    </script>
@endpush
