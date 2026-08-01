<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Hợp đồng chuyển nhượng - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                SportHub
            </h1>
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500 ml-4 border-l border-slate-200 pl-4">
                <a href="{{ route('owner.dashboard') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('owner.web.venues.index') }}" class="hover:text-emerald-600 transition-colors">Quản lý cơ sở</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Quản lý hợp đồng chuyển nhượng</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Danh sách sân</a>
            <a href="{{ route('owner.web.venues.transfer.general_create') }}" class="text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors border border-amber-200">Tạo hợp đồng mới</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Header Block -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Quản lý hợp đồng chuyển nhượng</h2>
                <p class="text-slate-500 text-sm mt-1">Danh sách và trạng thái toàn bộ hợp đồng chuyển nhượng cơ sở thể thao</p>
            </div>
            <a href="{{ route('owner.web.venues.transfer.general_create') }}" 
               class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tạo hợp đồng mới
            </a>
        </div>

        <!-- BỘ LỌC VÀ TÌM KIẾM (FILTER BLOCK) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-6 space-y-6">
            <form action="{{ route('owner.web.venues.transfers.history') }}" method="GET" id="filterForm">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                    
                    <!-- KHOẢNG TÌM THEO (Checkboxes / Radios + Input) -->
                    <div class="lg:col-span-7 space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Tìm theo</label>
                        <div class="flex flex-wrap items-center gap-4 text-sm mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                                <input type="radio" name="search_type" value="all" {{ request('search_type', 'all') == 'all' ? 'checked' : '' }}
                                       class="text-emerald-600 focus:ring-emerald-500 rounded-full border-slate-300">
                                <span>Tất cả</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                                <input type="radio" name="search_type" value="code" {{ request('search_type') == 'code' ? 'checked' : '' }}
                                       class="text-emerald-600 focus:ring-emerald-500 rounded-full border-slate-300">
                                <span>Mã hợp đồng</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                                <input type="radio" name="search_type" value="venue" {{ request('search_type') == 'venue' ? 'checked' : '' }}
                                       class="text-emerald-600 focus:ring-emerald-500 rounded-full border-slate-300">
                                <span>Tên cơ sở</span>
                            </label>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Nhập mã hợp đồng (VD: HDCN-1) hoặc tên cơ sở..."
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition">
                                Tìm kiếm
                            </button>
                            @if(request()->hasAny(['search', 'status', 'search_type']))
                                <a href="{{ route('owner.web.venues.transfers.history') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition flex items-center justify-center">
                                    Đặt lại
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- BỘ LỌC TRẠNG THÁI (Tất cả, Chờ xác nhận, Chờ ký, Đã hoàn thành, Đã hủy) -->
                    <div class="lg:col-span-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Trạng thái</label>
                        <div class="flex flex-wrap gap-1.5 p-1 bg-slate-100/80 rounded-xl border border-slate-200">
                            @php
                                $currStatus = request('status', 'all');
                            @endphp
                            <a href="{{ route('owner.web.venues.transfers.history', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                               class="flex-1 text-center py-2 px-2.5 text-xs font-bold rounded-lg transition-all {{ $currStatus == 'all' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Tất cả
                            </a>
                            <a href="{{ route('owner.web.venues.transfers.history', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                               class="flex-1 text-center py-2 px-2.5 text-xs font-bold rounded-lg transition-all {{ $currStatus == 'pending' ? 'bg-white text-amber-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Chờ xác nhận
                            </a>
                            <a href="{{ route('owner.web.venues.transfers.history', array_merge(request()->except('status'), ['status' => 'pending_admin'])) }}"
                               class="flex-1 text-center py-2 px-2.5 text-xs font-bold rounded-lg transition-all {{ $currStatus == 'pending_admin' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Chờ ký
                            </a>
                            <a href="{{ route('owner.web.venues.transfers.history', array_merge(request()->except('status'), ['status' => 'approved'])) }}"
                               class="flex-1 text-center py-2 px-2.5 text-xs font-bold rounded-lg transition-all {{ $currStatus == 'approved' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Đã hoàn thành
                            </a>
                            <a href="{{ route('owner.web.venues.transfers.history', array_merge(request()->except('status'), ['status' => 'rejected'])) }}"
                               class="flex-1 text-center py-2 px-2.5 text-xs font-bold rounded-lg transition-all {{ $currStatus == 'rejected' ? 'bg-white text-red-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Đã hủy
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH HỢP ĐỒNG -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-bold">
                            <th class="py-4 px-5">Mã HĐ</th>
                            <th class="py-4 px-5">Cơ sở</th>
                            <th class="py-4 px-5">Bên chuyển</th>
                            <th class="py-4 px-5">Bên nhận</th>
                            <th class="py-4 px-5 text-right">Giá</th>
                            <th class="py-4 px-5 text-center">Trạng thái</th>
                            <th class="py-4 px-5">Ngày tạo</th>
                            <th class="py-4 px-5 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($transfers as $transfer)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Mã HĐ -->
                                <td class="py-4 px-5 font-bold text-slate-800 whitespace-nowrap">
                                    HDCN-#{{ $transfer->id }}
                                </td>

                                <!-- Cơ sở -->
                                <td class="py-4 px-5 font-semibold text-slate-900">
                                    {{ $transfer->venue->name ?? 'N/A' }}
                                </td>

                                <!-- Bên chuyển (Bên A) -->
                                <td class="py-4 px-5">
                                    <div class="font-medium text-slate-800">
                                        {{ $transfer->fromOwner->name ?? $transfer->fromOwner->full_name ?? 'Bên chuyển' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $transfer->fromOwner->email ?? '' }}
                                    </div>
                                </td>

                                <!-- Bên nhận (Bên B) -->
                                <td class="py-4 px-5">
                                    <div class="font-medium text-slate-800">
                                        {{ $transfer->toOwner->name ?? $transfer->toOwner->full_name ?? 'Bên nhận' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $transfer->toOwner->email ?? '' }}
                                    </div>
                                </td>

                                <!-- Giá -->
                                <td class="py-4 px-5 text-right font-bold text-emerald-600 whitespace-nowrap">
                                    {{ $transfer->price ? number_format($transfer->price, 0, ',', '.') . ' VNĐ' : 'Chưa nhập' }}
                                </td>

                                <!-- Trạng thái -->
                                <td class="py-4 px-5 text-center whitespace-nowrap">
                                    @if($transfer->status === 'pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Chờ xác nhận
                                        </span>
                                    @elseif($transfer->status === 'pending_admin')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Chờ ký
                                        </span>
                                    @elseif($transfer->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Đã hoàn thành
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                            Đã hủy
                                        </span>
                                    @endif
                                </td>

                                <!-- Ngày tạo -->
                                <td class="py-4 px-5 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $transfer->created_at ? $transfer->created_at->format('d/m/Y H:i') : '' }}
                                </td>

                                <!-- Thao tác(xem) -->
                                <td class="py-4 px-5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('owner.web.venues.transfers.show', $transfer->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 text-xs font-bold rounded-lg transition-colors border border-slate-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Xem
                                        </a>

                                        {{-- Nút GỬIL: chỉ hiện với Bên A khi status=pending và chưa gửi --}}
                                        @if($transfer->from_owner_id === auth()->id() && $transfer->status === 'pending' && !$transfer->notified_at)
                                            <form action="{{ route('owner.web.venues.transfers.send', $transfer->id) }}" 
                                                  method="POST" 
                                                  id="send-form-{{ $transfer->id }}"
                                                  class="inline-block m-0">
                                                @csrf
                                                <button type="button"
                                                        onclick="confirmSend({{ $transfer->id }}, '{{ addslashes($transfer->venue->name ?? 'cơ sở') }}', '{{ addslashes($transfer->toOwner->email ?? '') }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                    Gửi
                                                </button>
                                            </form>
                                        @elseif($transfer->from_owner_id === auth()->id() && $transfer->notified_at && $transfer->status === 'pending')
                                            <span class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200" title="Đã gửi lúc {{ $transfer->notified_at->format('d/m/Y H:i') }}">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Đã gửi
                                            </span>
                                        @endif

                                        @if($transfer->to_owner_id === auth()->id() && $transfer->status === 'pending')
                                            <a href="{{ route('owner.web.venues.transfers.accept', $transfer->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                                                Xác nhận ngay
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 px-4 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="font-medium text-slate-600">Không tìm thấy hợp đồng chuyển nhượng nào phù hợp.</p>
                                        <p class="text-xs text-slate-400 mt-1">Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc trạng thái.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PHÂN TRANG -->
            @if($transfers->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('owner.partials.notification-script')

    <!-- MODAL XÁC NHẬN GỬI HỢP ĐỒNG -->
    <div id="sendConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md mx-4 p-8 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">Xác nhận gửi hợp đồng</h3>
            <p class="text-sm text-slate-600 mb-1">Bạn có chắc chắn muốn gửi hợp đồng chuyển nhượng</p>
            <p id="modalVenueName" class="text-base font-bold text-emerald-700 mb-1"></p>
            <p class="text-sm text-slate-600 mb-5">đến bên nhận (<span id="modalReceiverEmail" class="font-semibold text-slate-800"></span>)?<br>
                <span class="text-xs text-slate-400 mt-1 inline-block">Sau khi gửi, nút này sẽ không hiển thị lại.</span>
            </p>
            <div class="flex justify-center gap-3">
                <button type="button" onclick="closeSendModal()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirmSendBtn" onclick="submitSendForm()"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Xác nhận gửi
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentSendFormId = null;

        function confirmSend(transferId, venueName, receiverEmail) {
            currentSendFormId = 'send-form-' + transferId;
            document.getElementById('modalVenueName').textContent = '"' + venueName + '"';
            document.getElementById('modalReceiverEmail').textContent = receiverEmail;
            document.getElementById('sendConfirmModal').classList.remove('hidden');
            document.getElementById('sendConfirmModal').classList.add('flex');
        }

        function closeSendModal() {
            document.getElementById('sendConfirmModal').classList.add('hidden');
            document.getElementById('sendConfirmModal').classList.remove('flex');
            currentSendFormId = null;
        }

        function submitSendForm() {
            if (currentSendFormId) {
                const btn = document.getElementById('confirmSendBtn');
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang gửi...';
                document.getElementById(currentSendFormId).submit();
            }
        }

        // Đóng modal khi bấm ngoài vùng
        document.getElementById('sendConfirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeSendModal();
        });
    </script>
</body>
</html>