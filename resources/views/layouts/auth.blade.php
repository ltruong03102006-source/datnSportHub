<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SportHub')</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --bg: #f7f8f5;
            --panel: #ffffff;
            --text: #18181b;
            --muted: #5f6368;
            --line: #d9ded7;
            --primary: #047857;
            --primary-dark: #065f46;
            --primary-soft: #d1fae5;
            --danger: #b91c1c;
            --danger-soft: #fee2e2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--bg);
            color: var(--text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 480px;
        }

        .auth-hero {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            justify-content: space-between;
            background: #052e25;
            color: #ffffff;
            padding: 40px 48px;
        }

        .brand {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
        }

        .brand-mark {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border-radius: 8px;
            background: #ffffff;
            color: #052e25;
            font-weight: 800;
        }

        .hero-copy {
            max-width: 720px;
        }

        .hero-eyebrow {
            margin: 0 0 16px;
            color: #a7f3d0;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 0;
            font-size: clamp(40px, 5vw, 58px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .hero-text {
            max-width: 620px;
            margin: 24px 0 0;
            color: #d1fae5;
            font-size: 16px;
            line-height: 1.75;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .hero-stat {
            min-height: 104px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            padding: 16px;
            color: #d1fae5;
        }

        .hero-stat strong {
            display: block;
            color: #ffffff;
            font-size: 26px;
            line-height: 1.1;
        }

        .hero-stat span {
            display: block;
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.45;
        }

        .auth-panel {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
        }

        .mobile-brand {
            display: none;
            margin-bottom: 32px;
        }

        .form-heading {
            margin-bottom: 30px;
        }

        .form-heading p {
            margin: 0;
            color: var(--primary);
            font-size: 14px;
            font-weight: 700;
        }

        .form-heading h1 {
            margin: 8px 0 0;
            font-size: 32px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .form-heading span {
            display: block;
            margin-top: 12px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .auth-alert {
            display: none;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            padding: 12px 14px;
            font-size: 14px;
            line-height: 1.5;
        }

        .auth-alert.is-success {
            display: block;
            border-color: #a7f3d0;
            background: #ecfdf5;
            color: #065f46;
        }

        .auth-alert.is-error {
            display: block;
            border-color: #fecaca;
            background: #fef2f2;
            color: var(--danger);
        }

        .auth-form {
            display: grid;
            gap: 20px;
        }

        .field-label,
        .field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
            color: #27272a;
            font-size: 14px;
            font-weight: 600;
        }

        .text-button {
            border: 0;
            background: transparent;
            color: var(--primary);
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            padding: 0;
        }

        .text-button:hover,
        .auth-switch a:hover {
            color: var(--primary-dark);
        }

        .field-input {
            width: 100%;
            border: 1px solid #cfd6ce;
            border-radius: 8px;
            background: var(--panel);
            color: var(--text);
            font: inherit;
            font-size: 14px;
            outline: none;
            padding: 13px 14px;
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }

        .field-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-soft);
        }

        .field-error {
            display: none;
            margin: 8px 0 0;
            color: var(--danger);
            font-size: 13px;
            line-height: 1.45;
        }

        .field-error.is-visible {
            display: block;
        }

        .submit-button {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            padding: 13px 18px;
            transition: background 160ms ease, box-shadow 160ms ease;
        }

        .submit-button:hover {
            background: var(--primary-dark);
        }

        .submit-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px var(--primary-soft);
        }

        .submit-button:disabled {
            cursor: not-allowed;
            background: #34a581;
        }

        .auth-switch {
            margin: 24px 0 0;
            color: var(--muted);
            font-size: 14px;
            text-align: center;
        }

        .auth-switch a {
            color: var(--primary);
            font-weight: 800;
        }

        @media (max-width: 960px) {
            .auth-shell {
                display: block;
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
                background: var(--primary);
                color: #ffffff;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-hero">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark">S</span>
                <span>SportHub</span>
            </a>

            <div class="hero-copy">
                <p class="hero-eyebrow">Đặt sân nhanh, quản lý gọn</p>
                <h1 class="hero-title">Nền tảng kết nối người chơi với sân thể thao phù hợp.</h1>
                <p class="hero-text">
                    Tìm sân, đặt lịch và quản lý tài khoản trong một trải nghiệm đơn giản, rõ ràng và tối ưu cho người dùng SportHub.
                </p>
            </div>

            <div class="hero-stats" aria-label="SportHub highlights">
                <div class="hero-stat">
                    <strong>24/7</strong>
                    <span>Đăng ký tài khoản</span>
                </div>
                <div class="hero-stat">
                    <strong>API</strong>
                    <span>Kết nối Sanctum</span>
                </div>
                <div class="hero-stat">
                    <strong>Fast</strong>
                    <span>Giao diện nhẹ</span>
                </div>
            </div>
        </section>

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
