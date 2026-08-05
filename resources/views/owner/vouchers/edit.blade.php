@extends('owner.layoutOwner.app')

@section('title', 'Cập nhật voucher | SportHub')
@section('breadcrumb', 'Cập nhật voucher')

@section('content')

@vite(['resources/css/app.css', 'resources/js/app.js'])

<main class="mx-auto max-w-4xl px-6 py-8 font-[Inter] text-slate-800">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Cập nhật voucher: <span class="font-mono text-emerald-600">{{ $voucher->code }}</span>
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Chỉnh sửa thông tin chi tiết voucher theo đúng ràng buộc nghiệp vụ.
            </p>
        </div>
        <a href="{{ route('owner.web.vouchers.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
            &larr; Quay lại
        </a>
    </div>

    @if($hasBeenUsed)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 flex items-start gap-3">
            <svg class="h-6 w-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-bold text-amber-900">Lưu ý quan trọng:</p>
                <p class="mt-1">
                    Voucher này đã có <strong>{{ $voucher->used_count }} lượt sử dụng</strong> trong các giao dịch đặt sân.
                    Hệ thống sẽ <strong>khóa</strong> các trường Mã voucher, Loại giảm giá, Giá trị giảm giá và Cơ sở áp dụng để tránh sai lệch dữ liệu giao dịch. Bạn chỉ được phép sửa Tên, Thời hạn và Tăng lượt sử dụng.
                </p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ session('error') }}
        </div>
    @endif

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
        <form method="POST" action="{{ route('owner.web.vouchers.update', $voucher->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Mã Voucher (KHÔNG CHO PHÉP SỬA) -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-500">Mã Voucher (Cố định)</label>
                    <input type="text" value="{{ $voucher->code }}" disabled class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-mono font-bold text-slate-600">
                </div>

                <!-- Tên Voucher (ĐƯỢC SỬA) -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Tên Voucher <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $voucher->name) }}" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Loại giảm giá -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider {{ $hasBeenUsed ? 'text-slate-500' : 'text-slate-700' }}">Loại giảm giá</label>
                    @if($hasBeenUsed)
                        <input type="text" value="{{ $voucher->discount_type === 'percent' ? 'Theo phần trăm (%)' : 'Theo số tiền cố định (VNĐ)' }}" disabled class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600">
                    @else
                        <select name="discount_type" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="percent" {{ old('discount_type', $voucher->discount_type) == 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
                            <option value="fixed" {{ old('discount_type', $voucher->discount_type) == 'fixed' ? 'selected' : '' }}>Theo số tiền cố định (VNĐ)</option>
                        </select>
                    @endif
                </div>

                <!-- Mức giảm giá -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider {{ $hasBeenUsed ? 'text-slate-500' : 'text-slate-700' }}">Giá trị giảm</label>
                    @if($hasBeenUsed)
                        <input type="text" value="{{ $voucher->discount_type === 'percent' ? (float)$voucher->discount_value . '%' : number_format($voucher->discount_value, 0, ',', '.') . 'đ' }}" disabled class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600">
                    @else
                        <input type="number" step="0.01" name="discount_value" value="{{ old('discount_value', $voucher->discount_value) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    @endif
                </div>

                <!-- Đơn tối thiểu -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Giá trị đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_booking_value" value="{{ old('min_booking_value', $voucher->min_booking_value) }}" placeholder="0" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Mức giảm tối đa -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Mức giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}" placeholder="Không giới hạn" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Ngày bắt đầu (ĐƯỢC SỬA) -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', $voucher->start_date ? $voucher->start_date->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Ngày kết thúc (ĐƯỢC SỬA) -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', $voucher->end_date ? $voucher->end_date->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <!-- Số lượt sử dụng tối đa (ĐƯỢC SỬA TĂNG THÊM) -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">
                        Tổng số lượt sử dụng 
                        @if($voucher->used_count > 0)
                            <span class="text-xs text-emerald-600">(Đã dùng: {{ $voucher->used_count }})</span>
                        @endif
                    </label>
                    <input type="number" min="{{ $voucher->used_count }}" name="usage_limit" value="{{ old('usage_limit', $voucher->usage_limit) }}" placeholder="Để trống nếu không giới hạn" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Áp dụng cơ sở -->
            <div class="border-t border-slate-100 pt-6">
                <label class="mb-3 block text-xs font-extrabold uppercase tracking-wider {{ $hasBeenUsed ? 'text-slate-500' : 'text-slate-700' }}">Áp dụng cho cơ sở</label>

                @if($hasBeenUsed)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
                        @if($voucher->applies_to_all_fields)
                            Áp dụng cho tất cả cơ sở thuộc sở hữu.
                        @else
                            {{ $voucher->venues->pluck('name')->join(', ') ?: 'Không chọn cơ sở' }}
                        @endif
                    </div>
                @else
                    @php
                        $selectedVenueIds = old('venue_ids', $voucher->venues->pluck('id')->toArray());
                    @endphp
                    <div class="mb-4 flex items-center gap-2">
                        <input type="checkbox" id="applies_to_all_fields" name="applies_to_all_fields" value="1" {{ old('applies_to_all_fields', $voucher->applies_to_all_fields) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="applies_to_all_fields" class="text-sm font-semibold text-slate-700">
                            Áp dụng cho tất cả cơ sở thuộc sở hữu của tôi
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                        @foreach($venues as $venue)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" {{ in_array($venue->id, $selectedVenueIds) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-medium text-slate-800">{{ $venue->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('owner.web.vouchers.index') }}" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-200 transition">
                    Hủy
                </a>
                <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
