@extends('owner.layoutOwner.app')

@section('content')
<div class="flex-1 p-6 lg:p-10 max-w-4xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200/60">
        <div>
            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Tạo Voucher mới</h2>
            <p class="text-slate-500 text-sm mt-1">Tạo mã giảm giá để thu hút khách hàng đặt sân.</p>
        </div>
        <a href="{{ route('owner.web.vouchers.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-sm transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 flex items-start">
            <svg class="w-5 h-5 text-emerald-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-emerald-800">Thành công</h3>
                <p class="text-sm text-emerald-700 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 flex items-start">
            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800">Lỗi</h3>
                <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Vui lòng kiểm tra lại thông tin</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('owner.web.vouchers.store') }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-8">
        @csrf

        <!-- Thông tin cơ bản -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Thông tin cơ bản</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tên voucher -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tên Voucher <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="VD: Khuyến mãi mùa hè" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors" required>
                </div>
                
                <!-- Mã voucher -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mã Voucher</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Để trống hệ thống tự tạo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors uppercase">
                    <p class="text-xs text-slate-500 mt-1">Gồm chữ và số, viết liền không dấu (VD: SUMMER2026).</p>
                </div>

                <!-- Số lượng -->
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Số lượng <span class="text-red-500">*</span></label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', 100) }}" min="1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors" required>
                </div>
            </div>
        </div>

        <!-- Thiết lập mức giảm -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Thiết lập mức giảm</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ type: '{{ old('discount_type', 'percentage') }}' }">
                <!-- Loại giảm giá -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Loại giảm giá <span class="text-red-500">*</span></label>
                    <select name="discount_type" x-model="type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors" required>
                        <option value="percentage">Giảm theo phần trăm (%)</option>
                        <option value="fixed">Giảm số tiền cố định (đ)</option>
                    </select>
                </div>

                <!-- Mức giảm -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mức giảm <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="discount_value" value="{{ old('discount_value') }}" min="0" step="any" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-slate-500 font-medium" x-text="type == 'percentage' ? '%' : 'đ'"></span>
                        </div>
                    </div>
                </div>

                <!-- Giảm tối đa (Chỉ hiển thị khi chọn loại %) -->
                <div class="col-span-2 md:col-span-1" x-show="type == 'percentage'">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Giảm tối đa (đ)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0" placeholder="VD: 50000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    <p class="text-xs text-slate-500 mt-1">Để trống nếu không muốn giới hạn mức giảm tối đa.</p>
                </div>

                <!-- Giá trị đơn hàng tối thiểu -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Đơn hàng tối thiểu (đ)</label>
                    <input type="number" name="min_booking_value" value="{{ old('min_booking_value') }}" min="0" placeholder="VD: 100000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    <p class="text-xs text-slate-500 mt-1">Để trống nếu áp dụng cho mọi đơn hàng.</p>
                </div>
            </div>
        </div>

        <!-- Thời gian áp dụng -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Thời gian & Khung giờ</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ngày bắt đầu -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày bắt đầu <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors" required>
                </div>

                <!-- Ngày kết thúc -->
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày kết thúc <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors" required>
                </div>

                <!-- Khung giờ áp dụng -->
                <div class="col-span-2 grid grid-cols-2 gap-4 border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                    <div class="col-span-2 text-sm font-medium text-slate-700">Khung giờ áp dụng (Không bắt buộc)</div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Từ giờ</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Đến giờ</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <p class="col-span-2 text-xs text-slate-500 mt-1">Để trống nếu áp dụng cả ngày.</p>
                </div>

                <!-- Ngày trong tuần -->
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ngày áp dụng trong tuần</label>
                    @php
                        $days = [
                            '1' => 'Thứ 2', '2' => 'Thứ 3', '3' => 'Thứ 4',
                            '4' => 'Thứ 5', '5' => 'Thứ 6', '6' => 'Thứ 7', '0' => 'Chủ nhật'
                        ];
                        $oldDays = old('apply_days', array_keys($days)); // Mặc định tick hết
                    @endphp
                    <div class="flex flex-wrap gap-3">
                        @foreach($days as $key => $label)
                            <label class="inline-flex items-center cursor-pointer px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors">
                                <input type="checkbox" name="apply_days[]" value="{{ $key }}" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" {{ in_array($key, $oldDays) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Cơ sở áp dụng -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Cơ sở áp dụng <span class="text-red-500">*</span></h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-64 overflow-y-auto p-2">
                @forelse($venues as $venue)
                    <label class="flex items-start p-3 border border-slate-200 rounded-xl hover:bg-emerald-50 hover:border-emerald-200 cursor-pointer transition-colors">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" class="w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500" {{ in_array($venue->id, old('venue_ids', [])) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-bold text-slate-800">{{ $venue->name }}</span>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $venue->address }}</p>
                        </div>
                    </label>
                @empty
                    <div class="col-span-2 p-4 text-center text-red-600 bg-red-50 rounded-lg border border-red-200">
                        Bạn chưa có cơ sở nào để áp dụng voucher.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
            <a href="{{ route('owner.web.vouchers.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Hủy
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                Lưu Voucher
            </button>
        </div>
    </form>
</div>
@endsection
