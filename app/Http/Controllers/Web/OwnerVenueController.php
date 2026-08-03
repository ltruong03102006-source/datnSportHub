<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\Province;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Http\JsonResponse; // Dòng khai báo cực kỳ quan trọng vừa được thêm
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $provinces = Province::orderedByName()->get(['code', 'name']);

        return view('owner.venues.create', compact('sports', 'provinces'));
    }

    public function store(StoreVenueRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $bannerPath = $request->file('banner')->store('venues', 'public');
        $createdVenue = null;

        try {
            DB::transaction(function () use ($request, $validated, $bannerPath, &$createdVenue) {
                $venue = Venue::create([
                    'owner_id' => Auth::id(),
                    'sport_id' => $validated['sport_id'],
                    'name' => $validated['name'],
                    'address' => $validated['address'],
                    'province_code' => $validated['province_code'],
                    'ward_code' => $validated['ward_code'],
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
                        'bank_name' => $validated['bank_name'] ?? '',
                        'bank_account_number' => $validated['bank_account_number'] ?? '',
                        'bank_account_holder' => $validated['bank_account_holder'] ?? '',
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

                $createdVenue = $venue;
            });
        } catch (\Throwable $e) {
            Log::error('Owner venue creation failed.', [
                'owner_id' => Auth::id(),
                'message' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể lưu cơ sở lúc này. Vui lòng kiểm tra lại thông tin hoặc thử lại sau.',
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Không thể lưu cơ sở lúc này. Vui lòng kiểm tra lại thông tin hoặc thử lại sau.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'venue_id' => $createdVenue?->id,
                'message' => 'Tạo điểm sân thành công'
            ]);
        }

        return redirect()
            ->route('owner.web.venues.index')
            ->with('success', 'Đã gửi yêu cầu tạo cơ sở, vui lòng chờ Admin duyệt. Mã cơ sở: #' . $createdVenue?->id);
    }

  // Xử lý lưu cập nhật (Có phân luồng duyệt dữ liệu nhạy cảm)
    public function update(\App\Http\Requests\UpdateVenueRequest $request, \App\Models\Venue $venue)
    {
        $this->authorizeOwner($venue);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // ========================================================
            // PHẦN 1: QUÉT XEM CÓ THAY ĐỔI "THÔNG TIN CƠ BẢN" KHÔNG?
            // ========================================================
            $hasBasicChanges = false;

            if ($request->has('deleted_image_ids')) {
                $imagesToDelete = \App\Models\VenueImage::whereIn('id', $request->deleted_image_ids)
                                                        ->where('venue_id', $venue->id)->get();
                foreach ($imagesToDelete as $image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
                $hasBasicChanges = true;
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('venues/gallery', 'public');
                    $venue->images()->create(['image_path' => $path]);
                }
                $hasBasicChanges = true;
            }

            if ($request->hasFile('banner')) {
                if ($venue->banner) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($venue->banner);
                }
                $venue->banner = $request->file('banner')->store('venues', 'public');
                $hasBasicChanges = true;
            }

            $basicFields = ['sport_id', 'name', 'phone', 'email', 'description', 'address', 'province_code', 'ward_code', 'lat', 'lng'];
            $basicData = \Illuminate\Support\Arr::only($validated, $basicFields);
            
            $venue->fill($basicData);
            if ($venue->isDirty()) { // isDirty() giúp Laravel tự check xem dữ liệu có bị đổi khác với CSDL không
                $hasBasicChanges = true;
            }
            $venue->save();


            // ========================================================
            // PHẦN 2: QUÉT XEM CÓ THAY ĐỔI "HỒ SƠ PHÁP LÝ" KHÔNG?
            // ========================================================
            $legalFields = ['owner_name', 'citizen_id', 'business_license_number', 'bank_name', 'bank_account_number', 'bank_account_holder'];
            $fileFields = ['citizen_front_image', 'citizen_back_image', 'business_license_file', 'rental_contract_file', 'land_certificate_file'];

            $legalData = \Illuminate\Support\Arr::only($validated, $legalFields);
            $hasLegalChanges = false;
            $currentLegal = $venue->legalDocument;

            // Kiểm tra trường text
            foreach ($legalFields as $field) {
                if (trim($legalData[$field] ?? '') != trim($currentLegal->$field ?? '')) {
                    $hasLegalChanges = true;
                    break;
                }
            }

            // Kiểm tra file
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $hasLegalChanges = true;
                    // Chỉ đưa vào danh sách cần duyệt nếu Chủ sân thực sự up file MỚI
                    $legalData[$field] = $request->file($field)->store('venue-documents/temp_updates', 'public');
                }
                // (KHÔNG LÀM GÌ CẢ NẾU KHÔNG UP FILE MỚI, ĐỂ GIỮ NGUYÊN FILE CŨ TRONG DB)
            }

            if ($hasLegalChanges) {
                \App\Models\VenueUpdateRequest::where('venue_id', $venue->id)->where('status', 'pending')->delete();
                \App\Models\VenueUpdateRequest::create([
                    'venue_id' => $venue->id,
                    'requested_data' => $legalData,
                    'status' => 'pending'
                ]);
            }

            DB::commit();

            // ========================================================
            // PHẦN 3: XUẤT TÍN HIỆU THÔNG BÁO THEO 3 TRƯỜNG HỢP
            // ========================================================
            if ($hasBasicChanges && $hasLegalChanges) {
                return response()->json([
                    'success' => true, 
                    'update_type' => 'both',
                    'message' => 'Đã lưu Thông tin cơ bản. Riêng Hồ sơ pháp lý đang chờ Admin duyệt!'
                ]);
            } elseif ($hasLegalChanges) {
                return response()->json([
                    'success' => true, 
                    'update_type' => 'pending_legal',
                    'message' => 'Yêu cầu thay đổi Hồ sơ pháp lý đã được gửi và đang chờ Admin duyệt!'
                ]);
            } else {
                return response()->json([
                    'success' => true, 
                    'update_type' => 'basic',
                    'message' => 'Đã cập nhật thông tin điểm sân thành công!'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Xóa mềm (Tạm ẩn điểm sân)
   public function destroy(Venue $venue)
    {
        $this->authorizeOwner($venue);

        // ==========================
        // 1. PENDING / REJECTED => XÓA VĨNH VIỄN
        // ==========================
        if (in_array($venue->status, ['pending', 'rejected'])) {

            $courtIds = $venue->courts()->pluck('id');

            // Đã từng phát sinh booking (trong quá khứ hay tương lai) thì không cho xóa
            $hasBookings = \App\Models\Booking::whereIn('court_id', $courtIds)->exists();

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

            return back()->with('success', 'Đã xóa cơ sở thành công.');
        }

        // ==========================
        // 2. APPROVED / ACTIVE => TẠM NGỪNG
        // ==========================
        elseif (in_array($venue->status, ['approved', 'active'])) {
            
            $courtIds = $venue->courts()->pluck('id');

            // KIỂM TRA LỊCH ĐẶT TRONG TƯƠNG LAI
            $hasUpcomingBookings = \App\Models\Booking::whereIn('court_id', $courtIds)
                ->where('slot_date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed']) // Chỉ lấy lịch đang chờ hoặc đã chốt
                ->exists();

            if ($hasUpcomingBookings) {
                return back()->with(
                    'error',
                    'Không thể tạm ngừng! Cơ sở này đang có lịch đặt của khách trong tương lai. Vui lòng hoàn tất hoặc hủy lịch trước.'
                );
            }

            // Nếu không có lịch tương lai thì cho phép tạm ngừng
            $venue->update(['status' => 'inactive']);
            
            return back()->with('success', 'Đã tạm ngừng hoạt động cơ sở thành công!');
        }

        // ==========================
        // 3. CÁC TRẠNG THÁI KHÁC (Bị admin khóa...)
        // ==========================
        return back()->with('error', 'Không thể thực hiện thao tác này với trạng thái hiện tại.');
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
    public function restore($id) // Hoặc (Venue $venue) tùy cách bạn viết
    {
        $venue = \App\Models\Venue::findOrFail($id);

        // 1. Kiểm tra quyền sở hữu (Giữ nguyên bảo mật)
        if ((int) $venue->owner_id !== (int) Auth::id()) {
            return back()->with('error', 'Bạn không có quyền thao tác trên cơ sở này.');
        }

        // 2. TÌM HỢP ĐỒNG ĐÃ KÝ VÀ ĐANG TRONG THỜI GIAN HIỆU LỰC
        $validContract = \App\Models\Contract::where('venue_id', $venue->id)
            ->where('status', 'accepted')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        // 3. LOGIC MỞ LẠI THÔNG MINH
        if ($validContract) {
            // Nếu có hợp đồng hợp lệ & đã tới ngày -> Lên thẳng "Hoạt động"
            $venue->update(['status' => 'active']);
        } else {
            // Nếu hợp đồng chưa tới ngày (Chờ hiệu lực) -> Chỉ về "Đã duyệt"
            $venue->update(['status' => 'approved']);
        }

        return back()->with('success', 'Đã mở lại cơ sở thành công.');
    }
    public function edit(Venue $venue): View
    {
        $this->authorizeOwner($venue);
        $venue->load('images'); // Tải kèm thư viện ảnh
        $sports = Sport::query()->orderBy('name')->get();
        $provinces = Province::orderedByName()->get(['code', 'name']);
        return view('owner.venues.edit', compact('venue', 'sports', 'provinces'));
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
