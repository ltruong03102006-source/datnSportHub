@extends('layouts.app')

@section('title', 'Trang cá nhân | SportHub')

@php
    $avatarUrl = $user->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=10b981&color=fff';
@endphp

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8"
    x-data="{ tab: '{{ $errors->has('current_password') || $errors->has('password') ? 'security' : 'info' }}', preview: '{{ $avatarUrl }}' }">

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Tài khoản</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">Trang cá nhân</h1>
        <p class="mt-2 text-sm text-zinc-500">Quản lý thông tin, bảo mật và ảnh đại diện của bạn.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        {{-- Sidebar: avatar + menu --}}
        <aside class="space-y-4">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-sm">
                <form method="POST" action="{{ route('account.profile.avatar') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="relative mx-auto h-28 w-28">
                        <img :src="preview" alt="Avatar" class="h-28 w-28 rounded-full border border-stone-200 object-cover">
                        <button type="button" @click="$refs.avatarInput.click()"
                            class="absolute bottom-0 right-0 grid h-9 w-9 place-items-center rounded-full border-2 border-white bg-emerald-700 text-white shadow transition hover:bg-emerald-800" title="Đổi ảnh">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                            </svg>
                        </button>
                    </div>

                    <h2 class="mt-4 text-base font-bold text-zinc-900">{{ $user->name }}</h2>
                    <p class="truncate text-sm text-stone-500">{{ $user->email }}</p>

                    <input x-ref="avatarInput" type="file" name="avatar" accept="image/*" class="hidden"
                        @change="const f=$event.target.files[0]; if(f){ preview=URL.createObjectURL(f); $refs.avatarSave.classList.remove('hidden') }">
                    <button type="submit" x-ref="avatarSave" class="mt-4 hidden w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Lưu ảnh đại diện
                    </button>
                    @error('avatar')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            <nav class="rounded-2xl border border-stone-200 bg-white p-2 shadow-sm">
                <button type="button" @click="tab = 'info'"
                    :class="tab === 'info' ? 'bg-emerald-50 text-emerald-800' : 'text-zinc-600 hover:bg-stone-50'"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    Thông tin cá nhân
                </button>
                <button type="button" @click="tab = 'security'"
                    :class="tab === 'security' ? 'bg-emerald-50 text-emerald-800' : 'text-zinc-600 hover:bg-stone-50'"
                    class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                    Bảo mật
                </button>
            </nav>
        </aside>

        {{-- Content panel --}}
        <div>
            {{-- Tab: Thông tin cá nhân --}}
            <div x-show="tab === 'info'" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-base font-bold text-zinc-900">Thông tin cá nhân</h2>
                <p class="mt-1 text-sm text-stone-500">Cập nhật họ tên, email và số điện thoại của bạn.</p>

                <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 grid gap-4 sm:max-w-md">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold text-stone-500">Họ tên</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                            class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold text-stone-500">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                            class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-xs font-semibold text-stone-500">Số điện thoại</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" placeholder="VD: 0901234567"
                            class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <button type="submit" class="rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tab: Bảo mật --}}
            <div x-show="tab === 'security'" x-cloak class="space-y-6">
                <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-base font-bold text-zinc-900">Đổi mật khẩu</h2>
                    <p class="mt-1 text-sm text-stone-500">Nhập mật khẩu hiện tại để xác nhận thay đổi.</p>

                    <form method="POST" action="{{ route('account.profile.password') }}" class="mt-6 grid gap-4 sm:max-w-md">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="mb-1.5 block text-xs font-semibold text-stone-500">Mật khẩu hiện tại</label>
                            <input id="current_password" name="current_password" type="password"
                                class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                            @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="mb-1.5 block text-xs font-semibold text-stone-500">Mật khẩu mới</label>
                            <input id="password" name="password" type="password"
                                class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-xs font-semibold text-stone-500">Xác nhận mật khẩu mới</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                        </div>
                        <div>
                            <button type="submit" class="rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-base font-bold text-zinc-900">Lịch sử đăng nhập</h2>
                    <p class="mt-1 text-sm text-stone-500">10 lần đăng nhập gần nhất.</p>

                    @if ($loginHistories->isEmpty())
                        <p class="mt-4 text-sm text-stone-500">Chưa có dữ liệu đăng nhập.</p>
                    @else
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-stone-200 text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-stone-400">
                                        <th class="py-2 pr-4">Thời gian</th>
                                        <th class="py-2 pr-4">IP</th>
                                        <th class="py-2">Thiết bị</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    @foreach ($loginHistories as $login)
                                        <tr>
                                            <td class="whitespace-nowrap py-2.5 pr-4 font-semibold text-zinc-800">{{ $login->logged_in_at->format('H:i · d/m/Y') }}</td>
                                            <td class="whitespace-nowrap py-2.5 pr-4 text-zinc-600 tabular-nums">{{ $login->ip_address ?? '—' }}</td>
                                            <td class="py-2.5 text-zinc-600" title="{{ $login->user_agent }}">{{ $login->device_label }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
