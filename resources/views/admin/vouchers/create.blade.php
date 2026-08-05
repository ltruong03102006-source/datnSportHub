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
        <a href="{{ route('admin.vouchers.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
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
        <form method="POST" action="{{ route('admin.vouchers.store') }}" class="space-y-6">
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

                <!-- Giới hạn số lần dùng/khách hàng -->
                <div>
                    <label class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Giới hạn số lần dùng/khách hàng</label>
                    <input type="number" name="max_uses_per_user" value="{{ old('max_uses_per_user') }}" placeholder="Mỗi khách được dùng tối đa bao nhiêu lần? VD: 1" min="1" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Điều kiện áp dụng theo ngày & giờ -->
            <div class="border-t border-slate-100 pt-6">
                <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-800">Điều kiện ngày & giờ áp dụng</h4>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Áp dụng theo ngày trong tuần -->
                    <div>
                        <label class="mb-3 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Ngày áp dụng trong tuần (Để trống = Áp dụng tất cả ngày)</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach([1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 0 => 'Chủ Nhật'] as $val => $label)
                                <label class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 cursor-pointer text-xs font-semibold">
                                    <input type="checkbox" name="apply_days[]" value="{{ $val }}" {{ is_array(old('apply_days')) && in_array((string)$val, old('apply_days')) ? 'checked' : '' }} class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Áp dụng theo giờ -->
                    <div>
                        <label class="mb-3 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Khung giờ áp dụng (Để trống = Cả ngày)</label>
                        <div id="time-slots-container" class="space-y-2">
                            <div class="flex items-center gap-2 time-slot-row">
                                <input type="time" name="time_slots[0][start]" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <span class="text-xs text-slate-400">đến</span>
                                <input type="time" name="time_slots[0][end]" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <button type="button" onclick="addTimeSlotRow()" class="rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 px-2.5 py-1 text-xs font-bold">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                let timeSlotIndex = 1;
                function addTimeSlotRow() {
                    const container = document.getElementById('time-slots-container');
                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-2 time-slot-row';
                    div.innerHTML = `
                        <input type="time" name="time_slots[${timeSlotIndex}][start]" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <span class="text-xs text-slate-400">đến</span>
                        <input type="time" name="time_slots[${timeSlotIndex}][end]" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <button type="button" onclick="this.parentElement.remove()" class="rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 px-2.5 py-1 text-xs font-bold">-</button>
                    `;
                    container.appendChild(div);
                    timeSlotIndex++;
                }
            </script>

            <!-- Voucher đích danh (Tùy chọn) -->
            <div class="border-t border-slate-100 pt-6">
                <label class="mb-3 block text-xs font-extrabold uppercase tracking-wider text-slate-700">Dành riêng cho khách hàng (Tùy chọn)</label>
                <div class="mb-2">
                    <input type="text" name="target_user_input" value="{{ old('target_user_input') }}" placeholder="Nhập Số điện thoại hoặc Email khách hàng" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-800 transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <p class="mt-1.5 text-[13px] text-slate-500">Nếu nhập, mã voucher này sẽ được cấp ĐỘC QUYỀN cho khách hàng này. Người khác không thể lấy mã để sử dụng.</p>
                </div>
            </div>

            <!-- Admin System Voucher note -->
            <div class="border-t border-slate-100 pt-6">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                    <p class="text-[13px] font-semibold text-emerald-800">
                        <i class="fas fa-info-circle mr-1"></i> Mã giảm giá này là System Voucher. Nó sẽ tự động áp dụng cho TẤT CẢ cơ sở/sân trên toàn hệ thống.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('admin.vouchers.index') }}" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-200 transition">
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
