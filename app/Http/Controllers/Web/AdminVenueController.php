<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Sport;
use App\Models\Booking;
use App\Models\VenueLegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminVenueController extends Controller
{
    /**
     * Hiển thị danh sách Cơ sở sân toàn hệ thống
     */
    public function index(Request $request): View
    {
        // 1. Thống kê số liệu (Stat Cards)
        $totalVenues = Venue::count();
        $activeVenues = Venue::where('status', 'active')->count(); // Đang kinh doanh (Đã ký HĐ)
        $approvedVenues = Venue::where('status', 'approved')->count(); // Đã duyệt hồ sơ (Chờ ký HĐ)
        $maintenanceVenues = Venue::where('status', 'pending')->count();
        $lockedVenues = Venue::where('status', 'inactive')->count();
        // 2. Lấy dữ liệu danh sách cùng khoảng giá
        $query = Venue::with(['owner', 'sport', 'images'])
            ->select('venues.*')
            ->selectSub(function($q) {
                $q->from('slot_prices')
                  ->join('time_slots', 'slot_prices.time_slot_id', '=', 'time_slots.id')
                  ->join('courts', 'time_slots.court_id', '=', 'courts.id')
                  ->whereColumn('courts.venue_id', 'venues.id')
                  ->selectRaw('MIN(slot_prices.price)');
            }, 'min_price')
            ->selectSub(function($q) {
                $q->from('slot_prices')
                  ->join('time_slots', 'slot_prices.time_slot_id', '=', 'time_slots.id')
                  ->join('courts', 'time_slots.court_id', '=', 'courts.id')
                  ->whereColumn('courts.venue_id', 'venues.id')
                  ->selectRaw('MAX(slot_prices.price)');
            }, 'max_price');

        // Lọc theo từ khóa (tên cơ sở hoặc chủ sân)
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo môn thể thao
        if ($sportId = $request->input('sport_id')) {
            $query->where('sport_id', $sportId);
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sắp xếp mới nhất
        $venues = $query->orderBy('created_at', 'desc')->paginate(15);

        // Lấy danh sách tất cả môn thể thao cho bộ lọc và form edit
        $sports = Sport::orderBy('name')->get();

        return view('admin.venues.index', compact(
            'totalVenues', 
            'activeVenues', 
            'maintenanceVenues', 
            'lockedVenues', 
            'venues',
            'sports'
        ));
    }

    /**
     * Cập nhật thông tin cơ sở sân
     */
    public function update(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sport_id' => 'required|exists:sports,id',
            'address' => 'required|string|max:255',
            'status' => 'required|in:pending,approved,active,rejected,inactive',
        ], [
            'name.required' => 'Vui lòng nhập tên sân.',
            'sport_id.required' => 'Vui lòng chọn môn thể thao.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'status.required' => 'Vui lòng chọn trạng thái.',
        ]);

        $venue->update($validated);

        return redirect()->route('admin.venues.index')->with('success', 'Cập nhật cơ sở sân thành công!');
    }

    public function approve(Venue $venue)
{
    if ($venue->status !== 'pending') {
        return back()->with('error', 'Chỉ có thể duyệt cơ sở đang chờ duyệt.');
    }

    DB::transaction(function () use ($venue) {
        $venue->update(['status' => 'approved']);

        if (Schema::hasTable('venue_legal_documents')) {
            $venue->legalDocument()->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'reject_reason' => null,
            ]);
        }
    });

    try {
        app(\App\Services\NotificationService::class)->create(
            $venue->owner_id,
            'Cơ sở đã được duyệt',
            "Cơ sở \"{$venue->name}\" và hồ sơ pháp lý đã được Admin phê duyệt thành công.",
            route('owner.web.venues.show', $venue->id),
            'owner_venue_approved'
        );
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Gửi notification cho owner thất bại: ' . $e->getMessage());
    }

    return redirect()->route('admin.venues.index')->with('success', 'Đã duyệt cơ sở thành công.');
}

    public function reject(Request $request, Venue $venue)
{
    if ($venue->status !== 'pending') {
        return back()->with('error', 'Chỉ có thể từ chối cơ sở đang chờ duyệt.');
    }

    $validated = $request->validate([
        'reject_reason' => 'required|string|min:5'
    ], [
        'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        'reject_reason.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.'
    ]);

    $venue->update([
        'status' => 'rejected'
    ]);

    if (Schema::hasTable('venue_legal_documents')) {
        $venue->legalDocument()->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'reject_reason' => $validated['reject_reason'],
        ]);
    }

    try {
        app(\App\Services\NotificationService::class)->create(
            $venue->owner_id,
            'Cơ sở bị từ chối',
            "Cơ sở \"{$venue->name}\" bị từ chối duyệt. Lý do: {$validated['reject_reason']}",
            route('owner.web.venues.edit', $venue->id),
            'owner_venue_rejected'
        );
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Gửi notification cho owner thất bại: ' . $e->getMessage());
    }

    return redirect()->route('admin.venues.index')->with('success', 'Đã từ chối cơ sở thành công.');
}

    /**
     * Xóa cơ sở sân
     */
    public function destroy(Venue $venue)
    {
        $courtIds = $venue->courts()->select('id')->pluck('id');

        // Kiểm tra xem có booking nào trong tương lai đang hoạt động không
        $hasUpcomingBookings = Booking::whereIn('court_id', $courtIds)
            ->where('slot_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasUpcomingBookings) {
            return back()->with('error', 'Không thể xóa! Cơ sở này đang có lịch đặt của khách trong tương lai.');
        }

        $venue->delete();

        return redirect()->route('admin.venues.index')->with('success', 'Đã xóa cơ sở sân thành công!');
    }
    public function documents(Venue $venue)
    {
        $hasLegalDocumentsTable = Schema::hasTable('venue_legal_documents');

        $venue->load(['owner']);

        if ($hasLegalDocumentsTable) {
            $venue->load('legalDocument');
        }

        return view(
            'admin.venues.documents',
            compact('venue', 'hasLegalDocumentsTable')
        );
    }
    /**
     * Admin duyệt yêu cầu thay đổi thông tin
     */
    public function approveUpdateReq(Request $request, \App\Models\VenueUpdateRequest $updateRequest)
    {
        if ($updateRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($updateRequest) {
            $venue = $updateRequest->venue;
            
            // Lấy cục dữ liệu JSON (đã ép kiểu mảng trong Model VenueUpdateRequest)
            $data = $updateRequest->requested_data; 

            // 1. Tách lọc và cập nhật bảng `venues` (Các thông tin cơ bản)
            $venueFields = ['name', 'address', 'province_code', 'ward_code', 'lat', 'lng', 'phone', 'email', 'description'];
            $venueData = \Illuminate\Support\Arr::only($data, $venueFields);
            if (!empty($venueData)) {
                $venue->update($venueData);
            }

            // 2. Tách lọc và cập nhật bảng `venue_legal_documents` (Hồ sơ pháp lý)
            $legalFields = [
                'owner_name', 'citizen_id', 'business_license_number', 
                'bank_name', 'bank_account_number', 'bank_account_holder', 
                'citizen_front_image', 'citizen_back_image', 'business_license_file', 
                'rental_contract_file', 'land_certificate_file'
            ];
            $legalData = \Illuminate\Support\Arr::only($data, $legalFields);
            
            if (!empty($legalData)) {
                if ($venue->legalDocument) {
                    // Nếu đã có hồ sơ -> Ghi đè cập nhật
                    $venue->legalDocument->update($legalData);
                } else {
                    // Nếu chưa có (Cơ sở mới hoàn toàn) -> Tạo mới
                    $venue->legalDocument()->create(array_merge($legalData, ['status' => 'approved']));
                }
            }

            // 3. Đánh dấu Yêu cầu này đã được duyệt xong
            $updateRequest->update(['status' => 'approved']);
        });

        try {
            app(\App\Services\NotificationService::class)->create(
                $updateRequest->venue->owner_id,
                'Hồ sơ pháp lý được phê duyệt',
                "Yêu cầu cập nhật thông tin/hồ sơ cho cơ sở \"{$updateRequest->venue->name}\" đã được Admin phê duyệt.",
                route('owner.web.venues.show', $updateRequest->venue_id),
                'owner_legal_approved'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gửi notification cho owner thất bại: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã phê duyệt và ghi đè thông tin mới cho sân thành công!');
    }

    /**
     * Admin từ chối yêu cầu thay đổi thông tin
     */
    public function rejectUpdateReq(Request $request, \App\Models\VenueUpdateRequest $updateRequest)
    {
        if ($updateRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        // Bắt buộc Admin phải nhập lý do từ chối để Chủ sân biết đường sửa
        $request->validate(['admin_note' => 'required|string|min:5'], [
            'admin_note.required' => 'Bắt buộc phải nhập lý do từ chối.',
            'admin_note.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.'
        ]);

        $updateRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        try {
            app(\App\Services\NotificationService::class)->create(
                $updateRequest->venue->owner_id,
                'Yêu cầu thay đổi thông tin bị từ chối',
                "Yêu cầu cập nhật thông tin cho cơ sở \"{$updateRequest->venue->name}\" bị từ chối. Lý do: {$request->admin_note}",
                route('owner.web.venues.edit', $updateRequest->venue_id),
                'owner_legal_rejected'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gửi notification cho owner thất bại: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã từ chối bản nháp thay đổi thông tin của cơ sở này.');
    }
}
