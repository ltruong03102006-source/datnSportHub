<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueTransferRequest;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTransferRequest;
use Illuminate\Http\Request;

class OwnerVenueTransferController extends Controller
{
    /**
     * Hiển thị form tạo yêu cầu chuyển nhượng
     */
    public function create(Venue $venue)
    {
        // Chống truy cập trái phép
        if ($venue->owner_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền truy cập cơ sở này.');
        }

        // KIỂM TRA: Nếu đang có yêu cầu chờ duyệt thì chặn luôn không cho vào form
        $hasPending = \App\Models\VenueTransferRequest::where('venue_id', $venue->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()->route('owner.web.venues.index')
                ->with('error', 'Cơ sở này đang có yêu cầu chuyển nhượng chờ Admin duyệt. Bạn không thể tạo thêm!');
        }

        return view('owner.venues.transfer', compact('venue'));
    }

    /**
     * Xử lý lưu yêu cầu
     */
    public function store(StoreVenueTransferRequest $request, Venue $venue)
    {
        // Do đã qua bước FormRequest Validate, ta chắc chắn Email này tồn tại
        $receiver = User::where('email', $request->receiver_email)->first();

        // Tạo bản ghi lưu lịch sử
        VenueTransferRequest::create([
            'venue_id'      => $venue->id,
            'from_owner_id' => auth()->id(),
            'to_owner_id'   => $receiver->id,
            'status'        => 'pending',
        ]);

        return redirect()->route('owner.web.venues.index')
            ->with('success', 'Đã gửi yêu cầu chuyển nhượng thành công! Vui lòng chờ Admin phê duyệt.');
    }
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $receiver = User::where('email', $request->email)->where('role', 'owner')->first();

        if (!$receiver) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản Chủ sân nào trùng khớp.'
            ]);
        }

        if ($receiver->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể chuyển nhượng cho chính mình.'
            ]);
        }

        return response()->json([
            'success' => true,
            // Thay 'name' bằng cột lưu tên trong bảng users của bạn (VD: full_name, name...)
            'name' => $receiver->name ?? $receiver->full_name ?? 'Chủ sân ẩn danh' 
        ]);
    }
}