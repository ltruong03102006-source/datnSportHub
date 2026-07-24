<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử chuyển nhượng - Chủ Sân</title>
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
                <span class="text-slate-800 font-medium">Lịch sử chuyển nhượng</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Quản lý gói</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <!-- Nội dung chính -->
    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Lịch sử Chuyển nhượng</h2>
                <p class="text-slate-500">Xem lại các giao dịch sang nhượng cơ sở của bạn.</p>
            </div>
            <a href="{{ route('owner.web.venues.index') }}" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 transition shadow-sm">
                Quay lại
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="p-4 font-semibold text-slate-600">Mã YC</th>
                        <th class="p-4 font-semibold text-slate-600">Cơ sở</th>
                        <th class="p-4 font-semibold text-slate-600">Vai trò của bạn</th>
                        <th class="p-4 font-semibold text-slate-600">Đối tác</th>
                        <th class="p-4 font-semibold text-slate-600">Ngày tạo</th>
                        <th class="p-4 font-semibold text-slate-600">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-4 text-slate-500">#{{ $transfer->id }}</td>
                            <td class="p-4 font-medium text-slate-800">{{ $transfer->venue->name ?? 'N/A' }}</td>
                            <td class="p-4">
                                @if($transfer->from_owner_id === auth()->id())
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">Bên Bán (Chuyển đi)</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Bên Mua (Nhận về)</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-600">
                                @if($transfer->from_owner_id === auth()->id())
                                    {{ $transfer->toOwner->email ?? 'N/A' }}
                                @else
                                    {{ $transfer->fromOwner->email ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="p-4 text-slate-500">{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-4">
                                @if($transfer->status === 'pending')
                                    <span class="text-amber-600 font-medium">Chờ duyệt</span>
                                @elseif($transfer->status === 'approved')
                                    <span class="text-green-600 font-medium">Đã duyệt</span>
                                @else
                                    <span class="text-red-600 font-medium">Từ chối</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">Bạn chưa có lịch sử chuyển nhượng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $transfers->links() }}
        </div>
    </div>

    @include('owner.partials.notification-script')
</body>
</html>