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
}