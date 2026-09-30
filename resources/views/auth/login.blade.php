@extends('layouts.auth')

@section('title', 'Đăng nhập | SportHub')

@section('content')
    <!-- Role Switcher -->
    <div class="role-switcher-container">
        <span class="role-switcher-label">Phân loại tài khoản:</span>
        <div class="role-switcher" role="tablist">
            <button 
                type="button" 
                id="role-btn-customer" 
                class="role-tab-btn active-customer" 
                onclick="switchAuthRole('customer')"
                role="tab"
                aria-selected="true"
            >
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Khách Hàng
            </button>
            
            <button 
                type="button" 
                id="role-btn-owner" 
                class="role-tab-btn" 
                onclick="switchAuthRole('owner')"
                role="tab"
                aria-selected="false"
            >
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4"/>
                </svg>
                Chủ Sân / Đối Tác
            </button>
        </div>
    </div>

    <!-- Alert Box -->
    <div id="auth-alert" class="auth-alert"></div>

    @if (session('error'))
        <div class="auth-alert is-error">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (session('owner_login_error'))
        <div class="auth-alert is-error" style="display: flex;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('owner_login_error') }}</span>
        </div>
    @endif

    <!-- ================= CUSTOMER LOGIN MODE ================= -->
    <div id="customer-mode-panel">
        <div class="form-heading">
            <span class="role-badge-tag customer">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                Tài khoản Người chơi
            </span>
            <h1>Đăng nhập Khách hàng</h1>
            <span>Đăng nhập để tìm sân, đặt lịch và quản lý lịch thi đấu của bạn.</span>
        </div>

        <form id="customer-login-form" class="auth-form" novalidate>
            <div class="field-group">
                <label for="customer-email" class="field-label">Địa chỉ Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input
                        id="customer-email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        class="field-input"
                        placeholder="ten@example.com"
                    >
                </div>
                <p data-error-for="email" class="field-error"></p>
            </div>

            <div class="field-group">
                <label for="customer-password" class="field-label">
                    <span>Mật khẩu</span>
                </label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input
                        id="customer-password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="field-input"
                        placeholder="Nhập mật khẩu của bạn"
                        style="padding-right: 2.75rem;"
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('customer-password', this)" tabindex="-1">
                        <svg class="eye-closed-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                        <svg class="eye-open-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
                <p data-error-for="password" class="field-error"></p>
            </div>

            <button id="customer-submit-btn" type="submit" class="submit-btn-customer">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Đăng nhập Khách hàng
            </button>
        </form>

        <div class="divider">
            <span class="divider-line"></span>
            <span class="divider-text">Hoặc đăng nhập với</span>
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

        <p class="auth-switch">
            Chưa có tài khoản khách?
            <a href="{{ route('register') }}">Đăng ký người chơi ngay</a>
        </p>
    </div>

    <!-- ================= OWNER LOGIN MODE ================= -->
    <div id="owner-mode-panel" style="display: none;">
        <div class="form-heading">
            <span class="role-badge-tag owner">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                Quản lý Cụm Sân & Cho Thuê
            </span>
            <h1>Đăng nhập Chủ sân</h1>
            <span>Đăng nhập dành riêng cho đối tác quản lý điểm sân thể thao trên SportHub.</span>
        </div>

        <form method="POST" action="{{ route('owner.login.store') }}" class="auth-form" novalidate>
            @csrf

            <div class="field-group">
                <label for="owner-email" class="field-label">Email Chủ sân</label>
                <div class="input-wrapper owner-wrapper">
                    <span class="input-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input
                        id="owner-email"
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
                <label for="owner-password" class="field-label">Mật khẩu</label>
                <div class="input-wrapper owner-wrapper">
                    <span class="input-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input
                        id="owner-password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="field-input owner-input"
                        placeholder="Nhập mật khẩu chủ sân"
                        style="padding-right: 2.75rem;"
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('owner-password', this)" tabindex="-1">
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

        <!-- Prominent Owner Registration Box -->
        <div class="owner-reg-card">
            <span class="reg-badge">✨ Đăng ký Đối Tác Mới</span>
            <h3>Bạn muốn hợp tác cho thuê sân bóng, cầu lông, tennis?</h3>
            <p>Trở thành đối tác của SportHub để tiếp cận hàng nghìn khách hàng đặt sân mỗi ngày, tối ưu lịch và tăng lợi nhuận kinh doanh!</p>
            <a href="{{ route('owner.register.page') }}" class="owner-reg-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Đăng ký tài khoản Chủ sân ngay
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Check URL or session for initial role
        const urlParams = new URLSearchParams(window.location.search);
        const initialRole = urlParams.get('role') === 'owner' || window.location.pathname.includes('/owner') ? 'owner' : 'customer';

        function switchAuthRole(role) {
            const customerBtn = document.getElementById('role-btn-customer');
            const ownerBtn = document.getElementById('role-btn-owner');
            const customerPanel = document.getElementById('customer-mode-panel');
            const ownerPanel = document.getElementById('owner-mode-panel');
            const alertBox = document.getElementById('auth-alert');

            if (alertBox) alertBox.style.display = 'none';

            if (role === 'owner') {
                customerBtn.className = 'role-tab-btn';
                customerBtn.setAttribute('aria-selected', 'false');
                ownerBtn.className = 'role-tab-btn active-owner';
                ownerBtn.setAttribute('aria-selected', 'true');
                
                customerPanel.style.display = 'none';
                ownerPanel.style.display = 'block';
            } else {
                ownerBtn.className = 'role-tab-btn';
                ownerBtn.setAttribute('aria-selected', 'false');
                customerBtn.className = 'role-tab-btn active-customer';
                customerBtn.setAttribute('aria-selected', 'true');
                
                ownerPanel.style.display = 'none';
                customerPanel.style.display = 'block';
            }
        }

        // Initialize role state
        switchAuthRole(initialRole);

        // Password Toggle Function
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

        // Customer Login AJAX submission
        const customerForm = document.querySelector('#customer-login-form');
        const customerSubmitBtn = document.querySelector('#customer-submit-btn');
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
            if (!alertBox) return;
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
            if (alertBox) alertBox.style.display = 'none';
        }

        function showErrors(errors = {}) {
            Object.entries(errors).forEach(([field, messages]) => {
                const node = document.querySelector(`[data-error-for="${field}"]`);
                if (!node) return;

                node.textContent = Array.isArray(messages) ? messages[0] : messages;
                node.classList.add('is-visible');
            });
        }

        if (customerForm) {
            customerForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearErrors();
                customerSubmitBtn.disabled = true;
                customerSubmitBtn.innerHTML = `
                    <svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Đang đăng nhập...
                `;

                try {
                    const response = await fetch('{{ route('web.login') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(Object.fromEntries(new FormData(customerForm))),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        showErrors(data.errors);
                        setAlert(data.message || 'Đăng nhập không thành công.');
                        return;
                    }

                    localStorage.setItem('sporthub_token', data.token);
                    localStorage.setItem('sporthub_user', JSON.stringify(data.user));
                    setAlert('Đăng nhập thành công. Đang chuyển hướng...', 'success');
                    customerForm.reset();
                    window.location.href = postAuthDestination();
                } catch (error) {
                    setAlert('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
                } finally {
                    customerSubmitBtn.disabled = false;
                    customerSubmitBtn.innerHTML = `
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Đăng nhập Khách hàng
                    `;
                }
            });
        }
    </script>
@endpush
