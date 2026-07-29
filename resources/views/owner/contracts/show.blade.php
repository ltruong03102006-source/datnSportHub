<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hợp đồng - Chủ sân</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .page-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(15, 23, 42, .04); }
    </style>
</head>
<body class="text-slate-800 antialiased min-vh-100">
    <nav class="bg-white shadow-sm border-bottom border-slate-200 px-4 py-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('owner.dashboard') }}" class="text-xl fw-bold text-slate-800">SportHub</a>
        </div>
        <div class="d-flex align-items-center gap-3 small text-secondary">
            <a href="{{ route('owner.dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a>
            <a href="{{ route('owner.venues.index') }}" class="text-decoration-none text-secondary">Cơ sở</a>
            <a href="{{ route('owner.contracts.index') }}" class="text-decoration-none text-success fw-semibold">Hợp đồng</a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="page-card p-4 mb-4">
            <div class="row align-items-center gy-3">
                <div class="col-md-8">
                    <h1 class="h3 mb-1">Chi tiết hợp đồng</h1>
                    <p class="text-muted mb-0">Xem thông tin hợp đồng và quyết định đồng ý hoặc từ chối.</p>
                </div>
                <div class="col-md-4 d-flex flex-wrap justify-content-md-end gap-2">
                    <a href="{{ route('owner.contracts.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
                    <a href="{{ route('owner.contracts.download', $contract) }}" class="btn btn-secondary btn-sm">Tải PDF</a>
                    @if($contract->status === 'sent')
                        <form action="{{ route('owner.contracts.accept', $contract) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Đồng ý</button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectContractModal">Từ chối</button>
                    @endif
                </div>
            </div>
        </div>

        @if($contract->status === 'rejected' && $contract->rejection_reason)
            <div class="alert alert-danger rounded-4">
                <h6 class="alert-heading">Lý do từ chối</h6>
                <p class="mb-0">{{ $contract->rejection_reason }}</p>
            </div>
        @endif

        <div class="page-card p-4 mb-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <h2 class="h5 mb-3">Thông tin hợp đồng</h2>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Mã hợp đồng</div>
                        <div class="col-sm-8 fw-semibold">{{ $contract->contract_code }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Tiêu đề</div>
                        <div class="col-sm-8 fw-semibold">{{ $contract->title }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nội dung</div>
                        <div class="col-sm-8"><p class="mb-0">{!! nl2br(e($contract->content)) !!}</p></div>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Hoa hồng</div>
                                <div class="fw-semibold">{{ number_format($contract->commission_rate, 2) }}%</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Trạng thái</div>
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
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Ngày bắt đầu</div>
                                <div class="fw-semibold">{{ $contract->start_date?->format('Y-m-d') }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Ngày kết thúc</div>
                                <div class="fw-semibold">{{ $contract->end_date?->format('Y-m-d') }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Ngày tạo</div>
                                <div class="fw-semibold">{{ $contract->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Ngày cập nhật</div>
                                <div class="fw-semibold">{{ $contract->updated_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 g-3 mt-3">
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Ngày ký hợp đồng</div>
                                <div class="fw-semibold">{{ $contract->signed_at?->format('Y-m-d H:i') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-3 p-3">
                                <div class="text-muted small">Ghi chú</div>
                                <div class="fw-semibold">{{ $contract->note ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-muted small">Lý do từ chối</div>
                        <div class="fw-semibold">{{ $contract->rejection_reason ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="page-card p-4 mb-4">
                        <h2 class="h6 mb-3">Thông tin Admin</h2>
                        <div class="mb-3">
                            <div class="text-muted small">Họ và tên</div>
                            <div class="fw-semibold">{{ $contract->creator?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Email</div>
                            <div class="fw-semibold">{{ $contract->creator?->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rejectContractModal" tabindex="-1" aria-labelledby="rejectContractModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectContractModalLabel">Từ chối hợp đồng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('owner.contracts.reject', $contract->id) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label">Lý do từ chối</label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="5" class="form-control @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Mô tả chi tiết lý do từ chối tối thiểu 10 ký tự.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
