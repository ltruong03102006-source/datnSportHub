@extends('admin.layouts.app')

@push('styles')
<style>
    .breadcrumb-custom { font-size: 12px; color: var(--text-muted); margin-bottom: 8px; font-weight: 500; }
    .breadcrumb-custom span { color: #f59e0b; font-weight: 600; }
    .header-section { margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; }
    .header-title-box h2 { font-size: 24px; font-weight: 700; color: var(--text-dark); margin: 0 0 6px 0; }
    .header-title-box p { font-size: 13px; color: var(--text-muted); margin: 0; }
    
    .data-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 2px 10px rgba(0,0,0,0.02); padding: 24px; max-width: 800px;}
    .form-label { font-size: 12px; font-weight: 700; color: #495057; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .form-control { border-radius: 8px; padding: 10px 16px; font-size: 14px; border: 1px solid #ced4da; }
    .form-control:focus { border-color: #f59e0b; box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.25); }
    .input-group-text { background-color: #f8f9fa; border: 1px solid #ced4da; color: #495057; font-weight: 600; border-radius: 0 8px 8px 0; }
    .input-group .form-control { border-radius: 8px 0 0 8px; border-right: 0; }
    
    .btn-save { background-color: #f59e0b; color: white; font-weight: 600; padding: 10px 24px; border-radius: 8px; border: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-save:hover { background-color: #d97706; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(217, 119, 6, 0.2); color: white; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div style="background-color: #eafaf1; color: #2ecc71; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #d4efdf; font-size: 14px; font-weight: 600;">
        <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i> {{ session('success') }}
    </div>
@endif

<div class="breadcrumb-custom">
    Cài đặt hệ thống > <span>Cấu hình tài chính</span>
</div>
<div class="header-section">
    <div class="header-title-box">
        <h2>Cấu hình Tài chính & Hoa hồng</h2>
        <p>Quản lý tỷ lệ ăn chia, hạn mức nạp/rút và công nợ của hệ thống Marketplace.</p>
    </div>
</div>

<div class="data-card">
    <form action="{{ route('admin.financial-settings.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label">Tỷ lệ hoa hồng sàn mặc định</label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="default_commission_rate" value="{{ $settings['default_commission_rate'] ?? 10 }}">
                    <span class="input-group-text">%</span>
                </div>
                <small class="text-muted d-block mt-2">Áp dụng cho các sân không cấu hình hoa hồng riêng.</small>
@error('default_commission_rate')
    <small class="text-danger d-block mt-1 fw-bold">{{ $message }}</small>
@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Hạn mức nợ tối đa</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="owner_credit_limit" value="{{ $settings['owner_credit_limit'] ?? -1000000 }}">
                    <span class="input-group-text">VNĐ</span>
                </div>
                <small class="text-danger d-block mt-2"><i class="fa-solid fa-triangle-exclamation"></i> Nhập số âm. VD: -1000000 (Chủ sân được nợ tối đa 1 triệu).</small>
@error('owner_credit_limit')
    <small class="text-danger d-block mt-1 fw-bold">{{ $message }}</small>
@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Rút tiền tối thiểu</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="minimum_withdraw" value="{{ $settings['minimum_withdraw'] ?? 200000 }}">
                    <span class="input-group-text">VNĐ</span>
                </div>
                <!-- SỬA minimum_topup THÀNH minimum_withdraw Ở ĐÂY -->
                @error('minimum_withdraw')
                    <small class="text-danger d-block mt-1 fw-bold">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Nạp tiền tối thiểu</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="minimum_topup" value="{{ $settings['minimum_topup'] ?? 50000 }}">
                    <span class="input-group-text">VNĐ</span>
                </div>
                <!-- Ở đây giữ nguyên minimum_topup là đúng rồi -->
                @error('minimum_topup')
                    <small class="text-danger d-block mt-1 fw-bold">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-12 mt-2">
                <hr style="border-color: #e9ecef; margin-bottom: 20px;">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Cấu Hình
                </button>
            </div>
        </div>
    </form>
</div>
@endsection