<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VenueTransferRequest;
use App\Services\TransferService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $transfer->load(['venue.legalDocument', 'fromOwner', 'toOwner']);

        return view('admin.venue_transfers.show', compact('transfer'));
    }

    public function contract(VenueTransferRequest $transfer)
    {
        $transfer->load(['venue', 'fromOwner', 'toOwner']);

        return view('owner.venues.transfers.show', compact('transfer'));
    }

    public function approve(\App\Models\VenueTransferRequest $transfer)
    {
        if (!in_array($transfer->status, ['signed', 'pending_admin'])) {
            return back()->with('error', 'Yêu cầu này không ở trạng thái chờ duyệt (Chưa ký điện tử hoặc đã được xử lý).');
        }

        DB::beginTransaction();
        try {
            $venue = $transfer->venue;
            $newOwnerData = $transfer->receiver_data; // Lấy cục JSON chứa SĐT, Email và Pháp lý ra
            
            // 1. CẬP NHẬT THÔNG TIN CƠ BẢN CỦA SÂN
            $venue->update([
                'owner_id' => $transfer->to_owner_id, 
                'phone' => $newOwnerData['phone'] ?? $venue->phone, // Đổi sang SĐT mới
                'email' => $newOwnerData['email'] ?? $venue->email, // Đổi sang Email mới
                'status' => 'approved' // Đảm bảo sân tiếp tục hoạt động
            ]);

            // 2. LẤY HỒ SƠ PHÁP LÝ CHỦ CŨ ĐỂ KẾ THỪA FILE
            $oldLegal = $venue->legalDocument;

            // 3. TẠO HỒ SƠ PHÁP LÝ CHO CHỦ MỚI (KẾ THỪA FILE GPKD, HỢP ĐỒNG THUÊ, SỔ ĐỎ TỪ CHỦ CŨ)
            $venue->legalDocument()->create([
                'owner_name' => $newOwnerData['owner_name'] ?? $venue->name,
                'citizen_id' => $newOwnerData['citizen_id'] ?? '000000000000',
                'address' => $newOwnerData['address'] ?? $venue->address, 
                'business_license_number' => $newOwnerData['business_license_number'] ?? optional($oldLegal)->business_license_number,
                'bank_name' => optional($oldLegal)->bank_name ?? 'N/A',
                'bank_account_number' => optional($oldLegal)->bank_account_number ?? 'N/A',
                'bank_account_holder' => optional($oldLegal)->bank_account_holder ?? 'N/A',
                'citizen_front_image' => $newOwnerData['citizen_front_image'] ?? optional($oldLegal)->citizen_front_image ?? '',
                'citizen_back_image' => $newOwnerData['citizen_back_image'] ?? optional($oldLegal)->citizen_back_image ?? '',
                'business_license_file' => $newOwnerData['business_license_file'] ?? optional($oldLegal)->business_license_file,
                'rental_contract_file' => $newOwnerData['rental_contract_file'] ?? optional($oldLegal)->rental_contract_file,
                'land_certificate_file' => $newOwnerData['land_certificate_file'] ?? optional($oldLegal)->land_certificate_file,
                'status' => 'approved'
            ]);

            // Xóa hồ sơ cũ nếu có sau khi tạo mới xong
            if ($oldLegal) {
                $oldLegal->delete();
            }

            // 4. CẬP NHẬT TRẠNG THÁI YÊU CẦU
            $transfer->update(['status' => 'approved']);

            // 5. XÓA CÁC YÊU CẦU CẬP NHẬT PHÁP LÝ ĐANG TREO (NẾU CÓ)
            if (method_exists($venue, 'updateRequests')) {
                $venue->updateRequests()->delete();
            }

            DB::commit();

            return back()->with('success', 'Đã phê duyệt sang tên và cập nhật Hồ sơ pháp lý thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Admin từ chối yêu cầu chuyển nhượng
     */
    public function reject(Request $request, VenueTransferRequest $transfer)
    {
        // Chặn nếu không phải trạng thái chờ Admin
        if (!in_array($transfer->status, ['signed', 'pending_admin'])) {
            return back()->with('error', 'Yêu cầu này không ở trạng thái chờ duyệt.');
        }

        $request->validate([
            'admin_note' => 'required|string|max:1000'
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối để gửi cho chủ sân.'
        ]);

        try {
            // Xử lý cập nhật trực tiếp tại Controller (Bỏ qua TransferService cũ)
            $transfer->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note
            ]);

            return back()->with('success', 'Đã từ chối yêu cầu chuyển nhượng thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }
}