<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SportHub')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-stone-50 font-sans text-zinc-900 antialiased">
    
    <header x-data="{ open: false }" class="sticky top-0 z-30 border-b border-stone-200/80 bg-white/90 backdrop-blur-md">
        
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 transition hover:opacity-80">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-base font-extrabold text-white shadow-sm">S</span>
                <span class="text-lg font-bold tracking-tight text-zinc-900">SportHub</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-zinc-600 md:flex">
                <a href="{{ route('home') }}" class="text-emerald-700 transition hover:text-emerald-800">Tìm sân</a>
                @auth
                    @if (Auth::user()->role === 'owner')
                        <a href="{{ route('owner.dashboard') }}" class="font-semibold text-zinc-600 transition hover:text-emerald-700">Quản lý sân</a>
                    @else
                        <a href="{{ route('owner.register.page') }}" class="text-zinc-600 transition hover:text-blue-700 font-semibold">Chủ sân</a>
                    @endif
                @else
                    <a href="{{ route('owner.register.page') }}" class="text-zinc-600 transition hover:text-blue-700 font-semibold">Chủ sân</a>
                @endauth
            </nav>

            @guest
            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-600 transition hover:text-emerald-700">Đăng nhập</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow">Đăng ký ngay</a>
            </div>
            @endguest

            @auth
            <div class="relative hidden items-center md:flex" x-data="{ profileOpen: false }">
                <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="flex items-center gap-2.5 rounded-full border border-stone-200 bg-white py-1.5 pl-1.5 pr-3 shadow-sm transition hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="text-sm font-semibold text-zinc-700">{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>

                <div x-show="profileOpen" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 top-full mt-2 w-56 origin-top-right rounded-xl border border-stone-100 bg-white py-2 shadow-lg ring-1 ring-black/5 focus:outline-none">
                    <div class="px-4 py-3 border-b border-stone-100">
                        <p class="text-xs text-zinc-500">Tài khoản của</p>
                        <p class="truncate text-sm font-semibold text-zinc-900">{{ Auth::user()->name }}</p>
                    </div>
                    
                    <a href="{{ route('account.bookings.index') }}" class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-stone-50 hover:text-emerald-700 transition">Lịch sử đặt sân</a>
                    
                    <a href="#" class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-stone-50 hover:text-emerald-700 transition">Thông tin cá nhân</a>
                    <div class="my-1 border-t border-stone-100"></div>
                    <button onclick="handleLogout()" class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50 transition">Đăng xuất</button>
                </div>
            </div>
            @endauth

            <button type="button" @click="open = !open" class="grid h-10 w-10 place-items-center rounded-lg text-zinc-700 transition hover:bg-stone-100 md:hidden">
                <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div x-show="open" x-cloak x-transition class="border-t border-stone-200 bg-white md:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 text-sm font-medium sm:px-6">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2.5 text-emerald-700 bg-emerald-50">Tìm sân</a>
                @auth
                    @if (Auth::user()->role === 'owner')
                        <a href="{{ route('owner.dashboard') }}" class="rounded-lg px-3 py-2.5 font-semibold text-emerald-700 hover:bg-emerald-50">Quản lý sân</a>
                    @else
                        <a href="{{ route('owner.register.page') }}" class="rounded-lg px-3 py-2.5 text-blue-700 hover:bg-blue-50 font-semibold">Đăng ký làm chủ sân</a>
                    @endif
                @else
                    <a href="{{ route('owner.register.page') }}" class="rounded-lg px-3 py-2.5 text-blue-700 hover:bg-blue-50 font-semibold">Đăng ký làm chủ sân</a>
                @endauth
                
                @guest
                <div class="mt-2 border-t border-stone-100 pt-2 flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 text-zinc-600 hover:bg-stone-100">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-3 py-2.5 text-center font-semibold text-white shadow-sm">Đăng ký ngay</a>
                </div>
                @endguest

                @auth
                <div class="mt-2 border-t border-stone-100 pt-2 flex flex-col gap-1">
                    <div class="flex items-center gap-3 px-3 py-3">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-emerald-100 text-base font-bold text-emerald-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        <div>
                            <p class="text-xs text-zinc-500">Xin chào</p>
                            <p class="text-sm font-bold text-zinc-900">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                    <a href="{{ route('account.bookings.index') }}" class="rounded-lg px-3 py-2.5 text-zinc-700 hover:bg-stone-100">Lịch sử đặt sân</a>
                    <button onclick="handleLogout()" class="rounded-lg px-3 py-2.5 text-left font-semibold text-red-600 hover:bg-red-50">Đăng xuất</button>
                </div>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white pb-8 pt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8">
                
                <div class="md:col-span-1">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 transition hover:opacity-80 mb-4">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-700 text-sm font-extrabold text-white">S</span>
                        <span class="text-lg font-bold tracking-tight text-zinc-900">SportHub</span>
                    </a>
                    <p class="text-sm leading-relaxed text-zinc-500">
                        Nền tảng kết nối người chơi với các sân thể thao chất lượng cao. Đặt lịch nhanh chóng, thanh toán tiện lợi, trải nghiệm tuyệt vời.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider mb-4">Khám phá</h3>
                    <ul class="space-y-3 text-sm text-zinc-500">
                        <li><a href="#" class="hover:text-emerald-600 transition">Tìm sân gần đây</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Sân bóng đá</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Sân cầu lông</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Sân tennis</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider mb-4">Đối tác</h3>
                    <ul class="space-y-3 text-sm text-zinc-500">
                        <li><a href="{{ route('owner.register.page') }}" class="hover:text-emerald-600 transition">Đăng ký làm đối tác</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Quản lý sân (Portal)</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Quy định hợp tác</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Trung tâm hỗ trợ</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider mb-4">Hỗ trợ</h3>
                    <ul class="space-y-3 text-sm text-zinc-500">
                        <li><a href="#" class="hover:text-emerald-600 transition">Câu hỏi thường gặp (FAQ)</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Chính sách bảo mật</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition">Điều khoản sử dụng</a></li>
                        <li><a href="#" class="hover:text-emerald-600 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            Hotline: 1900 1234
                        </a></li>
                    </ul>
                </div>

            </div>

            <div class="mt-12 pt-8 border-t border-stone-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-zinc-500">
                    &copy; {{ date('Y') }} SportHub. Bảo lưu mọi quyền.
                </p>
                <div class="flex gap-4 text-zinc-400">
                    <a href="#" class="hover:text-emerald-600 transition">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                    </a>
                    <a href="#" class="hover:text-emerald-600 transition">
                        <span class="sr-only">Instagram</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    @yield('scripts')
    <script>
        async function handleLogout() {
            try {
                await fetch('{{ route('web.logout') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            } catch (e) {}
            
            // Xóa rác trong localStorage
            localStorage.removeItem('sporthub_token');
            localStorage.removeItem('sporthub_user');
            
            // F5 lại trang để server cập nhật giao diện
            window.location.href = '{{ route('home') }}';
        }
    </script>
</body>
</html>
