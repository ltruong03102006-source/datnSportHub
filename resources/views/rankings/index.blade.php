@extends('layouts.app')

@section('title', 'Xếp hạng cơ sở | SportHub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
    <section class="mb-8 flex flex-col gap-4 border-b border-stone-200 pb-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Bảng xếp hạng</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl">Xếp hạng cơ sở theo đánh giá và lượt đặt</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-500">
                Điểm tổng hợp ưu tiên chất lượng đánh giá và độ phổ biến đặt sân, giúp người chơi chọn nhanh các cơ sở đáng tin cậy.
            </p>
        </div>
        <a href="{{ route('home', ['sort' => 'ranking']) }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700">
            Tìm sân theo xếp hạng
        </a>
    </section>

    <section class="mb-10">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-zinc-900">Top cơ sở nổi bật</h2>
                <p class="text-sm text-stone-500">Tổng hợp từ điểm đánh giá và số lượt đặt thành công.</p>
            </div>
        </div>

        @if ($featured->isEmpty())
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white py-12 text-center text-sm font-medium text-stone-500">
                Chưa có dữ liệu xếp hạng.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $index => $venue)
                    <a href="{{ url('/venues/' . $venue['venue_id']) }}" class="group overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg">
                        <div class="relative h-40 bg-stone-100">
                            <img src="{{ $venue['thumbnail'] ?: 'https://placehold.co/600x400/e5e7eb/334155?text=SportHub' }}" alt="{{ $venue['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            <span class="absolute left-3 top-3 grid h-9 w-9 place-items-center rounded-full text-sm font-extrabold shadow {{ $index < 3 ? 'bg-amber-500 text-white' : 'bg-zinc-900 text-white' }}">{{ $index + 1 }}</span>
                            <span class="absolute bottom-3 right-3 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-emerald-700 shadow-sm">{{ number_format($venue['ranking_score'], 1) }} điểm</span>
                        </div>
                        <div class="p-4">
                            <h3 class="line-clamp-1 text-base font-extrabold text-zinc-900 group-hover:text-emerald-700">{{ $venue['name'] }}</h3>
                            <p class="mt-1 line-clamp-1 text-sm text-stone-500">{{ $venue['sport_name'] }} · {{ $venue['address'] }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <span class="rounded-lg bg-amber-50 px-2.5 py-2 font-bold text-amber-700">★ {{ number_format($venue['avg_rating'], 1) }} ({{ $venue['reviews_count'] }})</span>
                                <span class="rounded-lg bg-indigo-50 px-2.5 py-2 font-bold text-indigo-700">{{ number_format($venue['bookings_count']) }} lượt đặt</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section>
            <h2 class="mb-4 text-lg font-extrabold text-zinc-900">Đặt nhiều nhất</h2>
            <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                @forelse ($most_booked as $index => $venue)
                    <a href="{{ url('/venues/' . $venue['venue_id']) }}" class="flex items-center gap-3 border-b border-stone-100 px-4 py-3 transition last:border-b-0 hover:bg-stone-50">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-zinc-100 text-xs font-extrabold text-zinc-700">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-zinc-900">{{ $venue['name'] }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $venue['sport_name'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-indigo-50 px-3 py-1 text-xs font-extrabold text-indigo-700">{{ number_format($venue['bookings_count']) }} lượt</span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-stone-500">Chưa có lượt đặt.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-4 text-lg font-extrabold text-zinc-900">Đánh giá cao nhất</h2>
            <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                @forelse ($top_rated as $index => $venue)
                    <a href="{{ url('/venues/' . $venue['venue_id']) }}" class="flex items-center gap-3 border-b border-stone-100 px-4 py-3 transition last:border-b-0 hover:bg-stone-50">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-zinc-100 text-xs font-extrabold text-zinc-700">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-zinc-900">{{ $venue['name'] }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $venue['sport_name'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-extrabold text-amber-700">★ {{ number_format($venue['avg_rating'], 1) }} ({{ $venue['reviews_count'] }})</span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-stone-500">Chưa có đánh giá.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
