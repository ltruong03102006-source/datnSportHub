@extends('layouts.auth')

@section('title', 'Đăng ký chủ sân | SportHub')

@section('content')
    <style>
        .owner-register-heading {
            margin-bottom: 32px;
            text-align: center;
        }

        .owner-register-heading p {
            margin: 0;
            color: #1e40af;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .owner-register-heading h1 {
            margin: 8px 0 0;
            font-size: 32px;
            line-height: 1.15;
            letter-spacing: -0.5px;
            color: #111827;
        }

        .owner-register-heading span {
            display: block;
            margin-top: 12px;
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
        }

        .owner-register-form {
            display: grid;
            gap: 20px;
            margin-bottom: 32px;
        }

        .owner-submit-button {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            font-size: 15px;
            font-weight: 700;
            padding: 13px 18px;
            transition: all 200ms ease;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .owner-submit-button:hover {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
            transform: translateY(-2px);
        }

        .owner-submit-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.3);
        }

        .owner-submit-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1), 0 4px 12px rgba(30, 64, 175, 0.3);
        }

        .owner-submit-button:disabled {
            cursor: not-allowed;
            background: #9ca3af;
            box-shadow: none;
            transform: none;
        }
    </style>

    <div class="owner-register-heading">
        <p>Đối Tác SportHub</p>
        <h1>Đăng ký làm chủ sân</h1>
        <span>Điền thông tin để tạo tài khoản chủ sân. Sau đó bạn sẽ tự thiết lập mật khẩu để bắt đầu sử dụng.</span>
    </div>

    <form method="POST" action="{{ route('owner.register.store') }}" class="owner-register-form" novalidate>
        @csrf

        <div>
            <label for="name" class="field-label">Họ và tên chủ sân</label>
            <input
                id="name"
                name="name"
                type="text"
                autocomplete="name"
                required
                class="field-input"
                value="{{ old('name', auth()->user()?->name) }}"
                placeholder="Nguyễn Văn A"
            >
            @error('name')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="field-label">Số điện thoại</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                autocomplete="tel"
                required
                class="field-input"
                value="{{ old('phone') }}"
                placeholder="0901234567"
            >
            @error('phone')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="field-label">Email liên hệ</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                class="field-input"
                value="{{ old('email', auth()->user()?->email) }}"
                placeholder="owner@example.com"
            >
            @error('email')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="owner-submit-button">
            Đăng ký ngay
        </button>
    </form>

    <p class="auth-switch">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập ngay</a>
    </p>
@endsection
