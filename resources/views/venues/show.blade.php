@extends('layouts.app')

@section('title', $venue->name)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6" x-data="{ activeTab: 'info' }">
    
    <div class="overflow-hidden rounded-xl shadow-sm border border-stone-200 bg-white">
        <img src="{{ $venue->banner ?? 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da' }}" class="aspect-video w-full object-cover">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-zinc-900">{{ $venue->name }}</h1>
            <p class="text-sm text-emerald-700 font-semibold mt-1">{{ $venue->sport->name }}</p>
        </div>
    </div>

    <div class="border-b border-stone-200">
        <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
            <button @click="activeTab = 'info'" 
                :class="activeTab === 'info' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition cursor-pointer">
                Thông tin
            </button>
            <button @click="activeTab = 'courts'" 
                :class="activeTab === 'courts' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition cursor-pointer">
                Danh sách sân con
            </button>
            <button @click="activeTab = 'reviews'" 
                :class="activeTab === 'reviews' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-stone-500 hover:text-stone-700 hover:border-stone-300'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition cursor-pointer">
                Đánh giá (⭐⭐⭐⭐⭐)
            </button>
        </nav>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div x-show="activeTab === 'info'" class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm space-y-4">
                <div>
                    <p class="text-sm font-medium text-stone-600">Địa chỉ</p>
                    <p class="text-base text-zinc-700">{{ $venue->address }}</p>
                </div>
                @if ($ownerPhone)
                    <div class="border-t border-stone-100 pt-4">
                        <p class="text-sm font-medium text-stone-600">Liên hệ chủ sân</p>
                        <p class="text-base text-zinc-700 font-mono">{{ $ownerPhone }}</p>
                    </div>
                @endif
                @if ($venue->description)
                    <div class="border-t border-stone-100 pt-4">
                        <p class="text-sm font-medium text-stone-600">Mô tả</p>
                        <p class="text-base leading-relaxed text-zinc-700">{{ $venue->description }}</p>
                    </div>
                @endif
            </div>

            <div x-show="activeTab === 'courts'" class="rounded-lg border border-stone-200 bg-white shadow-sm overflow-hidden">
                <div class="p-6 space-y-3">
                    @forelse ($venue->courts as $court)
                        <div class="flex items-center justify-between rounded-lg border border-stone-100 bg-white p-4">
                            <p class="font-medium text-zinc-900">{{ $court->name }}</p>
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                {{ ucfirst($court->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-stone-500">Chưa có sân con nào được cấu hình.</p>
                    @endforelse
                </div>
            </div>

            <div x-show="activeTab === 'reviews'" class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm text-center py-12">
                <div class="text-4xl mb-2">⭐</div>
                <p class="text-base font-semibold text-zinc-800">Chưa có đánh giá nào</p>
                <p class="text-sm text-stone-500 mt-1">Tính năng đánh giá sẽ khả dụng sau khi lượt đặt sân hoàn tất.</p>
                </div>

        </div>

        <div class="lg:col-span-1">
            <div class="sticky top-24 rounded-lg border border-stone-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-stone-200 bg-stone-50 px-6 py-4">
                    <h3 class="font-semibold text-zinc-900">Vị trí bản đồ</h3>
                </div>
                <div class="aspect-square w-full">
                    @if ($venue->lat && $venue->lng)
                        <iframe class="h-full w-full"
                            src="https://maps.google.com/maps?q={{ $venue->lat }},{{ $venue->lng }}&hl=vi&z=15&output=embed"
                            style="border: none;" allowfullscreen="" loading="lazy"></iframe>
                    @else
                        <div class="flex h-full items-center justify-center bg-stone-50">
                            <p class="text-sm text-stone-500">Tọa độ chưa được cập nhật</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection