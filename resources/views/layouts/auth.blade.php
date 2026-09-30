<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SportHub')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --bg-body: #f8fafc;
            --panel-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --line-border: #e2e8f0;
            
            /* Customer Theme Colors (Emerald / Teal) */
            --primary-emerald: #059669;
            --primary-emerald-hover: #047857;
            --primary-emerald-soft: #d1fae5;
            --primary-emerald-glow: rgba(5, 150, 105, 0.15);

            /* Owner Theme Colors (Blue / Indigo) */
            --primary-indigo: #2563eb;
            --primary-indigo-hover: #1d4ed8;
            --primary-indigo-soft: #dbeafe;
            --primary-indigo-glow: rgba(37, 99, 235, 0.15);

            --danger: #ef4444;
            --danger-soft: #fef2f2;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
            --shadow-md: 0 4px 14px -2px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }

        /* Hero Left Panel */
        .auth-hero {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(135deg, #031b14 0%, #064e3b 45%, #0f172a 100%);
            color: #ffffff;
            padding: 48px 56px;
            position: relative;
            overflow: hidden;
        }

        /* Hero Glow Blobs */
        .auth-hero::before {
            content: '';
            position: absolute;
            top: -15%;
            left: -15%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, rgba(0,0,0,0) 70%);
            filter: blur(50px);
            pointer-events: none;
        }

        .auth-hero::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(0,0,0,0) 70%);
            filter: blur(50px);
            pointer-events: none;
        }

        .brand {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 14px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 2;
        }

        .brand-mark {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            font-weight: 800;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }

        .hero-copy {
            max-width: 620px;
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(52, 211, 153, 0.3);
            color: #6ee7b7;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
        }

        .hero-title {
            font-size: clamp(34px, 4vw, 48px);
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -1px;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .hero-text {
            color: #94a3b8;
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 36px;
        }

        /* Hero Feature Cards */
        .hero-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            position: relative;
            z-index: 2;
        }

        .hero-feat-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(12px);
            transition: transform 200ms ease, background 200ms ease;
        }

        .hero-feat-card:hover {
            background: rgba(255, 255, 255, 0.09);
            transform: translateY(-2px);
        }

        .hero-feat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .hero-feat-card.customer .hero-feat-icon {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .hero-feat-card.owner .hero-feat-icon {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .hero-feat-card h4 {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .hero-feat-card p {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.5;
        }

        /* Right Panel */
        .auth-panel {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: #ffffff;
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
        }

        .mobile-brand {
            display: none;
            margin-bottom: 28px;
        }

        /* Segmented Role Switcher */
        .role-switcher-container {
            margin-bottom: 28px;
        }

        .role-switcher-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: block;
        }

        .role-switcher {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #f1f5f9;
            padding: 5px;
            border-radius: var(--radius-md);
            gap: 4px;
            border: 1px solid var(--line-border);
        }

        .role-tab-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 14px;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #64748b;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 200ms ease;
        }

        .role-tab-btn:hover {
            color: var(--text-main);
        }

        .role-tab-btn.active-customer {
            background: #ffffff;
            color: var(--primary-emerald);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .role-tab-btn.active-owner {
            background: #ffffff;
            color: var(--primary-indigo);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* Form Heading */
        .form-heading {
            margin-bottom: 24px;
        }

        .form-heading .role-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .form-heading .role-badge-tag.customer {
            background: #ecfdf5;
            color: #047857;
        }

        .form-heading .role-badge-tag.owner {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .form-heading h1 {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.5px;
            color: var(--text-main);
        }

        .form-heading span {
            display: block;
            margin-top: 6px;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Alert Styling */
        .auth-alert {
            display: none;
            margin-bottom: 20px;
            border-radius: var(--radius-md);
            border: 1px solid transparent;
            padding: 13px 16px;
            font-size: 14px;
            line-height: 1.5;
            align-items: center;
            gap: 10px;
        }

        .auth-alert.is-success {
            display: flex;
            border-color: #a7f3d0;
            background: #ecfdf5;
            color: #065f46;
        }

        .auth-alert.is-error {
            display: flex;
            border-color: #fecaca;
            background: #fef2f2;
            color: var(--danger);
        }

        /* Form Controls */
        .auth-form {
            display: grid;
            gap: 18px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #334155;
            font-size: 13.5px;
            font-weight: 700;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 150ms ease;
        }

        .field-input {
            width: 100%;
            border: 1px solid var(--line-border);
            border-radius: var(--radius-md);
            background: #ffffff;
            color: var(--text-main);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            padding: 12.5px 14px 12.5px 44px;
            transition: all 180ms ease;
        }

        .field-input:focus {
            border-color: var(--primary-emerald);
            box-shadow: 0 0 0 4px var(--primary-emerald-glow);
        }

        .field-input.owner-input:focus {
            border-color: var(--primary-indigo);
            box-shadow: 0 0 0 4px var(--primary-indigo-glow);
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--primary-emerald);
        }

        .input-wrapper.owner-wrapper:focus-within .input-icon {
            color: var(--primary-indigo);
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 6px;
            transition: color 150ms ease;
        }

        .toggle-password-btn:hover {
            color: var(--text-main);
        }

        .field-error {
            display: none;
            margin-top: 4px;
            color: var(--danger);
            font-size: 12.5px;
            font-weight: 600;
        }

        .field-error.is-visible {
            display: block;
        }

        /* Buttons */
        .submit-btn-customer {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            cursor: pointer;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 800;
            padding: 13px 20px;
            transition: all 200ms ease;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn-customer:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
            transform: translateY(-1px);
        }

        .submit-btn-owner {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            cursor: pointer;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 800;
            padding: 13px 20px;
            transition: all 200ms ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn-owner:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        .submit-btn-customer:disabled,
        .submit-btn-owner:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 22px 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: var(--line-border);
        }

        .divider-text {
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Social Buttons */
        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px;
            border: 1px solid var(--line-border);
            border-radius: var(--radius-md);
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            font-size: 13.5px;
            text-decoration: none;
            transition: all 180ms ease;
        }

        .social-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        /* Owner Registration Callout Box */
        .owner-reg-card {
            margin-top: 22px;
            padding: 18px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            border: 1px solid #bfdbfe;
            position: relative;
        }

        .owner-reg-card .reg-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 800;
            color: #1d4ed8;
            background: #dbeafe;
            padding: 3px 10px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .owner-reg-card h3 {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 4px;
        }

        .owner-reg-card p {
            font-size: 13px;
            color: #3b82f6;
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .owner-reg-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px 16px;
            border-radius: var(--radius-md);
            background: #1d4ed8;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 800;
            text-decoration: none;
            transition: all 180ms ease;
            box-shadow: 0 2px 8px rgba(29, 78, 216, 0.2);
        }

        .owner-reg-btn:hover {
            background: #1e40af;
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.35);
            transform: translateY(-1px);
        }

        /* Footer Auth Switch */
        .auth-switch {
            margin-top: 24px;
            color: var(--text-muted);
            font-size: 14px;
            text-align: center;
        }

        .auth-switch a {
            color: var(--primary-emerald);
            font-weight: 700;
        }

        .auth-switch a.owner-link {
            color: var(--primary-indigo);
        }

        .auth-switch a:hover {
            text-decoration: underline;
        }

        /* Responsive Breakpoints */
        @media (max-width: 960px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-hero {
                display: none;
            }

            .auth-panel {
                padding: 32px 20px;
            }

            .mobile-brand {
                display: inline-flex;
            }

            .brand-mark {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <!-- Left Hero Section -->
        <section class="auth-hero">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark">S</span>
                <span>SportHub</span>
            </a>

            <div class="hero-copy">
                <div class="hero-badge">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Nền tảng Đặt sân & Quản lý Thể thao #1
                </div>
                <h1 class="hero-title">Kết nối niềm đam mê thể thao một cách nhanh chóng & chuyên nghiệp.</h1>
                <p class="hero-text">
                    Cho dù bạn là người chơi đang tìm sân thi đấu hay đối tác chủ sân muốn tối ưu công suất hoạt động, SportHub mang lại giải pháp toàn diện và hiện đại nhất.
                </p>

                <div class="hero-features">
                    <div class="hero-feat-card customer">
                        <div class="hero-feat-icon">⚡</div>
                        <h4>Dành cho Người chơi</h4>
                        <p>Đặt sân tức thì, nhận thông báo thời gian thực và thanh toán VNPay tiện lợi.</p>
                    </div>

                    <div class="hero-feat-card owner">
                        <div class="hero-feat-icon">🏟️</div>
                        <h4>Dành cho Chủ sân</h4>
                        <p>Bảng quản lý sân trực quan, theo dõi doanh thu và duyệt lịch thông minh.</p>
                    </div>
                </div>
            </div>

            <div style="font-size: 13px; color: #64748b; position: relative; z-index: 2;">
                &copy; {{ date('Y') }} SportHub. Bảo lưu mọi quyền.
            </div>
        </section>

        <!-- Right Form Panel -->
        <section class="auth-panel">
            <div class="auth-card">
                <a href="{{ url('/') }}" class="brand mobile-brand">
                    <span class="brand-mark">S</span>
                    <span>SportHub</span>
                </a>

                @yield('content')
            </div>
        </section>
    </main>

    @stack('scripts')
</body>
</html>

