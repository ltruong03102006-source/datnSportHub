<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OwnerServiceController extends Controller
{
    // 1. Hiển thị danh sách dịch vụ
    public function index()
    {
        $ownerId = Auth::id();
        
        // Lấy các cơ sở của chủ sân này để đưa vào Filter/Form thêm mới
        $venues = Venue::where('owner_id', $ownerId)->get();
        
        // Lấy danh sách dịch vụ thuộc các cơ sở của chủ sân
        $services = Service::whereIn('venue_id', $venues->pluck('id'))
            ->with('venue') // Load sẵn quan hệ venue để tối ưu truy vấn
            ->orderBy('created_at', 'desc')
            ->get();

        return view('owner.services.index', compact('services', 'venues'));
    }

    // 2. Lưu dịch vụ mới
    public function store(Request $request)
    {
        $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'name' => 'required|string|max:255',
            // Sửa lại dòng này: Việt hóa từ khóa và bỏ support
            'category' => 'required|string|in:do_uong,do_an,dung_cu,combo',
            'pricing_type' => 'required|string|in:retail,rental',
            // Ở CẢ HÀM store() VÀ update() BẠN ĐỀU SỬA THÀNH:
'price' => 'required|integer|min:0|max:100000000', // Chỉ nhận số nguyên, tối đa 100 triệu
            'stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Bảo mật: Kiểm tra xem venue_id này có đúng là của chủ sân đang đăng nhập không
        $venue = Venue::findOrFail($request->venue_id);
        if ($venue->owner_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thêm dịch vụ cho cơ sở này.');
        }

        if ($venue->status !== 'active') {
            return back()->with('error', 'Cơ sở chưa ký hợp đồng hợp tác với Admin (chưa ở trạng thái hoạt động) nên chưa thể thêm dịch vụ.');
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($data);

        return back()->with('success', 'Đã thêm dịch vụ mới thành công!');
    }

    // 3. Cập nhật dịch vụ
    public function update(Request $request, Service $service)
    {
        // Bảo mật: Chủ sân chỉ được sửa dịch vụ của mình
        if ($service->venue->owner_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:do_uong,do_an,dung_cu,combo', // Đã cập nhật chuẩn tiếng Việt
            'pricing_type' => 'required|string|in:retail,rental',
            // Ở CẢ HÀM store() VÀ update() BẠN ĐỀU SỬA THÀNH:
'price' => 'required|integer|min:0|max:100000000', // Chỉ nhận số nguyên, tối đa 100 triệu
            'stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ đi cho nhẹ server
            if ($service->image) Storage::disk('public')->delete($service->image);
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        return back()->with('success', 'Cập nhật dịch vụ thành công!');
    }

    // 4. Bật/Tắt trạng thái hoạt động (Thay vì xóa)
    public function toggleActive(Service $service)
    {
        if ($service->venue->owner_id !== Auth::id()) abort(403);

        $service->update(['is_active' => !$service->is_active]);

        $status = $service->is_active ? 'mở bán' : 'tạm ngưng';
        return back()->with('success', "Đã {$status} dịch vụ {$service->name}.");
    }

    // 5. Xóa dịch vụ (Chỉ cho phép xóa nếu chưa có ai mua)
    public function destroy(Service $service)
    {
        if ($service->venue->owner_id !== Auth::id()) abort(403);

        // NGHIỆP VỤ CHUẨN: Kiểm tra xem dịch vụ này đã nằm trong hóa đơn nào chưa
        if ($service->bookings()->exists()) {
            return back()->with('error', 'Không thể xóa dịch vụ này vì đã có khách hàng đặt. Vui lòng sử dụng tính năng "Tạm ngưng" để ẩn đi.');
        }

        // Nếu có ảnh thì xóa file ảnh trên server cho nhẹ dung lượng
        if ($service->image) Storage::disk('public')->delete($service->image);
        
        $service->delete();

        return back()->with('success', 'Đã xóa dịch vụ thành công!');
    }
}