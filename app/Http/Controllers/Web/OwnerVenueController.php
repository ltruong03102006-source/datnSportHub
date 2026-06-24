<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\JsonResponse; // Dòng khai báo cực kỳ quan trọng vừa được thêm
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $bannerPath = $request->file('banner')->store('venues', 'public');

        DB::transaction(function () use ($request, $validated, $bannerPath) {
            $venue = Venue::create([
                'owner_id' => Auth::id(),
                'sport_id' => $validated['sport_id'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'open_hours' => $validated['open_hours'] ?? null,
                'close_hours' => $validated['close_hours'] ?? null,
                'google_maps_address' => $validated['google_maps_address'],
                'description' => $validated['description'] ?? null,
                'banner' => $bannerPath,
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'status' => 'pending',
            ]);

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $venue->images()->create([
                        'image_path' => $file->store('venues/gallery', 'public'),
                    ]);
                }
            }

            if (Schema::hasTable('venue_legal_documents')) {
                $venue->legalDocument()->create([
                    'owner_name' => $validated['owner_name'],
                    'citizen_id' => $validated['citizen_id'],
                    'business_license_number' => $validated['business_license_number'],
                    'address' => $validated['address'],
                    'bank_name' => $validated['bank_name'],
                    'bank_account_number' => $validated['bank_account_number'],
                    'bank_account_holder' => $validated['bank_account_holder'],
                    'citizen_front_image' => $request->file('citizen_front_image')->store('venue-documents', 'public'),
                    'citizen_back_image' => $request->file('citizen_back_image')->store('venue-documents', 'public'),
                    'business_license_file' => $request->file('business_license_file')->store('venue-documents', 'public'),
                    'rental_contract_file' => $request->hasFile('rental_contract_file')
                        ? $request->file('rental_contract_file')->store('venue-documents', 'public')
                        : null,
                    'land_certificate_file' => $request->hasFile('land_certificate_file')
                        ? $request->file('land_certificate_file')->store('venue-documents', 'public')
                        : null,
                    'status' => 'pending',
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo điểm sân thành công'
            ]);
        }

        return redirect()
            ->route('owner.web.venues.index')
            ->with('success', 'Đã gửi yêu cầu tạo cơ sở, vui lòng chờ Admin duyệt.');
    }

    // Xử lý lưu cập nhật
    public function update(UpdateVenueRequest $request, Venue $venue)
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

    // ==========================
    // PENDING / REJECTED => XÓA
    // ==========================
    if (in_array($venue->status, ['pending', 'rejected'])) {

        $courtIds = $venue->courts()->pluck('id');

        // Đã từng phát sinh booking thì không cho xóa
        $hasBookings = Booking::whereIn('court_id', $courtIds)->exists();

        if ($hasBookings) {
            return back()->with(
                'error',
                'Không thể xóa cơ sở đã phát sinh lịch đặt.'
            );
        }

        // Xóa sân con
        $venue->courts()->delete();

        // Nếu có quan hệ ảnh
        if (method_exists($venue, 'images')) {
            $venue->images()->delete();
        }

        // Nếu có quan hệ hồ sơ pháp lý
        if (method_exists($venue, 'legalDocument')) {
            optional($venue->legalDocument)->delete();
        }

        $venue->delete();

        return back()->with(
            'success',
            'Đã xóa cơ sở thành công.'
        );
    }

    // ==========================
    // SUSPENDED => KHÔNG CHO THAO TÁC
    // ==========================
    if ($venue->status === 'suspended') {
        return back()->with(
            'error',
            'Cơ sở đang bị khóa bởi quản trị viên.'
        );
    }

    // ==========================
    // APPROVED => TẠM NGỪNG
    // ==========================
    if ($venue->status === 'approved') {

        $courtIds = $venue->courts()->pluck('id');

        $hasUpcomingBookings = Booking::whereIn('court_id', $courtIds)
            ->where('slot_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasUpcomingBookings) {
            return back()->with(
                'error',
                'Không thể tạm ngừng vì đang có lịch đặt trong tương lai.'
            );
        }

        $venue->update([
            'status' => 'inactive'
        ]);

        return back()->with(
            'success',
            'Đã tạm ngừng hoạt động cơ sở.'
        );
    }

    return back()->with(
        'error',
        'Không thể thực hiện thao tác này.'
    );
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

    $venue->update([
        'status' => 'approved'
    ]);

    return back()->with(
        'success',
        'Đã mở lại cơ sở thành công.'
    );
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
    // Đã thêm \Illuminate\Http\ để sửa lỗi thiếu thư viện Import
    public function updateRules(\Illuminate\Http\Request $request, \App\Models\Venue $venue)
    {
        // Dùng ['owner_id'] thay vì ->owner_id để VS Code hết báo đỏ
        if ($venue['owner_id'] !== Auth::id()) abort(403);

        $request->validate([
            'rules' => 'nullable|string'
        ]);

        // Dùng hàm update() thay vì gán $venue->rules = ...
        $venue->update(['rules' => $request->rules]);

        return response()->json(['message' => 'Đã lưu Nội quy cơ sở thành công!']);
    }
}
