@extends('owner.layoutOwner.app')

@section('title', 'Tạo voucher mới | SportHub')
@section('breadcrumb', 'Tạo voucher')

@section('content')

@vite(['resources/css/app.css', 'resources/js/app.js'])

<main class="mx-auto max-w-4xl px-6 py-8 font-[Inter] text-slate-800">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Tạo mã giảm giá mới
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Thiết lập mã giảm giá để thu hút khách hàng đặt sân tại cơ sở của bạn.
            </p>
        </div>
        <a href="{{ route('owner.web.vouchers.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
            &larr; Quay lại
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('owner.web.vouchers.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Tên Voucher -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Tên Voucher <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="VD: Giảm giá mùa hè 20%" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Mã Voucher -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Mã Code (Để trống để tự sinh)</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="VD: SUM20" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono uppercase text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Loại giảm giá -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Loại giảm giá <span class="text-rose-500">*</span></label>
                    <select name="discount_type" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
                        <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Theo số tiền cố định (VNĐ)</option>
                    </select>
                </div>

                <!-- Mức giảm giá -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Giá trị giảm <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="discount_value" value="{{ old('discount_value') }}" placeholder="VD: 20 hoặc 50000" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Đơn tối thiểu -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Giá trị đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_booking_value" value="{{ old('min_booking_value') }}" placeholder="0" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Mức giảm tối đa (Cho giảm phần trăm) -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Mức giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" placeholder="Để trống nếu không giới hạn" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Ngày bắt đầu -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Ngày kết thúc -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Số lượt sử dụng tối đa -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Tổng số lượt sử dụng tối đa</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" placeholder="Để trống nếu không giới hạn" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Áp dụng cơ sở -->
            <div class="border-t border-slate-100 pt-6">
                <label class="mb-3 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Áp dụng cho cơ sở</label>

                <div class="mb-4 flex items-center gap-2">
                    <input type="checkbox" id="applies_to_all_fields" name="applies_to_all_fields" value="1" {{ old('applies_to_all_fields') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="applies_to_all_fields" class="text-sm font-semibold text-slate-700">
                        Áp dụng cho tất cả cơ sở thuộc sở hữu của tôi
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                    @foreach($venues as $venue)
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" {{ is_array(old('venue_ids')) && in_array($venue->id, old('venue_ids')) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-medium text-slate-800">{{ $venue->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('owner.web.vouchers.index') }}" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-200 transition">
                    Hủy
                </a>
                <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                    Tạo voucher
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
