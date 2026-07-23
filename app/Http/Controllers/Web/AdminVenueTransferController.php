<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VenueTransferRequest;
use Illuminate\Http\Request;

class AdminVenueTransferController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu chuyển nhượng
     */
    public function index()
    {
        // Eager load các relations để tối ưu câu query, sắp xếp mới nhất lên đầu
        $transfers = VenueTransferRequest::with(['venue', 'fromOwner', 'toOwner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.venue_transfers.index', compact('transfers'));
    }
    public function show(VenueTransferRequest $transfer)
    {
        // Đã tạm ẩn load ví: 'fromOwner.wallet'
        $transfer->load(['venue', 'fromOwner', 'toOwner']);

        return view('admin.venue_transfers.show', compact('transfer'));
    }
    public function approve(VenueTransferRequest $transfer)
    {
        // Chặn thao tác nếu yêu cầu đã được xử lý (tránh Admin double-click)
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        // 1. Đổi chủ của cơ sở sang Chủ Mới
        $transfer->venue->update([
            'owner_id' => $transfer->to_owner_id
        ]);

        // 2. Cập nhật trạng thái yêu cầu thành Đã duyệt
        $transfer->update([
            'status' => 'approved'
        ]);

        return back()->with('success', 'Đã phê duyệt chuyển nhượng thành công! Cơ sở đã được đổi chủ.');
    }
}