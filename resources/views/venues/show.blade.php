@extends('layouts.app')

@section('title', $venue->name . ' | SportHub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" x-data="{ activeTab: 'courts' }">
    
    <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="relative h-64 w-full bg-stone-100 sm:h-80">
            <img src="{{ $venue->banner ?? 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da' }}" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
            
            <div class="absolute bottom-6 left-6 right-6 sm:bottom-8 sm:left-8 flex flex-col items-start">
                <span class="mb-3 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/90 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-md shadow-sm">
                    {{ $venue->sport->name }}
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $venue->name }}</h1>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
        
        <div class="lg:col-span-8 xl:col-span-8">
            
            <div class="mb-6 flex space-x-6 border-b border-stone-200 overflow-x-auto hide-scrollbar">
                <button @click="activeTab = 'courts'" 
                    :class="activeTab === 'courts' ? 'border-emerald-500 text-emerald-700' : 'border-transparent text-stone-500 hover:text-stone-800 hover:border-stone-300'"
                    class="whitespace-nowrap border-b-2 pb-3 text-sm font-bold transition-all outline-none">
                    Danh sách sân
                </button>
                <button @click="activeTab = 'info'" 
                    :class="activeTab === 'info' ? 'border-emerald-500 text-emerald-700' : 'border-transparent text-stone-500 hover:text-stone-800 hover:border-stone-300'"
                    class="whitespace-nowrap border-b-2 pb-3 text-sm font-bold transition-all outline-none">
                    Giới thiệu
                </button>
                <button @click="activeTab = 'reviews'" 
                    :class="activeTab === 'reviews' ? 'border-emerald-500 text-emerald-700' : 'border-transparent text-stone-500 hover:text-stone-800 hover:border-stone-300'"
                    class="whitespace-nowrap border-b-2 pb-3 text-sm font-bold transition-all outline-none flex items-center gap-1">
                    Đánh giá <span class="text-amber-400 text-xs">★★★★★</span>
                </button>
            </div>

            <div x-show="activeTab === 'courts'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                @forelse ($venue->courts as $court)
                    <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-2xl border border-stone-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-md">
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 transition-colors group-hover:text-emerald-700">{{ $court->name }}</h3>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="flex items-center gap-1.5 rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-emerald-600 ring-1 ring-inset ring-emerald-500/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ $court->status === 'active' ? 'Hoạt động' : 'Bảo trì' }}
                                </span>
                                <span class="text-xs font-medium text-stone-400">Sẵn sàng đặt lịch</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('web.courts.booking', $court->id) }}"
                           class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-600 focus:ring-4 focus:ring-emerald-500/20 active:scale-95 shadow-sm">
                            Chọn giờ
                        </a>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 py-12 text-center">
                        <p class="text-sm font-medium text-stone-500">Cơ sở này hiện chưa cấu hình sân con nào.</p>
                    </div>
                @endforelse
            </div>

            <div x-show="activeTab === 'info'" x-cloak class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8 space-y-6">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-2">Về chúng tôi</h4>
                    <p class="text-sm leading-relaxed text-zinc-700">{{ $venue->description ?? 'Chưa có mô tả chi tiết cho cơ sở này. Vui lòng liên hệ trực tiếp để biết thêm thông tin.' }}</p>
                </div>
            </div>

            <div x-show="activeTab === 'reviews'" x-cloak>
                @include('reviews.partials.panel', ['venue' => $venue])
            </div>

        </div>

        <div class="lg:col-span-4 xl:col-span-4">
            <div class="sticky top-24 space-y-6">
                
                <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-zinc-900">Thông tin liên hệ</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-0.5">Địa chỉ</p>
                                <p class="text-sm font-medium text-zinc-800">{{ $venue->address }}</p>
                            </div>
                        </div>

                        @if ($ownerPhone)
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-0.5">Hotline</p>
                                <p class="text-sm font-bold text-zinc-900">{{ $ownerPhone }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="border-b border-stone-100 bg-stone-50/50 px-5 py-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Vị trí bản đồ</h3>
                    </div>
                    <div class="aspect-square w-full bg-stone-100 relative">
                        @if ($venue->lat && $venue->lng)
                            <iframe class="absolute inset-0 h-full w-full"
                                src="https://maps.google.com/maps?q={{ $venue->lat }},{{ $venue->lng }}&hl=vi&z=15&output=embed"
                                style="border: none;" allowfullscreen="" loading="lazy"></iframe>
                        @else
                            <div class="flex h-full flex-col items-center justify-center text-stone-400">
                                <svg class="mb-2 h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                <p class="text-sm font-medium">Chưa cập nhật tọa độ</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
    /* Ẩn thanh cuộn cho Tabs trên Mobile */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection