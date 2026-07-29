@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent px-0 mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.contracts.index') }}">Hợp đồng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-1">Chi tiết hợp đồng</h1>
                    <p class="text-muted mb-0">Xem thông tin hợp đồng giữa Admin và Chủ sân.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
                    <a href="{{ route('admin.contracts.pdf', $contract) }}" class="btn btn-secondary">Tải PDF</a>
                    <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-primary">Chỉnh sửa</a>
                    @if(in_array($contract->status, ['draft', 'rejected'], true))
                        <form action="{{ route('admin.contracts.send', $contract) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn muốn gửi hợp đồng này?');">Gửi hợp đồng</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Thông tin hợp đồng</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Mã hợp đồng</dt>
                        <dd class="col-sm-8">{{ $contract->contract_code }}</dd>

                        <dt class="col-sm-4">Tiêu đề</dt>
                        <dd class="col-sm-8">{{ $contract->title }}</dd>

                        <dt class="col-sm-4">Nội dung</dt>
                        <dd class="col-sm-8">{!! nl2br(e($contract->content)) !!}</dd>

                        <dt class="col-sm-4">Trạng thái</dt>
                        <dd class="col-sm-8">
                            @php
                                $badge = match($contract->status) {
                                    'draft' => 'secondary',
                                    'sent' => 'primary',
                                    'accepted' => 'success',
                                    'rejected' => 'danger',
                                    'expired' => 'warning',
                                    'terminated' => 'dark',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }} text-capitalize">{{ $contract->status }}</span>
                        </dd>

                        <dt class="col-sm-4">Hoa hồng (%)</dt>
                        <dd class="col-sm-8">{{ number_format($contract->commission_rate, 2) }}</dd>

                        <dt class="col-sm-4">Ngày bắt đầu</dt>
                        <dd class="col-sm-8">{{ $contract->start_date?->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4">Ngày kết thúc</dt>
                        <dd class="col-sm-8">{{ $contract->end_date?->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4">Ngày tạo</dt>
                        <dd class="col-sm-8">{{ $contract->created_at->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Ngày cập nhật</dt>
                        <dd class="col-sm-8">{{ $contract->updated_at->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Thời gian ký</dt>
                        <dd class="col-sm-8">{{ $contract->signed_at?->format('Y-m-d H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4">Ghi chú</dt>
                        <dd class="col-sm-8">{{ $contract->note ?? '-' }}</dd>

                        <dt class="col-sm-4">Lý do từ chối</dt>
                        <dd class="col-sm-8">{{ $contract->rejection_reason ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Thông tin Chủ sân</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Họ và tên:</strong> {{ $contract->owner?->name ?? '-' }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $contract->owner?->email ?? '-' }}</p>
                    <p class="mb-0"><strong>Số điện thoại:</strong> {{ $contract->owner?->phone ?? '-' }}</p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Thông tin Admin tạo</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Họ và tên:</strong> {{ $contract->creator?->name ?? '-' }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $contract->creator?->email ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
