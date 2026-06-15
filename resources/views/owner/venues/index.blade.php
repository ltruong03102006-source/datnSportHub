<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý điểm sân - Chủ Sân</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: rgba(16, 185, 129, 0.3);
        }
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
                <span class="text-slate-800 font-medium">Quản lý cơ sở</span>
            </div>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch đặt sân</a>
        </div>
    </nav>

    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Cơ sở của bạn</h2>
                <p class="text-slate-500">Quản lý các điểm sân hiện tại hoặc thêm mới cơ sở kinh doanh.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('owner.web.venues.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Thêm cơ sở mới
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success') || request('created') == '1' || request('updated') == '1')
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 flex items-start" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-emerald-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-emerald-800">Thành công</h3>
                    <p class="text-sm text-emerald-700 mt-1">
                        {{ session('success') ?? (request('created') == '1' ? 'Đã tạo điểm sân thành công.' : 'Đã cập nhật thông tin điểm sân thành công.') }}
                    </p>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 flex items-start" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-red-800">Đã xảy ra lỗi</h3>
                    <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
        @endif

        <!-- Venues Grid -->
        @if(isset($venues) && $venues->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-slate-900 mb-1">Chưa có cơ sở nào</h3>
                <p class="text-slate-500 mb-6">Bạn chưa tạo điểm sân nào. Hãy bắt đầu bằng cách thêm cơ sở mới.</p>
                <a href="{{ route('owner.web.venues.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-lg transition-colors">
                    Thêm cơ sở đầu tiên
                </a>
            </div>
        @elseif(isset($venues))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($venues as $venue)
                    <div class="glass-card rounded-2xl overflow-hidden flex flex-col group relative">
                        
                        <!-- Image Container -->
                        <div class="relative h-48 w-full bg-slate-100 overflow-hidden">
                            @if($venue->banner)
                                <!-- Hình ảnh chính -->
                                <img 
                                    src="{{ asset('storage/' . $venue->banner) }}" 
                                    alt="{{ $venue->name }}" 
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >
                                <!-- Fallback nếu ảnh lỗi -->
                                <div class="hidden absolute inset-0 bg-slate-100 flex-col items-center justify-center text-slate-400">
                                    <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-xs font-medium">Lỗi tải ảnh</span>
                                </div>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-gradient-to-br from-slate-50 to-slate-200">
                                    <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <span class="text-xs font-medium">Không có ảnh</span>
                                </div>
                            @endif

                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4">
                                @if($venue->status === 'active')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 backdrop-blur-md">Hoạt động</span>
                                @elseif($venue->status === 'pending')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700 border border-amber-200 backdrop-blur-md">Chờ duyệt</span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200 backdrop-blur-md">{{ ucfirst($venue->status) }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-slate-800 mb-2 line-clamp-1" title="{{ $venue->name }}">{{ $venue->name }}</h3>
                            
                            <div class="flex items-start text-sm text-slate-500 mt-auto mb-4">
                                <svg class="w-4 h-4 mr-1.5 mt-0.5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="line-clamp-2 leading-relaxed" title="{{ $venue->address }}">{{ $venue->address }}</span>
                            </div>

                            <hr class="border-slate-100 my-4 -mx-5">

                            <!-- Actions -->
                            <div class="grid grid-cols-2 gap-2 mt-auto">
                                <a href="{{ route('owner.web.venues.show', $venue->id) }}" class="col-span-2 inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                    Chi tiết & Quản lý sân con
                                </a>
                                
                                <a href="{{ route('owner.web.venues.edit', $venue->id) }}" class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                                    Sửa
                                </a>

                                @if($venue->status === 'active')
                                    <form action="{{ route('owner.web.venues.destroy', $venue->id) }}" method="POST" class="inline-block w-full" onsubmit="return confirm('Tạm ngừng hoạt động cơ sở này? Khách hàng sẽ không thể đặt sân.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-colors">
                                            Tạm ngừng
                                        </button>
                                    </form>
                                @elseif($venue->status === 'inactive')
                                    <form action="{{ route('owner.web.venues.restore', $venue->id) }}" method="POST" class="inline-block w-full" onsubmit="return confirm('Xác nhận mở lại cơ sở này?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-lg transition-colors">
                                            Mở lại
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 bg-red-50 text-red-600 rounded-lg">Lỗi: Không thể lấy dữ liệu cơ sở từ server.</div>
        @endif
    </div>

</body>
</html>
