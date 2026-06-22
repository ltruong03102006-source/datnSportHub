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
        $activeVenues = Venue::where('status', 'approved')->count();
        $maintenanceVenues = Venue::where('status', 'pending')->count(); // Dùng pending tạm cho 'Đang sửa chữa'
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
                  ->orWhereHas('owner', function($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%");
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
            'status' => 'required|in:pending,approved,rejected,inactive',
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
}
