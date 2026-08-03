@extends('owner.layoutOwner.app')

@section('title', 'Yêu cầu đổi lịch | Chủ sân')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Quản lý đổi lịch
                </span>
                @if(($stats['pending'] ?? 0) > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-0.5 text-xs font-black text-white shadow-sm animate-pulse">
                        {{ $stats['pending'] }} cần xử lý
                    </span>
                @endif
            </div>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-900">Yêu cầu đổi lịch đặt sân</h1>
            <p class="mt-1 text-sm font-semibold text-slate-500">Xem, duyệt hoặc từ chối các yêu cầu thay đổi khung giờ chơi của khách hàng.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('owner.web.calendar.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-stone-50 hover:text-emerald-700">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                Xem lịch đặt sân
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 font-bold text-emerald-900 shadow-xs">
            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mt-5 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50/90 p-4 font-bold text-red-900 shadow-xs">
            <svg class="h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistics Cards Grid (Compact) -->
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        <!-- Total Card -->
        <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs transition hover:shadow-xs">
            <div class="flex items-center justify-between gap-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500">Tất cả yêu cầu</span>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3-6.75A2.25 2.25 0 0118 9v9a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V9a2.25 2.25 0 012.25-2.25h1.35m3-3h3a2.25 2.25 0 012.25 2.25v.75m-6 0h6" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black leading-none text-zinc-900">{{ $stats['total'] ?? $requests->count() }}</p>
            <p class="mt-1 text-[11px] font-semibold text-slate-400">Tổng số lượt yêu cầu</p>
        </div>

        <!-- Pending Card -->
        <div class="relative overflow-hidden rounded-xl border border-amber-200/80 bg-amber-50/40 p-3.5 shadow-2xs transition hover:shadow-xs">
            <div class="flex items-center justify-between gap-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-700">Chờ duyệt</span>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black leading-none text-amber-900">{{ $stats['pending'] ?? 0 }}</p>
            <p class="mt-1 text-[11px] font-bold text-amber-700">Cần xử lý phê duyệt</p>
        </div>

        <!-- Approved Card -->
        <div class="relative overflow-hidden rounded-xl border border-emerald-200/80 bg-emerald-50/40 p-3.5 shadow-2xs transition hover:shadow-xs">
            <div class="flex items-center justify-between gap-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-700">Đã duyệt</span>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black leading-none text-emerald-900">{{ $stats['approved'] ?? 0 }}</p>
            <p class="mt-1 text-[11px] font-bold text-emerald-700">Đã đồng ý đổi ca</p>
        </div>

        <!-- Rejected Card -->
        <div class="relative overflow-hidden rounded-xl border border-rose-200/80 bg-rose-50/40 p-3.5 shadow-2xs transition hover:shadow-xs">
            <div class="flex items-center justify-between gap-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-rose-700">Đã từ chối</span>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black leading-none text-rose-900">{{ $stats['rejected'] ?? 0 }}</p>
            <p class="mt-1 text-[11px] font-bold text-rose-700">Đã từ chối yêu cầu</p>
        </div>
    </div>

    <!-- Filter & Search Toolbar Card -->
    <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 rounded-xl bg-slate-100/80 p-1.5">
                <button type="button"
                        onclick="filterByStatus('all')"
                        id="tab-all"
                        class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-black transition active-tab">
                    <span>Tất cả</span>
                    <span class="rounded-md bg-white/30 px-2 py-0.5 text-[11px] font-black leading-none">{{ $stats['total'] ?? $requests->count() }}</span>
                </button>

                <button type="button"
                        onclick="filterByStatus('pending')"
                        id="tab-pending"
                        class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-black text-slate-600 transition hover:text-zinc-900">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                    <span>Chờ duyệt</span>
                    <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[11px] font-black text-amber-800 leading-none">{{ $stats['pending'] ?? 0 }}</span>
                </button>

                <button type="button"
                        onclick="filterByStatus('approved')"
                        id="tab-approved"
                        class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-black text-slate-600 transition hover:text-zinc-900">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Đã duyệt</span>
                    <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-black text-emerald-800 leading-none">{{ $stats['approved'] ?? 0 }}</span>
                </button>

                <button type="button"
                        onclick="filterByStatus('rejected')"
                        id="tab-rejected"
                        class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-black text-slate-600 transition hover:text-zinc-900">
                    <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>
                    <span>Đã từ chối</span>
                    <span class="rounded-md bg-rose-100 px-2 py-0.5 text-[11px] font-black text-rose-800 leading-none">{{ $stats['rejected'] ?? 0 }}</span>
                </button>
            </div>

            <!-- Search Input Box -->
            <div class="relative min-w-[260px] lg:w-80">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text"
                       id="search-input"
                       onkeyup="applySearchAndFilter()"
                       placeholder="Tìm mã, khách hàng, sân..."
                       class="w-full rounded-xl border border-slate-300 bg-slate-50/60 pl-10 pr-4 py-2.5 text-xs font-bold outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80 text-[11px] font-black uppercase tracking-wider text-slate-500">
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Mã yêu cầu</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Khách hàng</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Sân đặt</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap text-center">Số ca đổi</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap text-center">Trạng thái</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Ngày gửi</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($requests as $item)
                        @php($group = $item->getRelation('groupedRequests') ?? collect([$item]))
                        @php($code = $item->request_code ?: (string) $item->id)
                        @php($status = $item->status)
                        <tr class="reschedule-row transition-colors hover:bg-slate-50/80"
                            data-status="{{ $status }}"
                            data-search="{{ strtolower($code . ' ' . ($item->user?->name ?? '') . ' ' . ($item->booking?->court?->name ?? '') . ' ' . ($item->booking?->court?->venue?->name ?? '')) }}">
                            
                            <!-- Mã Yêu Cầu -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700 font-mono border border-slate-200/80">
                                        #{{ $code }}
                                    </span>
                                </div>
                            </td>

                            <!-- Khách Hàng -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-black text-emerald-800 uppercase shadow-2xs">
                                        {{ mb_substr($item->user?->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-zinc-900 leading-tight">{{ $item->user?->name ?? 'Khách hàng' }}</p>
                                        <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $item->user?->phone ?? $item->user?->email ?? 'Booking #' . $item->booking_id }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Sân Đặt -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="font-black text-zinc-900 leading-tight">{{ $item->booking?->court?->name ?? 'Sân' }}</p>
                                    <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $item->booking?->court?->venue?->name ?? '' }}</p>
                                </div>
                            </td>

                            <!-- Số Ca -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700 border border-slate-200/60">
                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $group->count() }} ca
                                </span>
                            </td>

                            <!-- Trạng Thái -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3.5 py-1.5 text-xs font-black text-amber-800 border border-amber-200/80 shadow-2xs whitespace-nowrap">
                                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        Chờ duyệt
                                    </span>
                                @elseif($status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3.5 py-1.5 text-xs font-black text-emerald-800 border border-emerald-200/80 shadow-2xs whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Đã duyệt
                                    </span>
                                @elseif($status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3.5 py-1.5 text-xs font-black text-rose-800 border border-rose-200/80 shadow-2xs whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Đã từ chối
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3.5 py-1.5 text-xs font-black text-slate-700 border border-slate-200/80 shadow-2xs whitespace-nowrap">
                                        {{ ucfirst($status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Ngày Gửi -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-600">
                                    <p class="font-black text-zinc-900">{{ $item->created_at?->format('d/m/Y') }}</p>
                                    <p class="text-slate-400 mt-0.5">{{ $item->created_at?->format('H:i') }}</p>
                                </div>
                            </td>

                            <!-- Thao Tác -->
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('owner.web.reschedule.show', $code) }}" 
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700 border border-emerald-200/80 shadow-2xs transition hover:bg-emerald-600 hover:text-white hover:border-emerald-600 whitespace-nowrap">
                                    Chi tiết
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-state-row">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </span>
                                    <p class="text-base font-black text-zinc-800">Chưa có yêu cầu đổi lịch nào</p>
                                    <p class="text-xs font-semibold text-slate-400">Các yêu cầu đổi lịch từ khách hàng sẽ xuất hiện tại đây.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .active-tab {
        background-color: #ffffff !important;
        color: #09090b !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
</style>

<script>
    let currentStatusFilter = 'all';

    function filterByStatus(status) {
        currentStatusFilter = status;

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active-tab', 'bg-white', 'text-zinc-900', 'shadow-xs');
            btn.classList.add('text-slate-600');
        });

        const activeBtn = document.getElementById(`tab-${status}`);
        if (activeBtn) {
            activeBtn.classList.add('active-tab');
            activeBtn.classList.remove('text-slate-600');
        }

        applySearchAndFilter();
    }

    function applySearchAndFilter() {
        const query = (document.getElementById('search-input').value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.reschedule-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const status = row.dataset.status;
            const searchData = row.dataset.search || '';

            const matchesStatus = (currentStatusFilter === 'all') || (status === currentStatusFilter);
            const matchesSearch = query === '' || searchData.includes(query);

            if (matchesStatus && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyRow = document.getElementById('empty-state-row');
        if (emptyRow) {
            emptyRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }
</script>
@endsection
