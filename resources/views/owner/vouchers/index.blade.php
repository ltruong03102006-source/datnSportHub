@extends('owner.layoutOwner.app')

@section('title', 'Quản lý mã giảm giá | SportHub')
@section('breadcrumb', 'Mã giảm giá')

@section('content')

@vite(['resources/css/app.css', 'resources/js/app.js'])

<main class="mx-auto max-w-7xl px-6 py-8 font-[Inter] text-slate-800">
    <!-- Header -->
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Danh sách voucher của sân
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Quản lý các mã giảm giá, theo dõi trạng thái áp dụng và thống kê lượt sử dụng.
            </p>
        </div>
        <div>
            <a href="{{ route('owner.web.vouchers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tạo voucher mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bộ lọc -->
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('owner.web.vouchers.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
            <!-- Tìm kiếm code/tên -->
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Tìm kiếm</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Mã hoặc tên voucher..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <!-- Lọc theo sân -->
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Lọc theo sân</label>
                <select name="venue_id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Tất cả cơ sở</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                            {{ $venue->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Lọc theo trạng thái -->
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Trạng thái</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang áp dụng</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Hết lượt</option>
                    <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Chưa kích hoạt / Tắt</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp áp dụng</option>
                </select>
            </div>

            <!-- Lọc ngày bắt đầu -->
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Từ ngày</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <!-- Lọc ngày kết thúc & Submit -->
            <div class="flex items-end gap-2">
                <div class="w-full">
                    <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Đến ngày</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">
                    Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-extrabold uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">Mã</th>
                        <th class="px-6 py-4">Tên Voucher</th>
                        <th class="px-6 py-4">Giảm</th>
                        <th class="px-6 py-4 text-center">Số lượng</th>
                        <th class="px-6 py-4 text-center">Đã dùng/Còn lại</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vouchers as $index => $voucher)
                        @php
                            $now = \Illuminate\Support\Carbon::now();
                            $isExpired = $voucher->end_date && $voucher->end_date < $now;
                            $isUpcoming = $voucher->start_date && $voucher->start_date > $now;
                            $isOutOfStock = !is_null($voucher->usage_limit) && $voucher->used_count >= $voucher->usage_limit;
                            $isDisabled = $voucher->status === 'disabled';

                            if ($isDisabled) {
                                $badgeClass = 'bg-slate-100 text-slate-600';
                                $badgeLabel = 'Chưa kích hoạt';
                            } elseif ($isOutOfStock) {
                                $badgeClass = 'bg-amber-100 text-amber-700';
                                $badgeLabel = 'Hết lượt';
                            } elseif ($isExpired) {
                                $badgeClass = 'bg-rose-100 text-rose-700';
                                $badgeLabel = 'Hết hạn';
                            } elseif ($isUpcoming) {
                                $badgeClass = 'bg-blue-100 text-blue-700';
                                $badgeLabel = 'Sắp áp dụng';
                            } else {
                                $badgeClass = 'bg-emerald-100 text-emerald-700';
                                $badgeLabel = 'Đang áp dụng';
                            }

                            $remaining = is_null($voucher->usage_limit) ? '∞' : max(0, $voucher->usage_limit - $voucher->used_count);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-400">
                                {{ $vouchers->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                {{ $voucher->code }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $voucher->name ?? 'Mã giảm giá' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                @if($voucher->discount_type === 'percent')
                                    {{ (float)$voucher->discount_value }}%
                                @else
                                    {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700">
                                {{ $voucher->usage_limit ?? 'Không giới hạn' }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700">
                                <span class="text-emerald-600">{{ $voucher->used_count }}</span> / 
                                <span class="text-slate-500">{{ $remaining }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('owner.web.vouchers.show', $voucher->id) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 transition">
                                        Chi tiết
                                    </a>

                                    <a href="{{ route('owner.web.vouchers.edit', $voucher->id) }}" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-100 transition">
                                        Sửa
                                    </a>

                                    <form method="POST" action="{{ route('owner.web.vouchers.toggle-status', $voucher->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-200 transition" title="Bật/Tắt trạng thái">
                                            {{ $voucher->status === 'active' ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>

                                    @if($voucher->used_count == 0)
                                        <form method="POST" action="{{ route('owner.web.vouchers.destroy', $voucher->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 transition">
                                                Xóa
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                Không tìm thấy voucher nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
