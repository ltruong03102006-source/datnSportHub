@extends('owner.layoutOwner.app')

@section('content')
<div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4 pb-4 border-b border-slate-200/60">
        <div>
            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Danh sách Voucher</h2>
            <p class="text-slate-500 text-sm mt-1">Quản lý và theo dõi các mã giảm giá của bạn.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('owner.web.vouchers.report') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-900 rounded-xl shadow-sm transition-all whitespace-nowrap">
                <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Báo cáo hiệu quả
            </a>
            <a href="{{ route('owner.web.vouchers.create') }}" 
               class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition-all whitespace-nowrap">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tạo Voucher mới
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('owner.web.vouchers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã, tên voucher..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            
            <!-- Venue Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cơ sở</label>
                <select name="venue_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Tất cả cơ sở</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                            {{ $venue->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
                <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang áp dụng</option>
                    <option value="used_up" {{ request('status') == 'used_up' ? 'selected' : '' }}>Hết lượt</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chưa kích hoạt</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Từ ngày (tạo)</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors">
                    Lọc
                </button>
                <a href="{{ route('owner.web.vouchers.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors" title="Xóa bộ lọc">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-sm text-slate-500">
                        <th class="px-6 py-4 font-medium">#</th>
                        <th class="px-6 py-4 font-medium">Mã</th>
                        <th class="px-6 py-4 font-medium">Tên</th>
                        <th class="px-6 py-4 font-medium">Giảm</th>
                        <th class="px-6 py-4 font-medium">Số lượng</th>
                        <th class="px-6 py-4 font-medium">Đã dùng/Còn lại</th>
                        <th class="px-6 py-4 font-medium">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm text-slate-700">
                    @forelse($vouchers as $index => $voucher)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">{{ $vouchers->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $voucher->code }}</td>
                            <td class="px-6 py-4">{{ $voucher->name }}</td>
                            <td class="px-6 py-4 text-emerald-600 font-medium">
                                @if($voucher->discount_type == 'percentage')
                                    {{ $voucher->discount_value + 0 }}%
                                @else
                                    {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $voucher->usage_limit ? $voucher->usage_limit : '∞' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $voucher->used_count }} / 
                                {{ $voucher->usage_limit ? ($voucher->usage_limit - $voucher->used_count) : '∞' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $now = now();
                                    $statusClass = 'bg-slate-100 text-slate-700';
                                    $statusText = 'Không xác định';

                                    if ($voucher->status === 'disabled') {
                                        $statusClass = 'bg-slate-100 text-slate-600';
                                        $statusText = 'Vô hiệu hóa';
                                    } elseif ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
                                        $statusClass = 'bg-red-100 text-red-700';
                                        $statusText = 'Hết lượt';
                                    } elseif ($voucher->end_date && $voucher->end_date < $now) {
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                        $statusText = 'Hết hạn';
                                    } elseif ($voucher->start_date && $voucher->start_date > $now) {
                                        $statusClass = 'bg-blue-100 text-blue-700';
                                        $statusText = 'Chưa kích hoạt';
                                    } else {
                                        $statusClass = 'bg-emerald-100 text-emerald-700';
                                        $statusText = 'Đang áp dụng';
                                    }
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                Không tìm thấy voucher nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
