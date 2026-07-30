@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent px-0 mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.contracts.index') }}">Hợp đồng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tạo hợp đồng</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-1">Tạo hợp đồng mới</h1>
                    <p class="text-muted mb-0">Điền thông tin hợp đồng để gửi cho Chủ sân.</p>
                </div>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Thông tin hợp đồng</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contracts.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="owner_id" class="form-label">Chủ sân</label>
                                <select id="owner_id" name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                    <option value="">Chọn chủ sân</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="title" class="form-label">Tiêu đề hợp đồng</label>
                                <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Ví dụ: Hợp tác kinh doanh mùa giải 2026">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="content" class="form-label">Nội dung hợp đồng</label>
                                <textarea id="content" name="content" rows="6" class="form-control @error('content') is-invalid @enderror" placeholder="Mô tả chi tiết điều khoản hợp đồng...">{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="commission_rate" class="form-label">Tỷ lệ hoa hồng (%)</label>
                                <input type="number" step="0.01" id="commission_rate" name="commission_rate" value="{{ old('commission_rate') }}" class="form-control @error('commission_rate') is-invalid @enderror" placeholder="10.00">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="start_date" class="form-label">Ngày bắt đầu</label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="end_date" class="form-label">Ngày kết thúc</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror" placeholder="Thêm ghi chú nội bộ...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save2 me-2"></i> Lưu hợp đồng
                            </button>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
