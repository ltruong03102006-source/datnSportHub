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
    
    @if(in_array($contract->status, ['draft', 'rejected'], true))
        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-primary">Chỉnh sửa</a>
        <form action="{{ route('admin.contracts.send', $contract) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn muốn gửi hợp đồng này?');">Gửi hợp đồng</button>
        </form>
    @endif

    <!-- THÊM NÚT CHẤM DỨT Ở ĐÂY -->
    <!-- THÊM NÚT KÍCH HOẠT MODAL CHẤM DỨT -->
    @if($contract->status === 'accepted')
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#terminateModal">
            Chấm dứt hợp đồng
        </button>

        <!-- MODAL NHẬP LÝ DO CHẤM DỨT -->
        <div class="modal fade" id="terminateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('admin.contracts.terminate', $contract) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-danger text-white border-0">
                            <h5 class="modal-title fw-bold">Chấm dứt hợp đồng trước thời hạn</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light text-start">
                            <div class="alert alert-warning small mb-3">
                                <strong>Cảnh báo:</strong> Hành động này sẽ vô hiệu hóa cơ sở và không thể hoàn tác!
                            </div>
                            
                            <label class="form-label fw-semibold text-dark">Lý do chấm dứt <span class="text-danger">*</span></label>

<!-- Bỏ required và minlength đi, thêm @error('reason') is-invalid @enderror -->
<textarea name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Nhập lý do cụ thể (tối thiểu 10 ký tự)...">{{ old('reason') }}</textarea>

@error('reason')
    <div class="invalid-feedback d-block small mt-1 fw-bold">{{ $message }}</div>
@enderror
                            @error('reason')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-danger px-4">Xác nhận chấm dứt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
                                $statusLabels = [
                                    'draft' => 'Bản nháp',
                                    'sent' => 'Đã gửi',
                                    'accepted' => 'Đã chấp nhận',
                                    'rejected' => 'Đã từ chối',
                                    'expired' => 'Hết hạn',
                                    'terminated' => 'Chấm dứt',
                                ];
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $statusLabels[$contract->status] ?? $contract->status }}</span>
                        </dd>

                        <dt class="col-sm-4">Hoa hồng (%)</dt>
                        <dd class="col-sm-8">{{ number_format($contract->commission_rate, 2) }}</dd>

                        <dt class="col-sm-4">Cơ sở liên kết</dt>
                        <dd class="col-sm-8">{{ $contract->venue?->name ?? 'Không gắn cơ sở cụ thể' }}</dd>

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

                        <dt class="col-sm-4">Thời gian gửi</dt>
                        <dd class="col-sm-8">{{ $contract->sent_at?->format('Y-m-d H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4">Thời gian từ chối</dt>
                        <dd class="col-sm-8">{{ $contract->rejected_at?->format('Y-m-d H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4">Thời gian hết hạn</dt>
                        <dd class="col-sm-8">{{ $contract->expired_at?->format('Y-m-d H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4">Thời gian chấm dứt</dt>
                        <dd class="col-sm-8">{{ $contract->terminated_at?->format('Y-m-d H:i') ?? '-' }}</dd>

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
                    <p class="mb-0"><strong>Số điện thoại:</strong> {{ $contract->venue?->phone ?? $contract->owner?->phone ?? 'Chưa cập nhật' }}</p>
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
<div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Bản xem trước hợp đồng</h5>
                    <span class="badge bg-secondary">Bản mềm</span>
                </div>
                <div class="card-body bg-light">
                    <!-- Tạo một khung mô phỏng tờ giấy A4 -->
                    <div class="bg-white p-5 mx-auto border shadow-sm rounded" style="max-width: 800px; min-height: 1000px;">
                        {!! $contract->content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    @if($errors->has('reason'))
        <script>
            // Chờ giao diện tải xong thì tự động bật lại Modal Chấm dứt lên
            document.addEventListener("DOMContentLoaded", function() {
                if(typeof bootstrap !== 'undefined') {
                    var terminateModal = new bootstrap.Modal(document.getElementById('terminateModal'));
                    terminateModal.show();
                }
            });
        </script>
    @endif
@endpush
@endsection
