@extends('admin.layouts.app')

@section('title', 'Cấu hình hệ thống')

@push('styles')
<style>
    .settings-page { display:flex; flex-direction:column; gap:22px; }
    .page-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; }
    .page-heading h2 { font-size:24px; font-weight:800; color:var(--text-dark); margin-bottom:6px; }
    .page-heading p { color:var(--text-muted); font-size:14px; }
    .settings-panel { background:var(--card-bg); border:1px solid var(--border-color); border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,.02); max-width:980px; }
    .settings-section { border-top:1px solid var(--border-color); margin-top:24px; padding-top:24px; }
    .settings-section:first-child { border-top:0; margin-top:0; padding-top:0; }
    .section-heading { color:var(--text-dark); font-size:17px; font-weight:900; margin:0 0 5px; }
    .section-description { color:var(--text-muted); font-size:13px; margin:0 0 18px; }
    .settings-grid { display:grid; grid-template-columns:minmax(0,1fr) 280px; gap:24px; align-items:start; }
    .form-group { margin-bottom:18px; }
    .form-label { display:block; font-weight:800; margin-bottom:8px; color:var(--text-dark); }
    .form-control { width:100%; min-height:42px; padding:0 14px; border:1px solid var(--border-color); border-radius:8px; font-size:14px; outline:none; transition:border-color .2s; }
    .form-control:focus { border-color:#27ae60; }
    .form-text { display:block; margin-top:6px; color:var(--text-muted); font-size:13px; }
    .btn-submit { display:inline-flex; align-items:center; gap:8px; min-height:42px; padding:0 20px; border:0; border-radius:8px; background:#27ae60; color:#fff; font-size:14px; font-weight:800; cursor:pointer; }
    .btn-submit:hover { background:#219653; }
    .alert { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:600; }
    .alert-success { background:#eafaf1; color:#229954; border:1px solid rgba(34,153,84,.2); }
    .qr-preview { border:1px dashed var(--border-color); border-radius:14px; background:#f8fafc; padding:18px; text-align:center; }
    .qr-preview img { width:210px; height:210px; object-fit:contain; border-radius:12px; background:#fff; border:1px solid var(--border-color); padding:8px; }
    .error-text { color:#e74c3c; font-size:13px; margin-top:6px; display:block; }
    @media (max-width:900px) { .settings-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
@php
    $adminQrBank = old('payment_qr_bank_name', $paymentQrBankName);
    $adminQrAccount = old('payment_qr_account_no', $paymentQrAccountNo);
    $adminQrName = old('payment_qr_account_name', $paymentQrAccountName);
    $adminQrPreview = null;

    if ($adminQrBank && $adminQrAccount) {
        $adminQrPreview = 'https://img.vietqr.io/image/'
            . trim($adminQrBank)
            . '-'
            . trim($adminQrAccount)
            . '-compact2.png?amount=50000&addInfo='
            . urlencode('THANH TOAN TEST')
            . '&accountName='
            . urlencode(trim($adminQrName ?: 'SPORTHUB'));
    }
@endphp

<div class="settings-page">
    <div class="page-heading">
        <div>
            <h2>Cấu hình hệ thống</h2>
            <p>Tùy chỉnh cài đặt đặt sân, thanh toán và mã QR của nền tảng.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="settings-panel">
        <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf

            <div class="settings-section">
                <h3 class="section-heading">Cài đặt đặt sân</h3>
                <p class="section-description">Thiết lập thời gian giữ chỗ khi khách đang chờ thanh toán.</p>

                <div class="form-group">
                    <label class="form-label" for="booking_hold_time">Thời gian giữ chỗ (phút)</label>
                    <input
                        type="number"
                        id="booking_hold_time"
                        name="booking_hold_time"
                        class="form-control"
                        value="{{ old('booking_hold_time', $bookingHoldTime) }}"
                        min="1"
                        max="1440"
                        required
                    >
                    <span class="form-text">Sau thời gian này, nếu khách chưa thanh toán, hệ thống sẽ giải phóng slot.</span>
                    @error('booking_hold_time') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="settings-section">
                <h3 class="section-heading">Cấu hình QR thanh toán</h3>
                <p class="section-description">
                    QR này do admin quản lý và dùng cho đặt sân lẻ, đặt gói. Chủ sân không cần tự cấu hình QR nhận tiền nữa.
                </p>

                <div class="settings-grid">
                    <div>
                        <div class="form-group">
                            <label class="form-label" for="payment_qr_bank_name">Mã ngân hàng / BIN</label>
                            <input
                                type="text"
                                id="payment_qr_bank_name"
                                name="payment_qr_bank_name"
                                class="form-control"
                                value="{{ $adminQrBank }}"
                                placeholder="VD: 970422 hoặc MB"
                            >
                            <span class="form-text">Khuyến nghị dùng mã BIN VietQR, ví dụ MB là 970422.</span>
                            @error('payment_qr_bank_name') <span class="error-text">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="payment_qr_account_no">Số tài khoản nhận tiền</label>
                            <input
                                type="text"
                                id="payment_qr_account_no"
                                name="payment_qr_account_no"
                                class="form-control"
                                value="{{ $adminQrAccount }}"
                                placeholder="VD: 0397959270"
                            >
                            @error('payment_qr_account_no') <span class="error-text">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="payment_qr_account_name">Tên chủ tài khoản</label>
                            <input
                                type="text"
                                id="payment_qr_account_name"
                                name="payment_qr_account_name"
                                class="form-control"
                                value="{{ $adminQrName }}"
                                placeholder="VD: LE XUAN TRUONG"
                            >
                            @error('payment_qr_account_name') <span class="error-text">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="qr-preview">
                        <strong style="display:block; margin-bottom:12px;">Xem trước QR test</strong>
                        @if($adminQrPreview)
                            <img src="{{ $adminQrPreview }}" alt="QR thanh toán admin">
                            <span class="form-text">Số tiền test: 50.000đ</span>
                        @else
                            <div style="height:210px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
                                Nhập ngân hàng và số tài khoản để xem trước QR.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình
            </button>
        </form>
    </div>
</div>
@endsection
