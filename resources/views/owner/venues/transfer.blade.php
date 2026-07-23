@extends('owner.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <div class="mb-6">
        <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Quay lại danh sách
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xl font-bold text-slate-800">Yêu cầu chuyển nhượng cơ sở</h2>
            <p class="text-sm text-slate-500 mt-1">Cơ sở: <strong class="text-slate-700">{{ $venue->name }}</strong></p>
        </div>

        <div class="p-6">
            {{-- Hiển thị lỗi từ Form Request --}}
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Không thể thực hiện yêu cầu:</h3>
                            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('owner.web.venues.transfer.store', $venue->id) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="receiver_email" class="block text-sm font-medium text-slate-700 mb-2">Email chủ sân nhận chuyển nhượng <span class="text-red-500">*</span></label>
                    <input type="email" name="receiver_email" id="receiver_email" value="{{ old('receiver_email') }}" required
                           class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="Nhập email tài khoản Chủ sân mới...">
                    <p class="mt-2 text-sm text-slate-500">Người nhận phải có tài khoản Chủ sân hợp lệ trên hệ thống SportHub.</p>
                </div>

                <div class="bg-amber-50 rounded-lg p-4 mb-6 border border-amber-200">
                    <h4 class="text-sm font-semibold text-amber-800 flex items-center mb-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Lưu ý quan trọng
                    </h4>
                    <ul class="text-xs text-amber-700 list-disc list-inside space-y-1">
                        <li>Bạn không thể hoàn tác sau khi Admin đã phê duyệt.</li>
                        <li>Toàn bộ doanh thu các đơn hàng thanh toán sau thời điểm duyệt sẽ thuộc về chủ mới.</li>
                        <li>Yêu cầu tài khoản của bạn phải không có công nợ.</li>
                    </ul>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('owner.web.venues.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Hủy</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700">
                        Gửi yêu cầu kiểm duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection