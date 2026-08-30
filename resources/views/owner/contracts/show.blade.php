@extends('owner.layoutOwner.app')

@section('title','Dashboard')

@section('content')
<style>
    .contract-preview .center,
    .contract-document .center {
        text-align: center !important;
    }
    .contract-preview .bold,
    .contract-document .bold {
        font-weight: bold !important;
    }
</style>
<!-- Kiểm tra nếu có lỗi của trường rejection_reason thì tự động mở Modal -->
<div x-data="{ openRejectModal: {{ $errors->has('rejection_reason') ? 'true' : 'false' }} }">
<main class="container-fluid max-w-7xl py-4 space-y-4">

    <!-- Top Header & Action Controls -->
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 bg-white p-4 rounded-4 border border-light-subtle shadow-sm">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('owner.contracts.index') }}" class="small fw-semibold text-secondary text-decoration-none hover-emerald d-inline-flex align-items-center gap-1">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Danh sách hợp đồng
                </a>
            </div>
            <h1 class="h4 font-weight-bold text-dark mb-0 d-flex align-items-center gap-2">
                Chi tiết hợp đồng
                <span class="small font-monospace fw-normal text-secondary bg-light px-2 py-1 rounded-3 border">
                    {{ $contract->contract_code }}
                </span>
            </h1>
        </div>

        <!-- Action Buttons Group -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('owner.contracts.index') }}" 
               class="btn btn-sm btn-white border-secondary-subtle text-dark d-inline-flex align-items-center gap-2 shadow-sm fw-medium">
                <svg width="16" height="16" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                Quay lại
            </a>

            <a href="{{ route('owner.contracts.download', $contract) }}" 
               class="btn btn-sm btn-light text-dark d-inline-flex align-items-center gap-2 shadow-sm fw-medium">
                <svg width="16" height="16" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Tải PDF
            </a>

            @if($contract->status === 'sent')
                <form action="{{ route('owner.contracts.accept', $contract) }}" method="POST" class="m-0 d-inline-block">
                    @csrf
                    <button type="submit" 
                            class="btn btn-sm btn-success d-inline-flex align-items-center gap-2 shadow-sm fw-semibold px-3">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Đồng ý
                    </button>
                </form>

                <button type="button" 
                        @click="openRejectModal = true"
                        class="btn btn-sm btn-danger-subtle text-danger border-danger-subtle d-inline-flex align-items-center gap-2 shadow-sm fw-semibold">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Từ chối
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Notification for Rejection Reason -->
    @if($contract->status === 'rejected' && $contract->rejection_reason)
        <div class="alert alert-danger bg-danger-subtle border-danger-subtle rounded-4 p-4 text-danger d-flex align-items-start gap-3 shadow-sm mb-0">
            <div class="p-2 bg-danger text-white rounded-3 flex-shrink-0 d-flex align-items-center justify-content-center">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="fw-bold small mb-1">Hợp đồng đã bị từ chối</h3>
                <p class="small mb-0 text-danger-emphasis">{{ $contract->rejection_reason }}</p>
            </div>
        </div>
    @endif

    <!-- THÊM ĐOẠN NÀY VÀO: Thông báo khi bị Chấm dứt hợp đồng -->
    @if($contract->status === 'terminated' && $contract->note)
        <div class="alert alert-dark bg-dark text-white rounded-4 p-4 d-flex align-items-start gap-3 shadow-sm mb-4 mt-4">
            <div class="p-2 bg-secondary text-white rounded-3 flex-shrink-0 d-flex align-items-center justify-content-center">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <h3 class="fw-bold small mb-1">Hợp đồng đã bị chấm dứt trước thời hạn</h3>
                <p class="small mb-2 text-light">Quản trị viên SportHub đã thực hiện chấm dứt hợp đồng này. Cơ sở liên kết hiện đang bị tạm khóa hoặc gỡ khỏi hệ thống.</p>
                <div class="bg-secondary bg-opacity-25 p-3 rounded-3 border border-secondary mt-2">
                    <p class="small mb-0 text-white font-monospace" style="white-space: pre-line;">{{ $contract->note }}</p>
                </div>
            </div>
        </div>
    @endif
    <!-- KẾT THÚC ĐOẠN THÊM -->

    <!-- Main Details Section Grid -->
    <div class="row g-4">

    <!-- Main Details Section Grid -->
    <div class="row g-4">
        
        <!-- Left & Middle Column: Main Contract Info (2 Cols) -->
        <div class="col-12 col-lg-8 space-y-4">
            
            <!-- Basic Contract Info -->
            <div class="bg-white p-4 rounded-4 border border-light-subtle shadow-sm space-y-4">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
                    <div>
                        <h2 class="h6 font-weight-bold text-dark mb-0">Thông tin chung</h2>
                        <p class="small text-muted mb-0">Chi tiết pháp lý và điều khoản cơ bản</p>
                    </div>

                    @php
                        $statusConfig = match($contract->status) {
                            'draft' => ['label' => 'Nháp', 'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
                            'sent' => ['label' => 'Chờ ký duyệt', 'class' => 'bg-primary-subtle text-primary border-primary-subtle'],
                            'accepted' => ['label' => 'Đã hiệu lực', 'class' => 'bg-success-subtle text-success border-success-subtle'],
                            'rejected' => ['label' => 'Đã từ chối', 'class' => 'bg-danger-subtle text-danger border-danger-subtle'],
                            'expired' => ['label' => 'Đã hết hạn', 'class' => 'bg-warning-subtle text-warning border-warning-subtle'],
                            'terminated' => ['label' => 'Đã chấm dứt', 'class' => 'bg-dark text-white border-dark'],
                            default => ['label' => $contract->status, 'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
                        };
                    @endphp
                    <span class="badge rounded-pill border {{ $statusConfig['class'] }} px-3 py-2 fw-semibold">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>

                <!-- Metrics Grid -->
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="small fw-medium text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.7rem;">Mã hợp đồng</span>
                            <div class="fw-bold text-dark font-monospace small">{{ $contract->contract_code }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="small fw-medium text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.7rem;">Mức hoa hồng</span>
                            <div class="fw-bold text-success small">{{ number_format($contract->commission_rate, 2) }}%</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="small fw-medium text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.7rem;">Tiêu đề hợp đồng</span>
                            <div class="fw-semibold text-dark small">{{ $contract->title }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="small fw-medium text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.7rem;">Ngày ký</span>
                            <div class="fw-semibold text-dark small">
                                {{ $contract->signed_at ? $contract->signed_at->format('H:i - d/m/Y') : 'Chưa ký' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="small fw-medium text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.7rem;">Thời hạn hiệu lực</span>
                            <div class="fw-semibold text-dark small">
                                {{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '-' }} 
                                <span class="text-muted fw-normal">đến</span> 
                                {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="small fw-medium text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.7rem;">Cơ sở liên kết</span>
                            <div class="fw-semibold text-dark small">{{ $contract->venue?->name ?? 'Chưa gắn cơ sở cụ thể' }}</div>
                            <div class="small text-muted mt-1">Khi bạn đồng ý hợp đồng, hệ thống sẽ kích hoạt cơ sở này để hoạt động trên nền tảng.</div>
                        </div>
                    </div>
                </div>

                <!-- Contract Document Content -->
                <div>
                    <h3 class="small font-weight-bold text-dark mb-2">Nội dung hợp đồng</h3>
                    <div class="p-4 rounded-3 bg-light border text-dark lh-lg font-serif small user-select-text contract-preview" style="max-height: 24rem; overflow-y: auto;">
                        {!! $contract->content !!}
                    </div>
                </div>
            </div>

            <!-- Timeline / Audit Meta -->
            <div class="bg-white p-4 rounded-4 border border-light-subtle shadow-sm">
                <h3 class="small font-weight-bold text-dark mb-3">Lịch sử ghi nhận</h3>
                <div class="row g-3 small">
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light">
                            <div class="rounded-3 bg-secondary-subtle d-flex align-items-center justify-content-center text-secondary" style="width: 32px; height: 32px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Ngày tạo</span>
                                <span class="fw-medium text-dark">{{ $contract->created_at ? $contract->created_at->format('H:i - d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light">
                            <div class="rounded-3 bg-secondary-subtle d-flex align-items-center justify-content-center text-secondary" style="width: 32px; height: 32px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Cập nhật gần nhất</span>
                                <span class="fw-medium text-dark">{{ $contract->updated_at ? $contract->updated_at->format('H:i - d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Admin Contact & Meta Sidebar (1 Col) -->
        <div class="col-12 col-lg-4 space-y-4">
            
            <!-- Admin Information Card -->
            <div class="bg-white p-4 rounded-4 border border-light-subtle shadow-sm space-y-3">
                <div class="border-bottom pb-2">
                    <h2 class="h6 font-weight-bold text-dark mb-0">Người đại diện (Admin)</h2>
                    <p class="small text-muted mb-0">Đại diện phía SportHub khởi tạo hợp đồng</p>
                </div>

                <div class="space-y-3 pt-1">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold small" style="width: 40px; height: 40px;">
                            {{ strtoupper(substr($contract->creator?->name ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <div class="small fw-semibold text-dark">{{ $contract->creator?->name ?? 'Không xác định' }}</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Quản trị viên SportHub</div>
                        </div>
                    </div>

                    <div class="pt-2 border-top space-y-2 small">
                        <div class="d-flex align-items-center gap-2 text-secondary">
                            <svg width="16" height="16" class="text-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $contract->creator?->email ?? '-' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-secondary">
                            <svg width="16" height="16" class="text-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $contract->creator?->phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Note / Help Box -->
            <div class="bg-success-subtle p-4 rounded-4 border border-success-subtle text-secondary small space-y-2">
                <div class="fw-semibold text-success d-flex align-items-center gap-2">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Lưu ý pháp lý
                </div>
                <p class="mb-0 lh-base" style="font-size: 0.8rem;">
                    Hợp đồng điện tử được ký bằng cách nhấn "Đồng ý" có giá trị pháp lý tương đương với hợp đồng bản giấy. Vui lòng đọc kỹ các điều khoản trước khi xác nhận.
                </p>
            </div>

        </div>

    </div>
</main>

<!-- Reject Modal Dialog (Alpine.js) -->
<div x-show="openRejectModal" 
     x-cloak
     class="position-fixed top-0 start-0 w-100 h-100 z-3 overflow-y-auto"
     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);"
     role="dialog" aria-modal="true">

    <div class="d-flex min-vh-100 align-items-center justify-content-center p-3">
        <div x-show="openRejectModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-4 shadow-lg w-100 border overflow-hidden"
             style="max-width: 500px;">
            
            <form method="POST" action="{{ route('owner.contracts.reject', $contract->id) }}">
                @csrf
                
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom">
                        <h3 class="h6 font-weight-bold text-dark mb-0">Từ chối hợp đồng</h3>
                        <button type="button" @click="openRejectModal = false" class="btn-close shadow-none" aria-label="Close"></button>
                    </div>

                    <div class="mt-3 space-y-2">
                        <label for="rejection_reason" class="form-label small fw-semibold text-uppercase text-secondary mb-1" style="font-size: 0.75rem;">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="Nhập lý do cụ thể bạn không chấp nhận các điều khoản của hợp đồng này..."
                                  class="form-control form-control-sm rounded-3 @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>
                        
                        @error('rejection_reason')
                            <div class="invalid-feedback d-block small font-weight-medium">{{ $message }}</div>
                        @enderror

                        <p class="small text-muted mb-0" style="font-size: 0.75rem;">Mô tả chi tiết lý do từ chối (tối thiểu 10 ký tự) để quản trị viên có thể điều chỉnh lại hợp đồng.</p>
                    </div>
                </div>

                <div class="bg-light px-4 py-3 d-flex align-items-center justify-content-end gap-2 border-top">
                    <button type="button" 
                            @click="openRejectModal = false"
                            class="btn btn-sm btn-link text-secondary text-decoration-none fw-semibold">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="btn btn-sm btn-danger px-3 fw-semibold rounded-3 shadow-sm">
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    
</script>
@endpush