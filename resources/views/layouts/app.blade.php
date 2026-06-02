<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SportHub')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-zinc-900 antialiased">
    <header x-data="{ open: false }" class="sticky top-0 z-30 border-b border-stone-200/80 bg-white/80 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-700 text-base font-extrabold text-white">S</span>
                <span class="text-lg font-bold tracking-tight">SportHub</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-zinc-600 md:flex">
                <a href="{{ route('home') }}" class="text-emerald-700">Trang chủ</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-600 transition hover:text-emerald-700">Đăng nhập</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Đăng ký</a>
            </div>

            <button
                type="button"
                @click="open = !open"
                :aria-expanded="open"
                aria-label="Mở menu"
                class="grid h-10 w-10 place-items-center rounded-lg text-zinc-700 transition hover:bg-stone-100 md:hidden"
            >
                <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            class="border-t border-stone-200 bg-white md:hidden"
        >
            <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 text-sm font-medium sm:px-6">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2.5 text-emerald-700 transition hover:bg-stone-100">Trang chủ</a>
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 text-zinc-600 transition hover:bg-stone-100">Đăng nhập</a>
                <a href="{{ route('register') }}" class="mt-1 rounded-lg bg-emerald-700 px-3 py-2.5 text-center font-semibold text-white transition hover:bg-emerald-800">Đăng ký</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-20 border-t border-stone-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-zinc-500 sm:flex-row sm:px-6 lg:px-8">
            <p>© {{ date('Y') }} SportHub. Nền tảng đặt sân thể thao.</p>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="transition hover:text-emerald-700">Trang chủ</a>
                <a href="{{ route('login') }}" class="transition hover:text-emerald-700">Đăng nhập</a>
            </div>
        </div>
    </footer>
</body>
</html>
