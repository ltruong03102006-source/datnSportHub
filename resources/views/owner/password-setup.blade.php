@extends('layouts.auth')

@section('title', 'Thiết lập mật khẩu chủ sân | SportHub')

@section('content')
    <style>
        .password-setup-card { max-width: 460px; margin: 18px auto; padding: 34px; border: 1px solid #e5e7eb; border-radius: 20px; background: #fff; box-shadow: 0 18px 45px rgba(15, 23, 42, .10); }
        .password-setup-heading { text-align: center; margin-bottom: 28px; }
        .password-setup-heading p { margin: 0; color: #059669; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .password-setup-heading h1 { margin: 8px 0 10px; color: #111827; font-size: 28px; line-height: 1.2; }
        .password-setup-heading span { color: #6b7280; font-size: 14px; line-height: 1.6; }
        .password-setup-form { display: grid; gap: 18px; }
        .password-setup-form label { display: block; margin-bottom: 8px; color: #374151; font-size: 14px; font-weight: 600; }
        .password-setup-form input { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: 12px 14px; color: #111827; font: inherit; outline: none; transition: border-color .2s, box-shadow .2s; }
        .password-setup-form input:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .14); }
        .password-setup-hint { display: block; margin: 6px 0 0; color: #9ca3af; font-size: 12px; line-height: 1.4; }
        .password-setup-button { width: 100%; border: 0; border-radius: 10px; padding: 13px 18px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 8px 18px rgba(5, 150, 105, .22); color: #fff; cursor: pointer; font: inherit; font-weight: 700; transition: transform .2s, box-shadow .2s; }
        .password-setup-button:hover { transform: translateY(-1px); box-shadow: 0 11px 22px rgba(5, 150, 105, .28); }
        .password-setup-error { margin: 0; color: #dc2626; font-size: 13px; }
    </style>

    <div class="password-setup-card">
        <div class="password-setup-heading">
            <p>Đối tác SportHub</p>
            <h1>Thiết lập mật khẩu</h1>
            <span>Tạo mật khẩu để bắt đầu quản lý cơ sở sân của bạn.</span>
        </div>

        <form method="POST" action="{{ route('owner.password.setup.store') }}" class="password-setup-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="password">Mật khẩu mới</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" autofocus>
                <p class="password-setup-hint">Tối thiểu 8 ký tự.</p>
                @error('password')<p class="password-setup-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation">Xác nhận mật khẩu</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>

            @error('token')<p class="password-setup-error">{{ $message }}</p>@enderror

            <button type="submit" class="password-setup-button">Lưu mật khẩu</button>
        </form>
    </div>
@endsection
