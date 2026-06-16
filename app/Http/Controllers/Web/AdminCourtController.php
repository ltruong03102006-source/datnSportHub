<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminCourtController extends Controller
{
    /**
     * Hiển thị danh sách sân (courts) với các tùy chọn lọc & tìm kiếm
     */
    public function index(Request $request): View
    {
        // Xây dựng query
        $query = Court::with(['venue', 'venue.owner', 'venue.sport'])
            ->select('courts.*');

        // Tìm kiếm theo tên sân
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }
        }

        // Lọc theo cơ sở sân (venue)
        if ($venue_id = $request->input('venue_id')) {
            $query->where('venue_id', $venue_id);
        }

        // Lọc theo chủ sân
        if ($owner_id = $request->input('owner_id')) {
            $query->whereHas('venue', function ($q) use ($owner_id) {
                $q->where('owner_id', $owner_id);
            });
        }

        // Sắp xếp
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        if (in_array($sortBy, ['id', 'name', 'status', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Phân trang
        $per_page = $request->input('per_page', 15);
        $courts = $query->paginate($per_page);

        // Lấy danh sách cơ sở sân cho dropdown filter
        $venues = Venue::select('id', 'name')->orderBy('name')->get();

        // Thống kê
        $stats = [
            'total' => Court::count(),
            'active' => Court::where('status', 'active')->count(),
            'inactive' => Court::where('status', 'inactive')->count(),
        ];

        return view('admin.courts.index', compact('courts', 'venues', 'stats'));
    }

    /**
     * Xem chi tiết một sân
     */
    public function show(Court $court): View
    {
        $court->load(['venue', 'venue.owner', 'venue.sport', 'timeSlots', 'bookings']);

        return view('admin.courts.show', compact('court'));
    }

    /**
     * Toggle trạng thái sân (active ↔ inactive)
     */
    public function toggleStatus(Court $court): RedirectResponse
    {
        // Xác định trạng thái mới
        $newStatus = $court->status === 'active' ? 'inactive' : 'active';

        // Cập nhật
        $court->update(['status' => $newStatus]);

        // Thông báo
        $message = $newStatus === 'active' 
            ? "✓ Sân '{$court->name}' đã được kích hoạt thành công!"
            : "✓ Sân '{$court->name}' đã được ẩn thành công!";

        return redirect()
            ->route('admin.courts.index')
            ->with('success', $message);
    }

    /**
     * Cập nhật thông tin sân
     */
    public function update(Request $request, Court $court): RedirectResponse
    {
        // Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Tên sân không được để trống.',
            'status.required' => 'Trạng thái không được để trống.',
        ]);

        // Cập nhật
        $court->update($request->only(['name', 'status']));

        return redirect()
            ->route('admin.courts.index')
            ->with('success', "✓ Sân '{$court->name}' đã được cập nhật thành công!");
    }

    /**
     * Xóa sân
     */
    public function destroy(Court $court): RedirectResponse
    {
        $courtName = $court->name;

        // Kiểm tra có booking không
        $bookingsCount = $court->bookings()->count();

        if ($bookingsCount > 0) {
            return redirect()
                ->route('admin.courts.index')
                ->with('error', "❌ Không thể xóa sân '{$courtName}' vì còn {$bookingsCount} lịch đặt liên quan.");
        }

        $court->delete();

        return redirect()
            ->route('admin.courts.index')
            ->with('success', "✓ Sân '{$courtName}' đã được xóa thành công!");
    }

    /**
     * Batch update status (cập nhật trạng thái hàng loạt)
     */
    public function batchUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'court_ids' => 'required|array|min:1',
            'court_ids.*' => 'numeric|exists:courts,id',
            'status' => 'required|in:active,inactive',
        ]);

        $courtIds = $request->input('court_ids');
        $status = $request->input('status');

        $count = Court::whereIn('id', $courtIds)
            ->update(['status' => $status]);

        $statusText = $status === 'active' ? 'kích hoạt' : 'ẩn';

        return redirect()
            ->route('admin.courts.index')
            ->with('success', "✓ Đã {$statusText} {$count} sân thành công!");
    }
}
