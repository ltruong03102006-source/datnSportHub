@extends('admin.layouts.app')

@section('title', 'Cấu hình hệ thống')

@push('styles')
<style>
    .settings-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .page-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
    }

    .page-heading h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
    }

    .page-heading p {
        color: var(--text-muted);
        font-size: 14px;
    }

    .settings-panel {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-dark);
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: #27ae60;
    }

    .form-text {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 6px;
        display: block;
    }

    .btn-submit {
        background: #27ae60;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0 20px;
        min-height: 42px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .btn-submit:hover {
        background: #219653;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-success {
        background: #eafaf1;
        color: #229954;
        border: 1px solid rgba(34, 153, 84, 0.2);
    }
</style>
@endpush

@section('content')
<div class="settings-page">
    <div class="page-heading">
        <div>
            <h2>Cấu hình hệ thống</h2>
            <p>Tùy chỉnh các thông số hoạt động của hệ thống.</p>
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
                <span class="form-text">
                    Khoảng thời gian tối đa khách hàng được phép thanh toán kể từ khi tạo đơn đặt sân. Sau thời gian này, nếu chưa thanh toán, hệ thống sẽ tự động giải phóng slot.
                </span>
                @error('booking_hold_time')
                    <span style="color: #e74c3c; font-size: 13px; margin-top: 6px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình
            </button>
        </form>
    </div>
</div>
@endsection
