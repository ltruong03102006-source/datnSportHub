@extends('layouts.auth')

@section('title', 'Đăng ký Đối tác Chủ sân | SportHub')

@section('content')
    <div class="form-heading">
        <span class="role-badge-tag owner">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
            Đăng ký Đối Tác Chủ Sân
        </span>
        <h1>Tạo tài khoản Chủ sân</h1>
        <span>Điền thông tin bên dưới để mở tài khoản quản lý điểm sân thể thao trên SportHub.</span>
    </div>

    @if(session('success'))
        <div class="auth-alert is-success" style="display: flex; margin-bottom: 20px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($registration) && $registration->status === 'rejected' && $registration->rejection_reason)
        <div class="auth-alert is-error" style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Yêu cầu đăng ký trước đó bị từ chối:
            </div>
            <div style="font-size: 13.5px; background: rgba(255,255,255,0.8); padding: 8px 12px; border-radius: 6px; border: 1px solid #fecaca; color: #b91c1c; width: 100%;">
                {{ $registration->rejection_reason }}
            </div>
            <p style="font-size: 12.5px; color: #7f1d1d; margin-top: 2px;">
                Vui lòng kiểm tra lại thông tin và nộp lại yêu cầu để Admin xét duyệt.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('owner.register.store') }}" class="auth-form" novalidate>
        @csrf

        <div class="field-group">
            <label for="name" class="field-label">Họ và tên chủ sân</label>
            <div class="input-wrapper owner-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input
                    id="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    required
                    class="field-input owner-input"
                    value="{{ old('name', auth()->user()?->name) }}"
                    placeholder="Nguyễn Văn A"
                >
            </div>
            @error('name')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <label for="phone" class="field-label">Số điện thoại liên hệ</label>
            <div class="input-wrapper owner-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </span>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    autocomplete="tel"
                    required
                    class="field-input owner-input"
                    value="{{ old('phone') }}"
                    placeholder="0901234567"
                >
            </div>
            @error('phone')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <label for="email" class="field-label">Email liên hệ & Đăng nhập</label>
            <div class="input-wrapper owner-wrapper">
                <span class="input-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    class="field-input owner-input"
                    value="{{ old('email', auth()->user()?->email) }}"
                    placeholder="owner@example.com"
                >
            </div>
            @error('email')
                <p class="field-error is-visible">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="submit-btn-owner" style="margin-top: 6px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Gửi Đăng ký Chủ sân
        </button>
    </form>

    <p class="auth-switch">
        Đã có tài khoản chủ sân?
        <a href="{{ route('owner.login.page') }}" class="owner-link">Đăng nhập ngay</a>
    </p>
@endsection
