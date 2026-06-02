@extends('layouts.app')

@section('title', 'Tìm sân thể thao | SportHub')

@section('content')
    <div
        x-data="courtBrowser({ sports: @js($sports), sport: @js($defaultSport) })"
        class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12"
    >
        <section class="mb-8">
            <p class="text-sm font-semibold text-emerald-700">Khám phá sân</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Tìm sân thể thao gần bạn
            </h1>
            <p class="mt-3 max-w-2xl text-base leading-relaxed text-zinc-500">
                Chọn loại môn ở thanh bên và tìm theo tên sân hoặc địa chỉ để xem các sân đang hoạt động.
            </p>

            <div class="relative mt-6 max-w-xl">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="search"
                    x-model="query"
                    @input="onSearch"
                    placeholder="Tìm theo tên sân hoặc địa chỉ…"
                    class="w-full rounded-xl border border-stone-300 bg-white py-3.5 pl-12 pr-4 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10"
                >
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="min-w-0 lg:sticky lg:top-24 lg:self-start">
                <h2 class="mb-3 px-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Loại môn</h2>
                <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-2 lg:mx-0 lg:flex-col lg:gap-1.5 lg:overflow-visible lg:px-0 lg:pb-0">
                    <button
                        type="button"
                        @click="selectSport('all')"
                        :class="sport === 'all'
                            ? 'border-emerald-600 bg-emerald-50 text-emerald-800'
                            : 'border-transparent text-zinc-600 hover:bg-stone-100'"
                        class="flex shrink-0 items-center gap-3 rounded-xl border px-3.5 py-2.5 text-left text-sm font-medium transition lg:w-full"
                    >
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100">
                            <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                        </span>
                        <span class="flex-1 whitespace-nowrap lg:whitespace-normal">Tất cả</span>
                        <span
                            :class="sport === 'all' ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-zinc-600'"
                            class="ml-auto rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums"
                        >{{ $totalCourts }}</span>
                    </button>

                    @foreach ($sports as $sport)
                        <button
                            type="button"
                            @click="selectSport({{ $sport['id'] }})"
                            :class="sport === {{ $sport['id'] }}
                                ? 'border-emerald-600 bg-emerald-50 text-emerald-800'
                                : 'border-transparent text-zinc-600 hover:bg-stone-100'"
                            class="flex shrink-0 items-center gap-3 rounded-xl border px-3.5 py-2.5 text-left text-sm font-medium transition lg:w-full"
                        >
                            <span class="grid h-8 w-8 shrink-0 place-items-center overflow-hidden rounded-lg bg-stone-100 text-sm font-bold text-zinc-500">
                                @if (!empty($sport['icon']))
                                    <img src="{{ $sport['icon'] }}" alt="{{ $sport['name'] }}" class="h-full w-full object-cover">
                                @else
                                    {{ mb_strtoupper(mb_substr($sport['name'], 0, 1)) }}
                                @endif
                            </span>
                            <span class="flex-1 whitespace-nowrap lg:whitespace-normal">{{ $sport['name'] }}</span>
                            <span
                                :class="sport === {{ $sport['id'] }} ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-zinc-600'"
                                class="ml-auto rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums"
                            >{{ $sport['courts_count'] }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <section class="min-w-0">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <p class="text-sm text-zinc-500">
                        <template x-if="!loading && meta.total > 0">
                            <span>Hiển thị <span class="font-semibold text-zinc-800" x-text="rangeLabel"></span>
                                trong <span class="font-semibold text-zinc-800" x-text="meta.total"></span> sân
                                <span x-show="activeSportName">· <span class="font-semibold text-zinc-800" x-text="activeSportName"></span></span>
                            </span>
                        </template>
                        <span x-show="loading">Đang tải…</span>
                    </p>
                </div>

                <div x-show="loading" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="i in 6" :key="i">
                        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                            <div class="h-40 animate-pulse bg-stone-200"></div>
                            <div class="space-y-3 p-4">
                                <div class="h-4 w-3/4 animate-pulse rounded bg-stone-200"></div>
                                <div class="h-3 w-1/2 animate-pulse rounded bg-stone-200"></div>
                                <div class="h-3 w-2/3 animate-pulse rounded bg-stone-200"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="hasResults" x-cloak class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="court in items" :key="court.court_id">
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/5">
                            <div class="relative h-40 overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-800">
                                <template x-if="court.thumbnail">
                                    <img :src="court.thumbnail" :alt="court.name" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                </template>
                                <template x-if="!court.thumbnail">
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-14 w-14 text-white/70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                    </div>
                                </template>
                                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-emerald-800 shadow-sm" x-text="court.sport_name"></span>
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="text-base font-semibold leading-snug text-zinc-900" x-text="court.name"></h3>
                                <p class="mt-1 text-sm text-zinc-500" x-text="court.court_name"></p>

                                <div class="mt-3 mb-4 space-y-2 text-sm text-zinc-600">
                                    <p class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        <span x-text="court.address"></span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                        </svg>
                                        <span class="tabular-nums" x-text="court.phone_hidden ?? 'Đang cập nhật'"></span>
                                    </p>
                                </div>

                                <a
                                    :href="`/courts/${court.court_id}`"
                                    class="mt-auto inline-flex items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 py-2.5 text-sm font-semibold text-emerald-700 transition group-hover:border-emerald-600 group-hover:bg-emerald-600 group-hover:text-white"
                                >
                                    Xem chi tiết
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </article>
                    </template>
                </div>

                <div x-show="isEmpty" x-cloak class="rounded-2xl border border-dashed border-stone-300 bg-white py-16 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-stone-100 text-2xl">🔍</div>
                    <p class="mt-4 text-base font-semibold text-zinc-800" x-text="empty?.message ?? 'Không tìm thấy kết quả phù hợp.'"></p>
                    <p class="mt-1 text-sm text-zinc-500" x-text="empty?.suggestion ?? 'Thử thay đổi từ khóa hoặc bỏ lọc môn thể thao.'"></p>
                </div>

                <div x-show="error" x-cloak class="rounded-2xl border border-dashed border-red-200 bg-red-50 py-16 text-center">
                    <p class="text-base font-semibold text-red-700">Không tải được dữ liệu.</p>
                    <p class="mt-1 text-sm text-red-500">Vui lòng kiểm tra kết nối và thử lại.</p>
                    <button type="button" @click="load" class="mt-4 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Thử lại</button>
                </div>

                <nav x-show="!loading && meta.last_page > 1" x-cloak class="mt-8 flex flex-wrap items-center justify-center gap-1.5">
                    <button
                        type="button"
                        @click="goTo(meta.current_page - 1)"
                        :disabled="meta.current_page === 1"
                        class="grid h-10 w-10 place-items-center rounded-lg border border-stone-200 bg-white text-zinc-600 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </button>

                    <template x-for="page in pages" :key="page">
                        <button
                            type="button"
                            @click="goTo(page)"
                            :class="page === meta.current_page
                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                : 'border-stone-200 bg-white text-zinc-600 hover:bg-stone-100'"
                            class="h-10 min-w-10 rounded-lg border px-3 text-sm font-semibold tabular-nums transition"
                            x-text="page"
                        ></button>
                    </template>

                    <button
                        type="button"
                        @click="goTo(meta.current_page + 1)"
                        :disabled="meta.current_page === meta.last_page"
                        class="grid h-10 w-10 place-items-center rounded-lg border border-stone-200 bg-white text-zinc-600 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>
                </nav>
            </section>
        </div>
    </div>
@endsection
