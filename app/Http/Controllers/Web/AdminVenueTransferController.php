<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VenueTransferRequest;
use App\Services\TransferService;
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
    public function approve(VenueTransferRequest $transfer, TransferService $transferService)
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        try {
            // Gọi Service xử lý nghiệp vụ Core
            $transferService->approve($transfer);
            return back()->with('success', 'Đã phê duyệt chuyển nhượng thành công! Cơ sở đã được đổi chủ.');
        } catch (\Exception $e) {
            return back()->with('error', 'Hệ thống gián đoạn. Không thể duyệt: ' . $e->getMessage());
        }
    }

    /**
     * Admin từ chối yêu cầu chuyển nhượng
     */
    public function reject(Request $request, VenueTransferRequest $transfer, TransferService $transferService)
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $request->validate([
            'admin_note' => 'required|string|max:1000'
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối để gửi cho chủ sân.'
        ]);

        try {
            // Gọi Service xử lý nghiệp vụ Core
            $transferService->reject($transfer, $request->admin_note);
            return back()->with('success', 'Đã từ chối yêu cầu chuyển nhượng.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }
}