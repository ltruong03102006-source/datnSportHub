@extends('layouts.owner') <!-- Thay bằng tên file layout chủ sân của bạn -->

@section('title', 'Quản lý Dịch vụ')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900">Quản lý Dịch vụ / Đồ uống</h2>
            <p class="text-sm text-zinc-500 mt-1">Quản lý các mặt hàng bán kèm tại các cơ sở của bạn.</p>
        </div>
        <button onclick="document.getElementById('modalCreateService').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-emerald-700 transition">
            + Thêm dịch vụ
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bảng danh sách dịch vụ -->
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <table class="min-w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Dịch vụ</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Cơ sở áp dụng</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Đơn giá</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Trạng thái</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-zinc-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-stone-200">
                @forelse($services as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            @if($item->image)
                                <img class="h-10 w-10 rounded object-cover border border-stone-200" src="{{ asset('storage/'.$item->image) }}" alt="">
                            @else
                                <div class="h-10 w-10 rounded bg-stone-100 border border-stone-200 flex items-center justify-center text-stone-400">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $item->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $item->unit }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 font-medium">
                        {{ $item->venue->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-emerald-600">{{ number_format($item->price, 0, ',', '.') }} đ</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('owner.services.toggle', $item->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                {{ $item->is_active ? 'Đang bán' : 'Tạm ngưng' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                        <!-- Nút Sửa -->
                        <button onclick="openEditModal({{ $item }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded"><i class="fa-solid fa-pen"></i></button>
                        
                        <!-- Nút Xóa -->
                        <form action="{{ route('owner.services.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-900 bg-rose-50 p-2 rounded"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-zinc-500">
                        Chưa có dịch vụ nào. Bấm "Thêm dịch vụ" để tạo mặt hàng đầu tiên.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm Dịch Vụ -->
<div id="modalCreateService" class="fixed inset-0 z-50 hidden bg-zinc-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-bold text-zinc-900">Thêm dịch vụ mới</h3>
                <button type="button" onclick="document.getElementById('modalCreateService').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="{{ route('owner.services.store') }}" method="POST" enctype="multipart/form-data" class="p-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Cơ sở áp dụng <span class="text-rose-500">*</span></label>
                        <select name="venue_id" class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                            @foreach($venues as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Tên dịch vụ <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" placeholder="VD: Nước khoáng Lavie" class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Giá bán (VNĐ) <span class="text-rose-500">*</span></label>
                            <input type="number" name="price" placeholder="10000" min="0" class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Đơn vị tính <span class="text-rose-500">*</span></label>
                            <input type="text" name="unit" placeholder="VD: Chai, Lố, Bộ..." class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Hình ảnh minh họa</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700">Lưu dịch vụ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Dịch Vụ (Render bằng JS) -->
<div id="modalEditService" class="fixed inset-0 z-50 hidden bg-zinc-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-bold text-zinc-900">Sửa dịch vụ</h3>
                <button type="button" onclick="document.getElementById('modalEditService').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="formEditService" method="POST" enctype="multipart/form-data" class="p-4">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Tên dịch vụ</label>
                        <input type="text" name="name" id="edit_name" class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Giá bán (VNĐ)</label>
                            <input type="number" name="price" id="edit_price" min="0" class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Đơn vị</label>
                            <input type="text" name="unit" id="edit_unit" class="w-full rounded-lg border-stone-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Cập nhật hình ảnh (Để trống nếu giữ nguyên)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700">Cập nhật thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openEditModal(service) {
        // Đổ data vào form sửa
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_price').value = parseInt(service.price);
        document.getElementById('edit_unit').value = service.unit;
        
        // Cập nhật action của form
        document.getElementById('formEditService').action = `/owner/services/${service.id}`;
        
        // Hiện modal
        document.getElementById('modalEditService').classList.remove('hidden');
    }
</script>
@endsection