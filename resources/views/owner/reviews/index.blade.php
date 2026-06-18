<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đánh giá - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">SportHub</h1>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Quản lý cơ sở</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch đặt</a>
            <a href="{{ route('owner.web.reviews.index') }}" class="text-sm font-medium text-emerald-600 border-b-2 border-emerald-600 py-2">Đánh giá</a>
        </div>
    </nav>

    <div class="flex-1 p-6 lg:p-10 max-w-5xl mx-auto w-full">
        <!-- Đã bổ sung Dropdown bộ lọc -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Đánh giá từ khách hàng</h2>
                <p class="text-slate-500">Lắng nghe và phản hồi trải nghiệm của khách để nâng cao chất lượng dịch vụ.</p>
            </div>
            
            <form action="{{ route('owner.web.reviews.index') }}" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
                <label for="venue_id" class="text-sm font-medium text-slate-600 whitespace-nowrap pl-2">Lọc theo cơ sở:</label>
                <select name="venue_id" id="venue_id" class="border-transparent bg-slate-50 rounded-lg text-sm py-2 px-3 outline-none focus:border-emerald-500 focus:ring-emerald-500 cursor-pointer" onchange="this.form.submit()">
                    <option value="">-- Tất cả cơ sở --</option>
                    @foreach($venues as $v)
                        <option value="{{ $v->id }}" {{ request('venue_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6">
            @forelse($reviews as $review)
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-lg font-bold text-slate-600">
                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">{{ $review->user->name }}</h4>
                                <p class="text-xs text-slate-500">{{ $review->court->venue->name }} • {{ $review->court->name }} • {{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex text-amber-400">
                            @for($i = 0; $i < $review->rating; $i++)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                    </div>
                    
                    <p class="text-sm text-slate-700 mb-4">{{ $review->content ?? 'Khách hàng không để lại nhận xét.' }}</p>

                    @if($review->owner_reply)
                        <div class="bg-emerald-50/50 border border-emerald-100 p-4 rounded-xl">
                            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-1">Phản hồi của bạn</p>
                            <p class="text-sm text-emerald-700">{{ $review->owner_reply }}</p>
                        </div>
                    @else
                        <form action="{{ route('owner.web.reviews.reply', $review->id) }}" method="POST" class="mt-4 flex gap-2">
                            @csrf
                            <input type="text" name="owner_reply" required placeholder="Viết phản hồi cảm ơn hoặc xin lỗi khách..." class="flex-1 rounded-lg border-slate-300 text-sm py-2 px-3 border outline-none focus:border-emerald-500 focus:ring-emerald-500">
                            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors">Gửi</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="text-center py-12 text-slate-500">Sân của bạn chưa có đánh giá nào.</div>
            @endforelse
            
            {{ $reviews->links() }}
        </div>
    </div>
</body>
</html>