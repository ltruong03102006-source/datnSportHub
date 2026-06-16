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

        // ĐIỀU CHỈNH 1: Gán kết quả trả về vào biến $venue
        $venue = Venue::create([
            'owner_id' => Auth::id(),
            'sport_id' => $validated['sport_id'],
            'name' => $validated['name'],
            'address' => $validated['address'],
            'description' => $validated['description'] ?? null,
            'banner' => $bannerPath,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'status' => 'active', 
        ]);

        // ĐIỀU CHỈNH 2: Xử lý lưu Thư viện nhiều ảnh (Gallery)
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                // Lưu từng ảnh vào thư mục storage/app/public/venues/gallery
                $path = $file->store('venues/gallery', 'public');
                
                // Lưu đường dẫn vào bảng venue_images
                $venue->images()->create([
                    'image_path' => $path
                ]);
            }
        }

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

    // Xử lý lưu cập nhật
    public function update(StoreVenueRequest $request, Venue $venue)
    {
        $this->authorizeOwner($venue);
        $validated = $request->validated();

        $hasUpcomingBookings = Booking::whereIn('court_id', $venue->courts()->pluck('id'))
            ->where('slot_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasUpcomingBookings) {
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

        // 1. Xử lý XÓA ẢNH nháp
        if ($request->has('deleted_image_ids')) {
            $imagesToDelete = \App\Models\VenueImage::whereIn('id', $request->deleted_image_ids)
                                                    ->where('venue_id', $venue->id)
                                                    ->get();
            foreach ($imagesToDelete as $image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // 2. Xử lý LƯU ẢNH MỚI (Chỉ gọi 1 lần duy nhất ở đây)
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('venues/gallery', 'public');
                $venue->images()->create(['image_path' => $path]);
            }
        }

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
    public function edit(Venue $venue): View
    {
        $this->authorizeOwner($venue);
        $venue->load('images'); // Tải kèm thư viện ảnh
        $sports = Sport::query()->orderBy('name')->get();
        return view('owner.venues.edit', compact('venue', 'sports'));
    }
    // API Xóa 1 ảnh trong thư viện
    public function destroyImage($imageId)
    {
        $image = \App\Models\VenueImage::findOrFail($imageId);
        $this->authorizeOwner($image->venue); // Đảm bảo chỉ chủ sân mới được xóa ảnh của mình

        // Xóa file vật lý trong storage
        \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
        
        // Xóa trong database
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa ảnh']);
    }
}