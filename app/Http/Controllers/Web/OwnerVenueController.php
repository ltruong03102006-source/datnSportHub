<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\JsonResponse; // Dòng khai báo cực kỳ quan trọng vừa được thêm
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Booking;

class OwnerVenueController extends Controller
{
    public function index(): View
    {
        // Lấy danh sách các điểm sân của chính chủ sân đang đăng nhập (sắp xếp mới nhất lên đầu)
        $venues = Venue::where('owner_id', Auth::id())
                        ->orderByDesc('created_at')
                        ->get();

        // Truyền biến $venues sang view
        return view('owner.venues.index', compact('venues'));
    }

    public function create(): View
    {
        $sports = Sport::query()->orderBy('name')->get();

        return view('owner.venues.create', compact('sports'));
    }

    public function store(StoreVenueRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $bannerPath = null;

        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('venues', 'public');
        }

        Venue::create([
            'owner_id' => Auth::id(),
            'sport_id' => $validated['sport_id'],
            'name' => $validated['name'],
            'address' => $validated['address'],
            'description' => $validated['description'] ?? null,
            'banner' => $bannerPath,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'status' => 'active', // Trạng thái đã được đổi thành active
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo điểm sân thành công'
            ]);
        }

        return redirect()
            ->route('owner.web.venues.create')
            ->with('success', 'Venue created successfully');
    }
    // Trang chỉnh sửa điểm sân
    public function edit(Venue $venue): View
    {
        $this->authorizeOwner($venue);
        $sports = Sport::query()->orderBy('name')->get();
        return view('owner.venues.edit', compact('venue', 'sports'));
    }

    // Xử lý lưu cập nhật
    public function update(StoreVenueRequest $request, Venue $venue)
    {
        $this->authorizeOwner($venue);
        $validated = $request->validated();

        // Kiểm tra xem có đơn đặt lịch trong tương lai không
        $hasUpcomingBookings = Booking::whereIn('court_id', $venue->courts()->pluck('id'))
            ->where('slot_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        // NẾU CÓ KHÁCH ĐẶT: Chỉ cho phép sửa nhóm An toàn
        // NẾU CÓ KHÁCH ĐẶT: Chỉ cho phép sửa nhóm An toàn
if ($hasUpcomingBookings) {
    // Ép kiểu float để so sánh chính xác tọa độ
    if ($validated['address'] !== $venue->address || 
        (float)$validated['lat'] !== (float)$venue->lat || 
        (float)$validated['lng'] !== (float)$venue->lng || 
        $validated['sport_id'] != $venue->sport_id) {
        
        return response()->json([
            'success' => false, 
            'message' => 'Lỗi: Không thể thay đổi Địa chỉ, Vị trí bản đồ hoặc Môn thể thao vì sân đang có lịch đặt của khách trong tương lai!'
        ], 400);
    }
}

        // Nếu qua được bước kiểm tra trên, tiến hành lưu
        if ($request->hasFile('banner')) {
            $venue->banner = $request->file('banner')->store('venues', 'public');
        }

        $venue->update([
            'sport_id' => $validated['sport_id'],
            'name' => $validated['name'],
            'address' => $validated['address'],
            'description' => $validated['description'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    }

    // Xóa mềm (Tạm ẩn điểm sân)
    public function destroy(Venue $venue)
    {
        $this->authorizeOwner($venue);

        // Lấy danh sách ID của tất cả các sân con thuộc điểm sân này
        $courtIds = $venue->courts()->select('id')->pluck('id');

        // LOGIC MỚI: Kiểm tra xem có lịch đặt nào TRONG TƯƠNG LAI (>= hôm nay) đang chờ duyệt hoặc đã xác nhận không
        $hasUpcomingBookings = Booking::whereIn('court_id', $courtIds)
            ->where('slot_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        // Nếu có lịch đặt tương lai, chặn không cho Tạm ngừng và báo lỗi
        if ($hasUpcomingBookings) {
            return back()->with('error', 'Không thể tạm ngừng! Cơ sở này đang có lịch đặt của khách trong tương lai. Vui lòng từ chối đơn hoặc chờ khách đá xong.');
        }

        // Nếu an toàn (không có lịch tương lai), tiến hành đổi trạng thái sang inactive
        $venue->update(['status' => 'inactive']); 
        
        return back()->with('success', 'Đã tạm dừng hoạt động điểm sân này.');
    }

    // Hàm bảo mật: Kiểm tra xem user hiện tại có phải chủ của sân này không
    private function authorizeOwner(Venue $venue): void
    {
        if ((int) $venue->owner_id !== (int) Auth::id()) {
            abort(403, 'Bạn không có quyền thao tác trên sân này.');
        }
    }
    // Hiển thị trang chi tiết điểm sân (Nơi sẽ làm Task #22 - Quản lý sân con)
    public function show(Venue $venue): View
    {
        $this->authorizeOwner($venue);
        
        // Load sẵn danh sách sân con thuộc cơ sở này (để chuẩn bị cho Task #22)
        $venue->load('courts'); 

        return view('owner.venues.show', compact('venue'));
    }// Hàm khôi phục (Mở lại sân sau khi đã tạm ngừng)
    public function restore(Venue $venue)
    {
        $this->authorizeOwner($venue);

        $venue->update(['status' => 'active']); 

        return back()->with('success', 'Sân đã được mở lại và hoạt động bình thường!');
    }
}