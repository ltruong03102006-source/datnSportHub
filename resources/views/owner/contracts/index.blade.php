<!DOCTYPE html>
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

    <!-- Header Navigation -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-2 text-xl font-bold text-slate-900 tracking-tight">
                        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-600 text-white font-black shadow-md shadow-emerald-200">S</span>
                        <span>Sport<span class="text-emerald-600">Hub</span></span>
                    </a>
                    
                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('owner.dashboard') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 rounded-lg hover:text-slate-900 hover:bg-slate-100 transition">Dashboard</a>
                        <a href="{{ route('owner.venues.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 rounded-lg hover:text-slate-900 hover:bg-slate-100 transition">Cơ sở</a>
                        <a href="{{ route('owner.contracts.index') }}" class="px-3.5 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg transition">Hợp đồng</a>
                    </nav>
                </div>

                <!-- User Profile / Right Area -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tài khoản</span>
                        <span class="text-sm font-medium text-slate-700">Chủ sân</span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300">
                        {{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}
                    </div>
                </div>
            </div>
        </div>
    </header>

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
</html>