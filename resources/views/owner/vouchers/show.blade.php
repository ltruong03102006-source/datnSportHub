@extends('owner.layoutOwner.app')

@section('title', 'Chi tiết voucher | SportHub')
@section('breadcrumb', 'Chi tiết voucher')

@section('content')

@vite(['resources/css/app.css', 'resources/js/app.js'])

<main class="mx-auto max-w-7xl px-6 py-8 font-[Inter] text-slate-800">
    <!-- Header -->
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-extrabold text-slate-900">
                    {{ $voucher->name ?? 'Mã giảm giá' }}
                </h1>
                <span class="rounded-lg bg-emerald-100 px-3 py-1 font-mono text-sm font-bold text-emerald-700">
                    {{ $voucher->code }}
                </span>
            </div>
            <p class="mt-2 text-sm text-slate-500">
                Thông tin cấu hình chi tiết và báo cáo thống kê hiệu quả sử dụng voucher.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('owner.web.vouchers.edit', $voucher->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-sm font-bold text-amber-800 hover:bg-amber-100 transition shadow-sm">
                Chỉnh sửa voucher
            </a>
            <a href="{{ route('owner.web.vouchers.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                &larr; Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Thống kê sử dụng (Stats Cards) -->
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Số lượt đã dùng -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-slate-400">Số lượt đã sử dụng</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">
                {{ number_format($statistics['used_count']) }}
            </h3>
            <p class="mt-1 text-xs text-slate-500">
                Lượt dùng thực tế
            </p>
        </div>

        <!-- Tổng tiền đã giảm -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-slate-400">Tổng số tiền giảm</p>
            <h3 class="mt-2 text-2xl font-black text-emerald-600">
                {{ number_format($statistics['total_discount'], 0, ',', '.') }}đ
            </h3>
            <p class="mt-1 text-xs text-slate-500">
                Đã tài trợ cho khách
            </p>
        </div>

        <!-- Đơn hàng cao nhất -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-slate-400">Đơn doanh thu cao nhất</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">
                {{ number_format($statistics['max_booking_revenue'], 0, ',', '.') }}đ
            </h3>
            <p class="mt-1 text-xs text-slate-500">
                Giá trị đơn lớn nhất
            </p>
        </div>

        <!-- Đơn hàng thấp nhất -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-slate-400">Đơn thấp nhất</p>
            <h3 class="mt-2 text-2xl font-black text-slate-900">
                {{ number_format($statistics['min_booking_revenue'], 0, ',', '.') }}đ
            </h3>
            <p class="mt-1 text-xs text-slate-500">
                Giá trị đơn nhỏ nhất
            </p>
        </div>

        <!-- Tỷ lệ sử dụng -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-slate-400">Tỷ lệ sử dụng</p>
            <h3 class="mt-2 text-2xl font-black text-blue-600">
                {{ !is_null($statistics['usage_rate']) ? $statistics['usage_rate'] . '%' : 'N/A' }}
            </h3>
            <p class="mt-1 text-xs text-slate-500">
                So với giới hạn
            </p>
        </div>
    </div>

    <!-- Thông tin chung & Sân áp dụng -->
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Panel Thông tin chung -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-4">
                Thông tin chung
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Mã Voucher:</span>
                    <p class="font-mono font-bold text-slate-800">{{ $voucher->code }}</p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Loại & Mức giảm:</span>
                    <p class="font-bold text-emerald-600">
                        @if(in_array($voucher->discount_type, ['percent', 'percentage']))
                            Giảm {{ (float)$voucher->discount_value }}%
                        @else
                            Giảm {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                        @endif
                    </p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Đơn hàng tối thiểu:</span>
                    <p class="font-semibold text-slate-800">
                        {{ $voucher->min_booking_value ? number_format($voucher->min_booking_value, 0, ',', '.') . 'đ' : 'Không yêu cầu' }}
                    </p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Giảm tối đa:</span>
                    <p class="font-semibold text-slate-800">
                        {{ ($voucher->max_discount_amount && (float)$voucher->max_discount_amount > 0) ? number_format($voucher->max_discount_amount, 0, ',', '.') . 'đ' : 'Không giới hạn' }}
                    </p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Thời gian bắt đầu:</span>
                    <p class="font-medium text-slate-700">
                        {{ $voucher->start_date ? $voucher->start_date->format('d/m/Y H:i') : 'Không giới hạn' }}
                    </p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Thời gian kết thúc:</span>
                    <p class="font-medium text-slate-700">
                        {{ $voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : 'Không giới hạn' }}
                    </p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Tổng giới hạn lượt dùng:</span>
                    <p class="font-medium text-slate-700">
                        {{ $voucher->usage_limit ?? 'Không giới hạn' }}
                    </p>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase text-slate-400">Trạng thái:</span>
                    <p>
                        @if($voucher->status === 'active')
                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-bold text-emerald-700">Đang áp dụng</span>
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-0.5 text-xs font-bold text-slate-600">Đã tắt / Chưa kích hoạt</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Panel Sân áp dụng -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-4">
                Cơ sở áp dụng
            </h2>

            @if($voucher->applies_to_all_fields)
                <div class="rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                    Voucher này áp dụng cho tất cả cơ sở thuộc sở hữu của bạn.
                </div>
            @elseif($voucher->venues->isNotEmpty())
                <ul class="divide-y divide-slate-100">
                    @foreach($voucher->venues as $venue)
                        <li class="py-2.5 flex items-center gap-3 text-sm font-semibold text-slate-800">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ $venue->name }}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-400">Chưa chọn cơ sở cụ thể.</p>
            @endif
        </div>
    </div>

    <!-- Danh sách đơn hàng đã sử dụng voucher -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-4 mb-4">
            Danh sách đơn hàng đã sử dụng voucher
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-extrabold uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Mã đơn hàng</th>
                        <th class="px-5 py-3.5">Người dùng</th>
                        <th class="px-5 py-3.5">Ngày đặt</th>
                        <th class="px-5 py-3.5">Sân đặt</th>
                        <th class="px-5 py-3.5 text-right">Số tiền gốc</th>
                        <th class="px-5 py-3.5 text-right">Số tiền giảm</th>
                        <th class="px-5 py-3.5 text-right">Thực thanh toán</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usedBookings as $booking)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 font-mono font-bold text-slate-800">
                                #{{ $booking['booking_id'] }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $booking['user_name'] }}
                                @if($booking['user_phone'])
                                    <span class="block text-xs font-normal text-slate-400">{{ $booking['user_phone'] }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $booking['booking_date'] }}
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ $booking['court_name'] }}
                                <span class="block text-xs text-slate-400">{{ $booking['venue_name'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-right font-medium text-slate-700">
                                {{ number_format($booking['original_amount'], 0, ',', '.') }}đ
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-rose-600">
                                -{{ number_format($booking['discount_amount'], 0, ',', '.') }}đ
                            </td>
                            <td class="px-5 py-4 text-right font-black text-emerald-600">
                                {{ number_format($booking['actual_paid_amount'], 0, ',', '.') }}đ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                Chưa có đơn hàng nào áp dụng mã giảm giá này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
