<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueTransferRequest;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Đã di chuyển lên đây đúng chuẩn

class OwnerVenueTransferController extends Controller
{
    /**
     * Hiển thị form tạo yêu cầu chuyển nhượng (Dành cho Chủ cũ)
     */
    public function create(Venue $venue)
    {
        if ($venue->owner_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền truy cập cơ sở này.');
        }

        $hasPending = \App\Models\VenueTransferRequest::where('venue_id', $venue->id)
            ->whereIn('status', ['pending', 'pending_admin'])
            ->exists();

        if ($hasPending) {
            return redirect()->route('owner.web.venues.index')
                ->with('error', 'Cơ sở này đang có yêu cầu chuyển nhượng chờ xử lý. Bạn không thể tạo thêm!');
        }

        return view('owner.venues.transfer', compact('venue'));
    }

    /**
     * Xử lý lưu yêu cầu chuyển nhượng (Dành cho Chủ cũ)
     */
    public function store(StoreVenueTransferRequest $request, Venue $venue)
    {
        $receiver = User::where('email', $request->receiver_email)->first();

        VenueTransferRequest::create([
            'venue_id'      => $venue->id,
            'from_owner_id' => auth()->id(),
            'to_owner_id'   => $receiver->id,
            'status'        => 'pending', // Trạng thái chờ chủ mới phản hồi
        ]);

        return redirect()->route('owner.web.venues.index')
            ->with('success', 'Đã gửi yêu cầu chuyển nhượng thành công! Vui lòng chờ Chủ mới xác nhận.');
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $receiver = User::where('email', $request->email)->where('role', 'owner')->first();

        if (!$receiver) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản Chủ sân nào trùng khớp.']);
        }
        if ($receiver->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không thể chuyển nhượng cho chính mình.']);
        }

        return response()->json(['success' => true, 'name' => $receiver->name ?? $receiver->full_name ?? 'Chủ sân ẩn danh']);
    }

    /**
     * Hiển thị lịch sử chuyển nhượng (Của cả Chủ cũ và Chủ mới)
     */
    public function history()
    {
        $userId = auth()->id();
        
        $transfers = VenueTransferRequest::with(['venue', 'fromOwner', 'toOwner'])
            ->where('from_owner_id', $userId)
            ->orWhere('to_owner_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('owner.venues.transfers.history', compact('transfers'));
    }

    // =========================================================================
    // PHẦN XỬ LÝ CỦA CHỦ MỚI (NGƯỜI NHẬN)
    // =========================================================================

    /**
     * Hàm hiển thị Form điền pháp lý cho Chủ mới
     */
    public function showAcceptForm(VenueTransferRequest $transfer)
    {
        // Kiểm tra đúng người nhận và đúng trạng thái chưa?
        if ($transfer->to_owner_id !== auth()->id() || $transfer->status !== 'pending') {
            abort(403, 'Bạn không có quyền thực hiện thao tác này hoặc yêu cầu đã hết hạn.');
        }

        return view('owner.venues.transfers.accept_form', compact('transfer'));
    }

    /**
     * Hàm xử lý khi Chủ mới nộp Form pháp lý
     */
    public function submitAcceptForm(Request $request, VenueTransferRequest $transfer)
    {
        if ($transfer->to_owner_id !== auth()->id() || $transfer->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'citizen_id' => ['required', 'digits:12'],
            // Thay đổi: Cho phép chữ cái (a-z, A-Z) và số (0-9). Mở rộng max lên 50 để tránh lỗi độ dài.
            'business_license_number' => ['required', 'string', 'regex:/^[a-zA-Z0-9]+$/', 'max:50'],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'regex:/^[0-9]+$/', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'citizen_front_image' => ['required', 'image', 'max:5120'],
            'citizen_back_image' => ['required', 'image', 'max:5120'], 
            
            // SIẾT CHẶT PHÁP LÝ Ở ĐÂY:
            'business_license_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Bắt buộc up GPKD
            
            // Bắt buộc phải có Hợp đồng thuê HOẶC Sổ đỏ (Nếu để trống cả 2 sẽ báo lỗi)
            'rental_contract_file' => ['required_without:land_certificate_file', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'land_certificate_file' => ['required_without:rental_contract_file', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            // Thêm việt hóa câu thông báo lỗi cho 2 trường này
            'rental_contract_file.required_without' => 'Bạn phải cung cấp Hợp đồng thuê mặt bằng hoặc Sổ đỏ.',
            'land_certificate_file.required_without' => 'Bạn phải cung cấp Sổ đỏ hoặc Hợp đồng thuê mặt bằng.',
            'business_license_file.required' => 'Vui lòng tải lên Giấy phép kinh doanh.'
        ]);

        $fileFields = ['citizen_front_image', 'citizen_back_image', 'business_license_file', 'rental_contract_file', 'land_certificate_file'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('venue-documents/temp_transfers', 'public');
            }
        }

        $transfer->update([
            'status' => 'pending_admin', // Chuyển sang chờ Admin duyệt
            'receiver_data' => $validated 
        ]);

        return redirect()->route('owner.web.venues.transfers.history')
            ->with('success', 'Đã nộp thông tin liên hệ và hồ sơ pháp lý! Vui lòng chờ Admin phê duyệt để hoàn tất.');
    }
}