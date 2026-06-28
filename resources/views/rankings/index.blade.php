@extends('layouts.app')

@section('title', 'Sân nổi bật | SportHub')

@php
    // Compact ranking row used by all three lists
    $stars = function (float $rating) {
        $full = (int) round($rating);
        return str_repeat('★', $full) . str_repeat('☆', 5 - $full);
    };
@endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Khám phá</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">Cơ sở nổi bật</h1>
        <p class="mt-2 text-sm text-zinc-500">Xếp hạng dựa trên điểm đánh giá (70%) và số lượt đặt sân (30%).</p>
    </div>

    {{-- Top 10 nổi bật --}}
    <section class="mb-10">
        <h2 class="mb-4 text-lg font-extrabold text-zinc-900">🏆 Top 10 nổi bật</h2>

        @if ($featured->isEmpty())
            <p class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 py-10 text-center text-sm text-stone-500">Chưa có dữ liệu xếp hạng.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $i => $venue)
                    <a href="{{ url('/venues/' . $venue['venue_id']) }}"
                        class="group flex gap-4 overflow-hidden rounded-2xl border border-stone-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                        <div class="relative shrink-0">
                            <div class="h-20 w-20 overflow-hidden rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800">
                                @if ($venue['thumbnail'])
                                    <img src="{{ $venue['thumbnail'] }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <span class="absolute -left-2 -top-2 grid h-7 w-7 place-items-center rounded-full text-xs font-extrabold text-white shadow {{ $i < 3 ? 'bg-amber-500' : 'bg-zinc-700' }}">{{ $i + 1 }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-zinc-900 group-hover:text-emerald-700">{{ $venue['name'] }}</h3>
                            <p class="text-xs text-stone-500">{{ $venue['sport_name'] }}</p>
                            <div class="mt-1.5 flex items-center gap-2 text-xs">
                                <span class="font-semibold text-amber-500">{{ $stars($venue['avg_rating']) }}</span>
                                <span class="text-stone-500">{{ number_format($venue['avg_rating'], 1) }} ({{ $venue['reviews_count'] }})</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-stone-500">{{ $venue['bookings_count'] }} lượt đặt</span>
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ $venue['ranking_score'] }} điểm</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <div class="grid gap-8 lg:grid-cols-2">
        {{-- Đặt nhiều nhất --}}
        <section>
            <h2 class="mb-4 text-lg font-extrabold text-zinc-900">🔥 Đặt nhiều nhất</h2>
            <div class="divide-y divide-stone-100 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                @forelse ($most_booked as $i => $venue)
                    <a href="{{ url('/venues/' . $venue['venue_id']) }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-stone-50">
                        <span class="w-5 text-center text-sm font-bold text-stone-400">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-zinc-900">{{ $venue['name'] }}</p>
                            <p class="text-xs text-stone-500">{{ $venue['sport_name'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $venue['bookings_count'] }} lượt</span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-stone-500">Chưa có dữ liệu.</p>
                @endforelse
            </div>
        </section>

        {{-- Đánh giá cao nhất --}}
        <section>
            <h2 class="mb-4 text-lg font-extrabold text-zinc-900">⭐ Đánh giá cao nhất</h2>
            <div class="divide-y divide-stone-100 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                @forelse ($top_rated as $i => $venue)
                    <a href="{{ url('/venues/' . $venue['venue_id']) }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-stone-50">
                        <span class="w-5 text-center text-sm font-bold text-stone-400">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-zinc-900">{{ $venue['name'] }}</p>
                            <p class="text-xs text-stone-500">{{ $venue['sport_name'] }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-amber-500">★ {{ number_format($venue['avg_rating'], 1) }} <span class="font-normal text-stone-400">({{ $venue['reviews_count'] }})</span></span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-stone-500">Chưa có đánh giá.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
