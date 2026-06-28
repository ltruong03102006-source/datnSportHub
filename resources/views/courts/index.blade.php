@extends('layouts.app')

@section('title', 'Tìm sân thể thao | SportHub')

@section('content')
    <div
        x-data="courtBrowser({ sports: @js($sports), sport: @js($defaultSport) })"
        class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12"
    >
        <section class="mb-10 lg:mb-14">
            <div class="max-w-3xl">
                <p class="text-sm font-bold tracking-wider text-emerald-600 uppercase">Khám phá sân</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl md:text-5xl">
                    Tìm sân thể thao gần bạn
                </h1>
                <p class="mt-4 text-base leading-relaxed text-zinc-500 sm:text-lg">
                    Lựa chọn bộ môn yêu thích hoặc tìm kiếm nhanh theo tên cơ sở, khu vực để bắt đầu ngay.
                </p>
            </div>

            <div class="relative mt-8 max-w-2xl group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none">
                    <svg class="h-5 w-5 text-zinc-400 group-focus-within:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input
                    type="search"
                    x-model="query"
                    @input="onSearch"
                    placeholder="Tìm theo tên sân, địa chỉ hoặc loại môn…"
                    class="w-full rounded-2xl border-0 bg-white py-4 pl-14 pr-12 text-base text-zinc-900 shadow-sm ring-1 ring-stone-200 transition-all focus:ring-2 focus:ring-emerald-500 focus:shadow-md outline-none"
                >
                <button
                    type="button"
                    x-show="query"
                    x-cloak
                    @click="clearSearch"
                    class="absolute right-3 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-full text-zinc-400 transition hover:bg-stone-100 hover:text-zinc-700 focus:outline-none"
                    aria-label="Xóa từ khóa"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <p x-show="query.trim() !== ''" x-cloak class="mt-3 flex items-center gap-2 text-sm text-zinc-500">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                Đang tìm kiếm realtime cho: <span class="font-bold text-zinc-800" x-text="query"></span>
            </p>
        </section>

        <!-- Owner Registration Section -->
        <section class="mb-10 lg:mb-16 rounded-3xl overflow-hidden bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 shadow-sm">
            <div class="relative">
                <!-- Background decoration -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="absolute -right-40 -top-40 w-80 h-80 bg-blue-200/10 rounded-full blur-3xl"></div>
                    <div class="absolute -left-40 -bottom-40 w-80 h-80 bg-indigo-200/10 rounded-full blur-3xl"></div>
                </div>

                <div class="relative px-6 py-12 sm:px-8 sm:py-16 lg:px-12 lg:py-20">
                    <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1 max-w-2xl">
                            <p class="text-sm font-bold tracking-wider text-blue-700 uppercase mb-2">Bạn là chủ sân?</p>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight mb-3">
                                Đăng ký miễn phí để quản lý sân
                            </h2>
                            <p class="text-base sm:text-lg text-gray-600 leading-relaxed">
                                Đăng ký làm chủ sân ngay để quản lý lịch đặt sân, doanh thu và khách hàng trên một nền tảng duy nhất.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                            <a href="{{ route('owner.register.page') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-base transition-all hover:from-blue-700 hover:to-blue-800 hover:shadow-lg hover:-translate-y-1 active:translate-y-0 shadow-md focus:ring-4 focus:ring-blue-300/50 uppercase tracking-wider">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Đăng ký ngay
                            </a>
                            <a href="#" class="inline-flex items-center justify-center px-8 py-4 rounded-xl border-2 border-blue-300 text-blue-700 font-bold text-base transition-all hover:bg-blue-50 hover:border-blue-400">
                                Tìm hiểu thêm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Cơ sở nổi bật — tự ẩn khi đang tìm kiếm hoặc lọc theo môn --}}
        @if (!empty($featured) && count($featured))
            <section x-show="sport === 'all' && query.trim() === '' && !hasActiveFilters" x-cloak class="mb-10">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-zinc-900">🏆 Cơ sở nổi bật</h2>
                        <p class="text-sm text-stone-500">Đánh giá cao &amp; được đặt nhiều nhất.</p>
                    </div>
                    <a href="{{ route('rankings') }}" class="shrink-0 text-sm font-semibold text-emerald-700 hover:text-emerald-800">Xem tất cả →</a>
                </div>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ($featured as $venue)
                        <a href="{{ url('/venues/' . $venue['venue_id']) }}"
                            class="group overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                            <div class="relative h-28 bg-gradient-to-br from-emerald-600 to-emerald-800">
                                @if ($venue['thumbnail'])
                                    <img src="{{ $venue['thumbnail'] }}" alt="" class="h-full w-full object-cover">
                                @endif
                                <span class="absolute left-2 top-2 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white shadow">★ {{ number_format($venue['avg_rating'], 1) }}</span>
                            </div>
                            <div class="p-3">
                                <h3 class="truncate text-sm font-bold text-zinc-900 group-hover:text-emerald-700">{{ $venue['name'] }}</h3>
                                <p class="truncate text-xs text-stone-500">{{ $venue['sport_name'] }}</p>
                                <p class="mt-1 text-xs text-stone-400">{{ $venue['bookings_count'] }} lượt đặt</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid gap-8 lg:grid-cols-[240px_minmax(0,1fr)]">

            <aside class="min-w-0 lg:sticky lg:top-24 lg:self-start">
                <h2 class="mb-4 px-2 text-xs font-bold uppercase tracking-wider text-stone-400">Danh mục bộ môn</h2>
                <div class="-mx-2 flex gap-1 overflow-x-auto px-2 pb-2 lg:mx-0 lg:flex-col lg:gap-1.5 lg:overflow-visible lg:px-0 lg:pb-0 hide-scrollbar">
                    
                    <button
                        type="button"
                        @click="selectSport('all')"
                        :class="sport === 'all'
                            ? 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-500/20'
                            : 'bg-transparent text-zinc-600 hover:bg-stone-100/80'"
                        class="flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition-all lg:w-full outline-none"
                    >
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg" :class="sport === 'all' ? 'bg-white shadow-sm' : 'bg-stone-100'">
                            <svg class="h-4 w-4" :class="sport === 'all' ? 'text-emerald-600' : 'text-zinc-500'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                        </span>
                        <span class="flex-1 whitespace-nowrap lg:whitespace-normal">Tất cả</span>
                        <span
                            :class="sport === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-stone-200/70 text-zinc-500'"
                            class="ml-auto rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums"
                        >{{ $totalCourts }}</span>
                    </button>

                    @foreach ($sports as $s)
                        <button
                            type="button"
                            @click="selectSport({{ $s['id'] }})"
                            :class="sport === {{ $s['id'] }}
                                ? 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-500/20'
                                : 'bg-transparent text-zinc-600 hover:bg-stone-100/80'"
                            class="flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition-all lg:w-full outline-none"
                        >
                            <span class="grid h-8 w-8 shrink-0 place-items-center overflow-hidden rounded-lg text-sm font-bold"
                                  :class="sport === {{ $s['id'] }} ? 'bg-white shadow-sm text-emerald-600' : 'bg-stone-100 text-zinc-500'">
                                @if (!empty($s['icon']))
                                    <img src="{{ $s['icon'] }}" alt="{{ $s['name'] }}" class="h-full w-full object-cover">
                                @else
                                    {{ mb_strtoupper(mb_substr($s['name'], 0, 1)) }}
                                @endif
                            </span>
                            <span class="flex-1 whitespace-nowrap lg:whitespace-normal">{{ $s['name'] }}</span>
                            <span
                                :class="sport === {{ $s['id'] }} ? 'bg-emerald-600 text-white shadow-sm' : 'bg-stone-200/70 text-zinc-500'"
                                class="ml-auto rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums"
                            >{{ $s['courts_count'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 space-y-6 border-t border-stone-200/60 pt-5">
                    <div class="flex items-center justify-between px-2">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-stone-400">Bộ lọc</h2>
                        <button
                            type="button"
                            x-show="hasActiveFilters"
                            x-cloak
                            @click="clearFilters()"
                            class="text-[11px] font-semibold text-emerald-600 transition hover:text-emerald-700"
                        >Xoá tất cả</button>
                    </div>

                    {{-- Khu vực (dropdown có tìm kiếm) --}}
                    <div class="space-y-2.5">
                        <p class="px-2 text-[11px] font-semibold uppercase tracking-wide text-stone-400">Khu vực</p>

                        {{-- Tỉnh/Thành --}}
                        <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                            <button
                                type="button"
                                @click="open = !open; q = ''"
                                class="flex w-full items-center justify-between gap-2 rounded-xl border border-stone-200 bg-white py-2.5 pl-3 pr-3 text-left text-sm font-medium outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                            >
                                <span class="truncate" :class="province ? 'text-zinc-700' : 'text-stone-400'" x-text="provinceName || 'Tất cả tỉnh/thành'"></span>
                                <svg class="h-4 w-4 shrink-0 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity class="absolute z-30 mt-1 w-full overflow-hidden rounded-xl border border-stone-200 bg-white shadow-lg">
                                <div class="p-2">
                                    <input x-model="q" type="text" placeholder="Tìm tỉnh/thành…" class="w-full rounded-lg border border-stone-200 px-2.5 py-1.5 text-sm outline-none focus:border-emerald-500" @click.stop>
                                </div>
                                <ul class="max-h-60 overflow-auto pb-1 text-sm">
                                    <li>
                                        <button type="button" @click="selectProvince(''); open = false" class="block w-full px-3 py-1.5 text-left text-stone-500 hover:bg-stone-50">Tất cả tỉnh/thành</button>
                                    </li>
                                    <template x-for="p in provinces.filter((p) => p.name.toLowerCase().includes(q.toLowerCase()))" :key="p.code">
                                        <li>
                                            <button type="button" @click="selectProvince(p.code); open = false" x-text="p.name" :class="p.code === province ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-zinc-700 hover:bg-stone-50'" class="block w-full px-3 py-1.5 text-left"></button>
                                        </li>
                                    </template>
                                    <li x-show="provinces.filter((p) => p.name.toLowerCase().includes(q.toLowerCase())).length === 0" class="px-3 py-2 text-stone-400">Không tìm thấy</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Phường/Xã --}}
                        <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                            <button
                                type="button"
                                @click="if (province && wards.length) { open = !open; q = '' }"
                                :disabled="!province || wards.length === 0"
                                class="flex w-full items-center justify-between gap-2 rounded-xl border border-stone-200 bg-white py-2.5 pl-3 pr-3 text-left text-sm font-medium outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:bg-stone-100"
                            >
                                <span class="truncate" :class="ward ? 'text-zinc-700' : 'text-stone-400'" x-text="ward ? wardName : (province ? 'Tất cả phường/xã' : 'Phường/Xã')"></span>
                                <svg class="h-4 w-4 shrink-0 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity class="absolute z-30 mt-1 w-full overflow-hidden rounded-xl border border-stone-200 bg-white shadow-lg">
                                <div class="p-2">
                                    <input x-model="q" type="text" placeholder="Tìm phường/xã…" class="w-full rounded-lg border border-stone-200 px-2.5 py-1.5 text-sm outline-none focus:border-emerald-500" @click.stop>
                                </div>
                                <ul class="max-h-60 overflow-auto pb-1 text-sm">
                                    <li>
                                        <button type="button" @click="selectWard(''); open = false" class="block w-full px-3 py-1.5 text-left text-stone-500 hover:bg-stone-50">Tất cả phường/xã</button>
                                    </li>
                                    <template x-for="w in wards.filter((w) => w.name.toLowerCase().includes(q.toLowerCase()))" :key="w.code">
                                        <li>
                                            <button type="button" @click="selectWard(w.code); open = false" x-text="w.name" :class="w.code === ward ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-zinc-700 hover:bg-stone-50'" class="block w-full px-3 py-1.5 text-left"></button>
                                        </li>
                                    </template>
                                    <li x-show="wards.filter((w) => w.name.toLowerCase().includes(q.toLowerCase())).length === 0" class="px-3 py-2 text-stone-400">Không tìm thấy</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Khoảng giá --}}
                    <div class="space-y-2.5">
                        <p class="px-2 text-[11px] font-semibold uppercase tracking-wide text-stone-400">Khoảng giá</p>
                        <div class="flex flex-wrap gap-2 px-0.5">
                            <template x-for="b in priceBuckets" :key="b.key">
                                <button
                                    type="button"
                                    @click="selectPrice(b.key)"
                                    x-text="b.label"
                                    :class="price === b.key
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-stone-200 bg-white text-zinc-600 hover:border-stone-300'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition"
                                ></button>
                            </template>
                        </div>
                    </div>

                    {{-- Đánh giá --}}
                    <div class="space-y-2.5">
                        <p class="px-2 text-[11px] font-semibold uppercase tracking-wide text-stone-400">Đánh giá</p>
                        <div class="flex flex-wrap gap-2 px-0.5">
                            <template x-for="r in ratingOptions" :key="r.value">
                                <button
                                    type="button"
                                    @click="selectRating(r.value)"
                                    x-text="r.label"
                                    :class="minRating === r.value
                                        ? 'border-amber-400 bg-amber-50 text-amber-700'
                                        : 'border-stone-200 bg-white text-zinc-600 hover:border-stone-300'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition"
                                ></button>
                            </template>
                        </div>
                    </div>

                    {{-- Khoảng cách --}}
                    <div class="space-y-2.5">
                        <p class="px-2 text-[11px] font-semibold uppercase tracking-wide text-stone-400">Khoảng cách</p>
                        <div class="flex flex-wrap gap-2 px-0.5">
                            <template x-for="d in distanceOptions" :key="d.value">
                                <button
                                    type="button"
                                    @click="selectDistance(d.value)"
                                    :disabled="geoLoading"
                                    x-text="d.label"
                                    :class="radius === d.value
                                        ? 'border-sky-400 bg-sky-50 text-sky-700'
                                        : 'border-stone-200 bg-white text-zinc-600 hover:border-stone-300'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition disabled:opacity-60"
                                ></button>
                            </template>
                        </div>
                        <p x-show="geoLoading" x-cloak class="px-2 text-[11px] text-stone-400">Đang lấy vị trí…</p>
                        <p x-show="geoError" x-cloak x-text="geoError" class="px-2 text-[11px] text-rose-500"></p>
                    </div>
                </div>
            </aside>

            <section class="min-w-0">
                
                <div class="mb-5 border-b border-stone-200/60 pb-3">
                    <p class="text-sm font-medium text-stone-500">
                        <template x-if="!loading && meta.total > 0">
                            <span>Đang hiển thị <span class="font-bold text-zinc-900" x-text="rangeLabel"></span>
                                trong <span class="font-bold text-zinc-900" x-text="meta.total"></span> cơ sở
                                <span x-show="activeSportName">· Môn <span class="font-bold text-zinc-900" x-text="activeSportName"></span></span>
                            </span>
                        </template>
                        <span x-show="loading" class="animate-pulse">Đang đồng bộ dữ liệu…</span>
                    </p>

                    {{-- Bộ lọc đang áp dụng --}}
                    <div x-show="query.trim() !== '' || hasActiveFilters" x-cloak class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-stone-400">Đang lọc:</span>

                        <template x-if="query.trim() !== ''">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 py-1 pl-3 pr-1.5 text-xs font-semibold text-zinc-700">
                                <span x-text="'“' + query.trim() + '”'"></span>
                                <button type="button" @click="clearSearch()" class="grid h-4 w-4 place-items-center rounded-full text-stone-400 transition hover:bg-stone-200 hover:text-zinc-700" aria-label="Bỏ từ khoá">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        </template>

                        <template x-if="province">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 py-1 pl-3 pr-1.5 text-xs font-semibold text-zinc-700">
                                <span x-text="provinceName + (ward ? ' / ' + wardName : '')"></span>
                                <button type="button" @click="clearLocation()" class="grid h-4 w-4 place-items-center rounded-full text-stone-400 transition hover:bg-stone-200 hover:text-zinc-700" aria-label="Bỏ khu vực">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        </template>

                        <template x-if="price">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 py-1 pl-3 pr-1.5 text-xs font-semibold text-emerald-700">
                                <span x-text="priceLabel"></span>
                                <button type="button" @click="selectPrice(price)" class="grid h-4 w-4 place-items-center rounded-full text-emerald-400 transition hover:bg-emerald-100 hover:text-emerald-700" aria-label="Bỏ khoảng giá">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        </template>

                        <template x-if="minRating">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 py-1 pl-3 pr-1.5 text-xs font-semibold text-amber-700">
                                <span x-text="ratingLabel"></span>
                                <button type="button" @click="selectRating(minRating)" class="grid h-4 w-4 place-items-center rounded-full text-amber-400 transition hover:bg-amber-100 hover:text-amber-700" aria-label="Bỏ đánh giá">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        </template>

                        <template x-if="radius">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 py-1 pl-3 pr-1.5 text-xs font-semibold text-sky-700">
                                <span x-text="distanceLabel"></span>
                                <button type="button" @click="selectDistance(radius)" class="grid h-4 w-4 place-items-center rounded-full text-sky-400 transition hover:bg-sky-100 hover:text-sky-700" aria-label="Bỏ khoảng cách">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        </template>

                        <button type="button" @click="clearAll()" class="ml-1 text-xs font-semibold text-rose-500 transition hover:text-rose-600">Xoá hết</button>
                    </div>
                </div>

                <div x-show="loading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="i in 6" :key="i">
                        <div class="overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm">
                            <div class="h-44 animate-pulse bg-stone-200/60"></div>
                            <div class="space-y-4 p-5">
                                <div class="h-5 w-3/4 animate-pulse rounded bg-stone-200/60"></div>
                                <div class="space-y-2">
                                    <div class="h-3 w-1/2 animate-pulse rounded bg-stone-200/60"></div>
                                    <div class="h-3 w-2/3 animate-pulse rounded bg-stone-200/60"></div>
                                </div>
                                <div class="mt-4 h-10 w-full animate-pulse rounded-xl bg-stone-200/60"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="hasResults" x-cloak class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="court in items" :key="court.venue_id">
                        
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl hover:shadow-emerald-900/5">
   <div class="relative h-44 overflow-hidden bg-stone-100">
        <a :href="'/venues/' + court.venue_id" class="block h-full w-full">
            <template x-if="court.thumbnail">
                <img :src="court.thumbnail" :alt="court.name" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            </template>
            <template x-if="!court.thumbnail">
                <div class="flex h-full w-full items-center justify-center">
                    <svg class="h-10 w-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            </template>
        </a>
        
        <span class="absolute left-3 top-3 rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-800 shadow-sm backdrop-blur-md" x-text="court.sport_name"></span>

        @auth
        <button @click.prevent="toggleCardFavorite(court.venue_id)" class="absolute right-14 top-3 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 shadow-sm backdrop-blur-md transition-transform hover:scale-110">
            <svg :id="'card-fav-icon-' + court.venue_id" class="h-4 w-4 text-zinc-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>
        @endauth

        <template x-if="court.lat && court.lng">
            <a :href="'https://www.google.com/maps/search/?api=1&query=' + court.lat + ',' + court.lng" target="_blank" title="Xem trên Google Maps" class="absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-full bg-white/95 text-red-500 shadow-sm backdrop-blur-md transition-transform hover:scale-110 hover:text-red-600">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </a>
        </template>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-bold leading-snug text-zinc-900 transition-colors group-hover:text-emerald-700 line-clamp-1" x-text="court.name"></h3>
        
        <p class="mt-1 text-xs font-semibold text-emerald-600" x-text="court.courts_count ? court.courts_count + ' sân con' : 'Đang cập nhật'"></p>

        <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
            <span x-show="court.avg_rating" x-cloak class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.39c.12.49-.42.88-.85.62L12 17.34l-4.74 2.91c-.43.26-.97-.13-.85-.62l1.28-5.39-4.2-3.6c-.38-.33-.18-.95.32-.99l5.52-.44 2.13-5.11Z" /></svg>
                <span x-text="court.avg_rating"></span>
            </span>
            <span x-show="court.min_price" x-cloak class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">
                <span x-text="'từ ' + formatPrice(court.min_price) + 'đ'"></span>
            </span>
            <span x-show="court.distance_km !== null && court.distance_km !== undefined" x-cloak class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-2 py-0.5 text-xs font-bold text-sky-700">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                <span x-text="court.distance_km + ' km'"></span>
            </span>
        </div>

        <div class="mt-3 mb-5 space-y-2 text-sm text-stone-500">
            <p class="flex items-start gap-2">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                <span class="line-clamp-1" x-text="court.address"></span>
            </p>
            <p class="flex items-center gap-2">
    <svg class="h-4 w-4 shrink-0 text-stone-400" ...></svg>
    <span class="tabular-nums font-medium" x-text="court.phone ?? 'Đang cập nhật'"></span>
</p>
        </div>

        <a :href="'/venues/' + court.venue_id"
           class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-emerald-600 shadow-sm focus:ring-4 focus:ring-emerald-500/20 active:scale-95">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
            </svg>
            Đặt lịch
        </a>
    </div>
</article>

                    </template>
                </div>

                <div x-show="isEmpty" x-cloak class="rounded-3xl border border-dashed border-stone-200 bg-white py-20 text-center shadow-sm">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-stone-50 text-3xl">🔍</div>
                    <p class="mt-4 text-lg font-bold text-zinc-900" x-text="empty?.message ?? 'Không tìm thấy kết quả phù hợp.'"></p>
                    <p class="mt-1 text-sm font-medium text-stone-500" x-text="empty?.suggestion ?? 'Thử thay đổi từ khóa hoặc bỏ lọc môn thể thao.'"></p>
                </div>

                <div x-show="error" x-cloak class="rounded-3xl border border-red-100 bg-red-50 py-16 text-center">
                    <p class="text-base font-bold text-red-700">Lỗi kết nối máy chủ</p>
                    <p class="mt-1 text-sm font-medium text-red-500">Vui lòng kiểm tra đường truyền và thử lại.</p>
                    <button type="button" @click="load" class="mt-4 rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 shadow-sm active:scale-95">Tải lại dữ liệu</button>
                </div>

                <nav x-show="!loading && meta.last_page > 1" x-cloak class="mt-10 flex flex-wrap items-center justify-center gap-2">
                    <button
                        type="button"
                        @click="goTo(meta.current_page - 1)"
                        :disabled="meta.current_page === 1"
                        class="grid h-10 w-10 place-items-center rounded-xl border border-stone-200 bg-white text-zinc-600 transition hover:bg-stone-50 hover:text-emerald-600 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </button>

                    <template x-for="page in pages" :key="page">
                        <button
                            type="button"
                            @click="goTo(page)"
                            :class="page === meta.current_page
                                ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                                : 'border-stone-200 bg-white text-zinc-600 hover:bg-stone-50 hover:text-emerald-600'"
                            class="h-10 min-w-[2.5rem] rounded-xl border px-3 text-sm font-bold tabular-nums transition"
                            x-text="page"
                        ></button>
                    </template>

                    <button
                        type="button"
                        @click="goTo(meta.current_page + 1)"
                        :disabled="meta.current_page === meta.last_page"
                        class="grid h-10 w-10 place-items-center rounded-xl border border-stone-200 bg-white text-zinc-600 transition hover:bg-stone-50 hover:text-emerald-600 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>
                </nav>
            </section>
        </div>
    </div>

    <style>
        /* Ẩn thanh cuộn cho bộ lọc môn trên Mobile */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endsection
@section('scripts')
<script>
    async function toggleCardFavorite(venueId) {
        try {
            const response = await fetch(`/venues/${venueId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (response.ok) {
                const icon = document.getElementById(`card-fav-icon-${venueId}`);
                if (data.status === 'added') {
                    icon.classList.remove('text-zinc-400');
                    icon.classList.add('text-rose-500', 'fill-rose-500');
                } else {
                    icon.classList.remove('text-rose-500', 'fill-rose-500');
                    icon.classList.add('text-zinc-400');
                }
                
                // GỌI THÔNG BÁO XỊN XÒ TẠI ĐÂY
                showToast(data.message, 'success');
            } else {
                showToast('Không thể thực hiện thao tác này.', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối máy chủ.', 'error');
        }
    }
</script>
@endsection