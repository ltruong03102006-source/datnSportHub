@extends('owner.layoutOwner.app')

@section('title','Dashboard')

@section('content')
<main class="container-fluid max-w-7xl py-4 space-y-4">

    <!-- Page Header & Overview -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 bg-white p-4 rounded-4 border border-light-subtle shadow-sm">
        <div>
            <h1 class="h4 font-weight-bold text-dark mb-1">Hợp đồng hợp tác</h1>
            <p class="small text-muted mb-0">Quản lý danh sách các hợp đồng dịch vụ và chính sách phân chia doanh thu với SportHub.</p>
        </div>
        
        <!-- Quick Action or Badge -->
        <div class="d-flex align-items-center gap-2 align-self-start align-self-md-auto">
            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-normal d-inline-flex align-items-center gap-2">
                <span class="spinner-grow spinner-grow-sm text-success" style="width: 0.5rem; height: 0.5rem;" role="status"></span>
                Hệ thống hoạt động bình thường
            </span>
        </div>
    </div>

    <!-- Quick Stats Cards (Optional overview) -->
    <div class="row g-3">
        <div class="col-12 col-sm-4">
            <div class="bg-white p-4 rounded-4 border border-light-subtle shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <p class="small fw-medium text-muted mb-1">Tổng hợp đồng</p>
                    <p class="h3 font-weight-bold text-dark mb-0">{{ $contracts->total() ?? count($contracts) }}</p>
                </div>
                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 40px; height: 40px;">
                    <svg class="bi" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="bg-white p-4 rounded-4 border border-light-subtle shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <p class="small fw-medium text-muted mb-1">Đang hiệu lực</p>
                    <p class="h3 font-weight-bold text-success mb-0">
                        {{ $contracts->where('status', 'accepted')->count() }}
                    </p>
                </div>
                <div class="rounded-3 bg-success-subtle d-flex align-items-center justify-content-center text-success" style="width: 40px; height: 40px;">
                    <svg class="bi" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="bg-white p-4 rounded-4 border border-light-subtle shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <p class="small fw-medium text-muted mb-1">Chờ phản hồi</p>
                    <p class="h3 font-weight-bold text-warning mb-0">
                        {{ $contracts->where('status', 'sent')->count() }}
                    </p>
                </div>
                <div class="rounded-3 bg-warning-subtle d-flex align-items-center justify-content-center text-warning" style="width: 40px; height: 40px;">
                    <svg class="bi" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card Section -->
    <div class="bg-white rounded-4 border border-light-subtle shadow-sm overflow-hidden">
        
        @if($contracts->isEmpty())
            <!-- Empty State -->
            <div class="py-5 px-3 text-center mx-auto" style="max-width: 400px;">
                <div class="bg-light text-secondary rounded-4 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                    <svg class="bi" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="h5 font-weight-bold text-dark mb-1">Chưa có hợp đồng nào</h3>
                <p class="small text-muted">Hiện tại bạn chưa phát sinh hợp đồng nào với SportHub. Danh sách sẽ xuất hiện tại đây khi có yêu cầu hợp tác mới.</p>
            </div>
        @else
            <!-- Table View -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-secondary small">
                    <thead class="bg-light text-uppercase fw-semibold text-muted border-bottom" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <tr>
                            <th scope="col" class="py-3 px-4" style="width: 60px;">STT</th>
                            <th scope="col" class="py-3 px-4">Mã HĐ</th>
                            <th scope="col" class="py-3 px-4">Tiêu đề</th>
                            <th scope="col" class="py-3 px-4 text-center">Hoa hồng</th>
                            <th scope="col" class="py-3 px-4">Thời hạn</th>
                            <th scope="col" class="py-3 px-4">Trạng thái</th>
                            <th scope="col" class="py-3 px-4">Ngày tạo</th>
                            <th scope="col" class="py-3 px-4 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @foreach($contracts as $index => $contract)
                            <tr>
                                <td class="py-3 px-4 fw-medium text-muted">
                                    {{ method_exists($contracts, 'firstItem') ? $contracts->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="py-3 px-4 fw-semibold text-dark text-nowrap">
                                    <span class="font-monospace bg-light text-dark px-2 py-1 rounded small">
                                        {{ $contract->contract_code }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 fw-medium text-dark text-truncate" style="max-width: 250px;">
                                    {{ $contract->title }}
                                </td>
                                <td class="py-3 px-4 text-center fw-bold text-dark">
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1.5 font-weight-semibold">
                                        {{ number_format($contract->commission_rate, 1) }}%
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-nowrap text-muted small">
                                    <div>Bắt đầu: <span class="fw-medium text-dark">{{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '-' }}</span></div>
                                    <div>Kết thúc: <span class="fw-medium text-dark">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}</span></div>
                                </td>
                                <td class="py-3 px-4 text-nowrap">
                                    @php
                                        $statusConfig = match($contract->status) {
                                            'draft' => ['label' => 'Nháp', 'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
                                            'sent' => ['label' => 'Chờ duyệt', 'class' => 'bg-primary-subtle text-primary border-primary-subtle'],
                                            'accepted' => ['label' => 'Đã ký kết', 'class' => 'bg-success-subtle text-success border-success-subtle'],
                                            'rejected' => ['label' => 'Từ chối', 'class' => 'bg-danger-subtle text-danger border-danger-subtle'],
                                            'expired' => ['label' => 'Hết hạn', 'class' => 'bg-warning-subtle text-warning border-warning-subtle'],
                                            'terminated' => ['label' => 'Đã chấm dứt', 'class' => 'bg-dark text-white border-dark'],
                                            default => ['label' => $contract->status, 'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
                                        };
                                    @endphp
                                    <span class="badge rounded-pill border {{ $statusConfig['class'] }} px-2.5 py-1.5 fw-medium">
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-nowrap text-muted small">
                                    {{ $contract->created_at ? $contract->created_at->format('d/m/Y') : '' }}
                                </td>
                                <td class="py-3 px-4 text-end text-nowrap">
                                    <a href="{{ route('owner.contracts.show', $contract) }}" 
                                       class="btn btn-sm btn-light text-success border-success-subtle d-inline-flex align-items-center gap-1 font-weight-semibold shadow-sm">
                                        <span>Xem chi tiết</span>
                                        <svg class="bi" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            @if(method_exists($contracts, 'hasPages') && $contracts->hasPages())
                <div class="p-3 border-top bg-light-subtle">
                    {{ $contracts->links() }}
                </div>
            @endif
        @endif

    </div>
</main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    
</script>
@endpush
{{-- <!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp đồng của tôi - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased flex flex-col selection:bg-emerald-500 selection:text-white">

    <nav class="sticky top-0 z-50 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-4 shadow-sm backdrop-blur-md">
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-2xl font-black text-emerald-700">SportHub</a>
            <div class="hidden md:flex items-center gap-2 border-l border-slate-200 pl-5 text-sm text-slate-500">
                <a href="{{ route('owner.dashboard') }}" class="font-semibold text-slate-600 hover:text-emerald-600 transition-colors">Tổng quan</a>
                <span>/</span>
                <span class="font-bold text-slate-800">Hợp đồng</span>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors py-2">Quản lý gói</a>
            <a href="{{ route('owner.contracts.index') }}" class="text-sm font-semibold text-emerald-700 py-2">Hợp đồng</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Page Header & Overview -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Hợp đồng hợp tác</h1>
                <p class="text-sm text-slate-500 mt-1">Quản lý danh sách các hợp đồng dịch vụ và chính sách phân chia doanh thu với SportHub.</p>
            </div>
            
            <!-- Quick Action or Badge -->
            <div class="flex items-center gap-2 self-start md:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Hệ thống hoạt động bình thường
                </span>
            </div>
        </div>

        <!-- Quick Stats Cards (Optional overview) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Tổng hợp đồng</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $contracts->total() ?? count($contracts) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Đang hiệu lực</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">
                        {{ $contracts->where('status', 'accepted')->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Chờ phản hồi</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">
                        {{ $contracts->where('status', 'sent')->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Main Card Section -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            @if($contracts->isEmpty())
                <!-- Empty State -->
                <div class="py-16 px-4 text-center max-w-md mx-auto">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">Chưa có hợp đồng nào</h3>
                    <p class="text-sm text-slate-500">Hiện tại bạn chưa phát sinh hợp đồng nào với SportHub. Danh sách sẽ xuất hiện tại đây khi có yêu cầu hợp tác mới.</p>
                </div>
            @else
                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16">STT</th>
                                <th scope="col" class="py-4 px-6">Mã HĐ</th>
                                <th scope="col" class="py-4 px-6">Tiêu đề</th>
                                <th scope="col" class="py-4 px-6 text-center">Hoa hồng</th>
                                <th scope="col" class="py-4 px-6">Thời hạn</th>
                                <th scope="col" class="py-4 px-6">Trạng thái</th>
                                <th scope="col" class="py-4 px-6">Ngày tạo</th>
                                <th scope="col" class="py-4 px-6 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($contracts as $index => $contract)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 px-6 font-medium text-slate-400">
                                        {{ method_exists($contracts, 'firstItem') ? $contracts->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-900 whitespace-nowrap">
                                        <span class="font-mono bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs">
                                            {{ $contract->contract_code }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-slate-800 max-w-xs truncate">
                                        {{ $contract->title }}
                                    </td>
                                    <td class="py-4 px-6 text-center font-bold text-slate-900">
                                        <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                            {{ number_format($contract->commission_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-500">
                                        <div>Bắt đầu: <span class="font-medium text-slate-700">{{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '-' }}</span></div>
                                        <div>Kết thúc: <span class="font-medium text-slate-700">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}</span></div>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @php
                                            $statusConfig = match($contract->status) {
                                                'draft' => ['label' => 'Nháp', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                                'sent' => ['label' => 'Chờ duyệt', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                                'accepted' => ['label' => 'Đã ký kết', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                                'rejected' => ['label' => 'Từ chối', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                                'expired' => ['label' => 'Hết hạn', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                                'terminated' => ['label' => 'Đã chấm dứt', 'class' => 'bg-slate-800 text-white border-slate-800'],
                                                default => ['label' => $contract->status, 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig['class'] }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-500">
                                        {{ $contract->created_at ? $contract->created_at->format('d/m/Y') : '' }}
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <a href="{{ route('owner.contracts.show', $contract) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/60 rounded-lg transition-all shadow-sm">
                                            <span>Xem chi tiết</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                @if(method_exists($contracts, 'hasPages') && $contracts->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $contracts->links() }}
                    </div>
                @endif
            @endif

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SportHub System. Tất cả các quyền được bảo lưu.
        </div>
    </footer>

</body>
</html> --}}
