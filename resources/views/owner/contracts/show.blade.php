<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hợp đồng - {{ $contract->contract_code }} - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body x-data="{ openRejectModal: {{ $errors->has('rejection_reason') ? 'true' : 'false' }} }" class="h-full text-slate-800 antialiased flex flex-col selection:bg-emerald-500 selection:text-white">

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

        <!-- Top Header & Action Controls -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('owner.contracts.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-600 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Danh sách hợp đồng
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                    Chi tiết hợp đồng
                    <span class="text-sm font-mono font-normal text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-lg border border-slate-200">
                        {{ $contract->contract_code }}
                    </span>
                </h1>
            </div>

            <!-- Action Buttons Group -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('owner.contracts.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    Quay lại
                </a>

                <a href="{{ route('owner.contracts.download', $contract) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Tải PDF
                </a>

                @if($contract->status === 'sent')
                    <form action="{{ route('owner.contracts.accept', $contract) }}" method="POST" class="m-0 inline-block">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm shadow-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Đồng ý
                        </button>
                    </form>

                    <button type="button" 
                            @click="openRejectModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Từ chối
                    </button>
                @endif
            </div>
        </div>

        <!-- Alert Notification for Rejection Reason -->
        @if($contract->status === 'rejected' && $contract->rejection_reason)
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-rose-900 flex items-start gap-4 shadow-sm">
                <div class="p-2 bg-rose-100 rounded-xl text-rose-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-rose-900">Hợp đồng đã bị từ chối</h3>
                    <p class="text-sm mt-1 text-rose-700">{{ $contract->rejection_reason }}</p>
                </div>
            </div>
        @endif

        <!-- Main Details Section Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left & Middle Column: Main Contract Info (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Basic Contract Info -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Thông tin chung</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Chi tiết pháp lý và điều khoản cơ bản</p>
                        </div>

                        @php
                            $statusConfig = match($contract->status) {
                                'draft' => ['label' => 'Nháp', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                'sent' => ['label' => 'Chờ ký duyệt', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                'accepted' => ['label' => 'Đã hiệu lực', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                'rejected' => ['label' => 'Đã từ chối', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                'expired' => ['label' => 'Đã hết hạn', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                'terminated' => ['label' => 'Đã chấm dứt', 'class' => 'bg-slate-800 text-white border-slate-800'],
                                default => ['label' => $contract->status, 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border {{ $statusConfig['class'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Mã hợp đồng</span>
                            <div class="text-sm font-bold text-slate-900 mt-1 font-mono">{{ $contract->contract_code }}</div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Mức hoa hồng</span>
                            <div class="text-sm font-bold text-emerald-600 mt-1">{{ number_format($contract->commission_rate, 2) }}%</div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 sm:col-span-2">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Tiêu đề hợp đồng</span>
                            <div class="text-sm font-semibold text-slate-800 mt-1">{{ $contract->title }}</div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Ngày ký</span>
                            <div class="text-sm font-semibold text-slate-800 mt-1">
                                {{ $contract->signed_at ? $contract->signed_at->format('H:i - d/m/Y') : 'Chưa ký' }}
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Thời hạn hiệu lực</span>
                            <div class="text-sm font-semibold text-slate-800 mt-1">
                                {{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '-' }} 
                                <span class="text-slate-400 font-normal">đến</span> 
                                {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Contract Document Content -->
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 mb-2">Nội dung hợp đồng</h3>
                        <div class="p-5 rounded-xl bg-slate-50/80 border border-slate-200/80 text-sm text-slate-700 leading-relaxed font-serif max-h-96 overflow-y-auto whitespace-pre-line select-text">
                            {!! e($contract->content) !!}
                        </div>
                    </div>
                </div>

                <!-- Timeline / Audit Meta -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">Lịch sử ghi nhận</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                            <div class="w-8 h-8 rounded-lg bg-slate-200/60 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Ngày tạo</span>
                                <span class="font-medium text-slate-700">{{ $contract->created_at ? $contract->created_at->format('H:i - d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                            <div class="w-8 h-8 rounded-lg bg-slate-200/60 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Cập nhật gần nhất</span>
                                <span class="font-medium text-slate-700">{{ $contract->updated_at ? $contract->updated_at->format('H:i - d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Admin Contact & Meta Sidebar (1 Col) -->
            <div class="space-y-6">
                
                <!-- Admin Information Card -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-bold text-slate-900">Người đại diện (Admin)</h2>
                        <p class="text-xs text-slate-500">Đại diện phía SportHub khởi tạo hợp đồng</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($contract->creator?->name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $contract->creator?->name ?? 'Không xác định' }}</div>
                                <div class="text-xs text-slate-400">Quản trị viên SportHub</div>
                            </div>
                        </div>

                        <div class="pt-2 space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>{{ $contract->creator?->email ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $contract->creator?->phone ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Note / Help Box -->
                <div class="bg-emerald-50/50 p-5 rounded-2xl border border-emerald-100 text-xs text-slate-600 space-y-2">
                    <div class="font-semibold text-emerald-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Lưu ý pháp lý
                    </div>
                    <p class="leading-relaxed">
                        Hợp đồng điện tử được ký bằng cách nhấn "Đồng ý" có giá trị pháp lý tương đương với hợp đồng bản giấy. Vui lòng đọc kỹ các điều khoản trước khi xác nhận.
                    </p>
                </div>

            </div>

        </div>
    </main>

    <!-- Reject Modal Dialog (Alpine.js) -->
    <div x-show="openRejectModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop Overlay -->
        <div x-show="openRejectModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="openRejectModal = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Content Container -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="openRejectModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                
                <form method="POST" action="{{ route('owner.contracts.reject', $contract->id) }}">
                    @csrf
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Từ chối hợp đồng</h3>
                            <button type="button" @click="openRejectModal = false" class="text-slate-400 hover:text-slate-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="mt-4 space-y-2">
                            <label for="rejection_reason" class="block text-xs font-semibold uppercase text-slate-600">Lý do từ chối <span class="text-rose-500">*</span></label>
                            <textarea id="rejection_reason" 
                                      name="rejection_reason" 
                                      rows="4" 
                                      placeholder="Nhập lý do cụ thể bạn không chấp nhận các điều khoản của hợp đồng này..."
                                      class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition @error('rejection_reason') border-rose-500 ring-2 ring-rose-500/20 @enderror">{{ old('rejection_reason') }}</textarea>
                            
                            @error('rejection_reason')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror

                            <p class="text-xs text-slate-400">Mô tả chi tiết lý do từ chối (tối thiểu 10 ký tự) để quản trị viên có thể điều chỉnh lại hợp đồng.</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100">
                        <button type="button" 
                                @click="openRejectModal = false"
                                class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                            Hủy
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm shadow-rose-200">
                            Xác nhận từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SportHub System. Tất cả các quyền được bảo lưu.
        </div>
    </footer>

</body>
</html>