@extends('layouts.auth')

@section('title', 'Đăng nhập chủ sân | SportHub')

@section('content')
    <div class="form-heading">
        <p>Chủ sân</p>
        <h1>Đăng nhập chủ sân</h1>
        <span>Đăng nhập bằng tài khoản chủ sân để tiếp tục quản lý điểm sân và sân nhỏ.</span>
    </div>

    @if (session('owner_login_error'))
        <div class="auth-alert is-error">
            {{ session('owner_login_error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('owner.login.store') }}" class="auth-form" novalidate>
        @csrf

        <div>
            <label for="email" class="field-label">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                class="field-input"
                value="{{ old('email') }}"
                placeholder="owner@example.com"
            >
            @error('email')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Mật khẩu</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="field-input"
                placeholder="Nhập mật khẩu"
            >
            @error('password')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="submit-button">
            Đăng nhập chủ sân
        </button>
    </form>

    <p class="auth-switch">
        Chưa có tài khoản chủ sân?
        <a href="{{ route('owner.register.page') }}">Đăng ký ngay</a>
    </p>
@endsection
