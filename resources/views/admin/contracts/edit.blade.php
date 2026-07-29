@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h1 class="h3 mb-1">Chỉnh sửa hợp đồng</h1>
                            <p class="text-muted mb-0">Cập nhật nội dung và thông tin hợp đồng kinh doanh.</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                        </div>
                    </div>

                    <form action="{{ route('admin.contracts.update', $contract) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="contract_code" class="form-label">Mã hợp đồng</label>
                                <input type="text" id="contract_code" class="form-control" value="{{ $contract->contract_code }}" readonly>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="owner_id" class="form-label">Chủ sân</label>
                                <select id="owner_id" name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                    <option value="">Chọn chủ sân</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ old('owner_id', $contract->owner_id) == $owner->id ? 'selected' : '' }}>
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
                                <input type="text" id="title" name="title" value="{{ old('title', $contract->title) }}" class="form-control @error('title') is-invalid @enderror">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="content" class="form-label">Nội dung hợp đồng</label>
                                <textarea id="content" name="content" rows="6" class="form-control @error('content') is-invalid @enderror">{{ old('content', $contract->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="commission_rate" class="form-label">Tỷ lệ hoa hồng (%)</label>
                                <input type="number" step="0.01" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $contract->commission_rate) }}" class="form-control @error('commission_rate') is-invalid @enderror">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="start_date" class="form-label">Ngày bắt đầu</label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="end_date" class="form-label">Ngày kết thúc</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror">{{ old('note', $contract->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
