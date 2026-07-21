{{-- resources/views/admin/financial-settings/index.blade.php --}}
@extends('admin.layouts.app') {{-- Nhớ sửa lại tên layout theo đúng template của bạn --}}

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Cấu Hình Tài Chính & Hoa Hồng (Marketplace)</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.financial-settings.update') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Tỷ lệ hoa hồng sàn mặc định (%)</label>
                    <input type="number" step="0.01" class="form-control" name="default_commission_rate" 
                           value="{{ $settings['default_commission_rate'] ?? 10 }}">
                    <small class="text-muted">Áp dụng cho các sân không cấu hình hoa hồng riêng.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hạn mức nợ tối đa (VNĐ)</label>
                    <input type="number" class="form-control" name="owner_credit_limit" 
                           value="{{ $settings['owner_credit_limit'] ?? -1000000 }}">
                    <small class="text-danger">Nhập số âm. Ví dụ: -1000000 (Chủ sân được nợ tối đa 1 triệu, quá mức sẽ khóa sân).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hạn mức rút tiền tối thiểu (VNĐ)</label>
                    <input type="number" class="form-control" name="minimum_withdraw" 
                           value="{{ $settings['minimum_withdraw'] ?? 200000 }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Hạn mức nạp tiền tối thiểu (VNĐ)</label>
                    <input type="number" class="form-control" name="minimum_topup" 
                           value="{{ $settings['minimum_topup'] ?? 50000 }}">
                </div>

                <button type="submit" class="btn btn-primary">Lưu Cấu Hình</button>
            </form>
        </div>
    </div>
</div>
@endsection