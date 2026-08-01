<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueTransferRequest;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerVenueTransferController extends Controller
{
    /**
     * Hiển thị form tạo hợp đồng chuyển nhượng (Dành cho Chủ cũ)
     */
    public function create(Request $request, ?Venue $venue = null)
    {
        $venues = Venue::where('owner_id', auth()->id())->get();

        if ($venues->isEmpty()) {
            return redirect()->route('owner.web.venues.index')
                ->with('error', 'Bạn chưa có cơ sở nào để thực hiện chuyển nhượng.');
        }

        if ($venue && $venue->exists && $venue->owner_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền truy cập cơ sở này.');
        }

        $selectedVenueId = ($venue && $venue->exists) ? $venue->id : (int) $request->query('venue_id', $venues->first()->id);

        return view('owner.venues.transfer', compact('venues', 'selectedVenueId', 'venue'));
    }

    /**
     * Xử lý lưu hợp đồng chuyển nhượng
     */
    public function store(StoreVenueTransferRequest $request, ?Venue $venue = null)
    {
        $venueId = $request->input('venue_id') ?? optional($venue)->id;
        $targetVenue = Venue::where('id', $venueId)->where('owner_id', auth()->id())->firstOrFail();

        $receiver = User::where('email', $request->receiver_email)->where('role', 'owner')->firstOrFail();

        VenueTransferRequest::create([
            'venue_id'          => $targetVenue->id,
            'from_owner_id'     => auth()->id(),
            'to_owner_id'       => $receiver->id,
            'price'             => $request->input('price'),
            'contract_date'     => $request->input('contract_date'),
            'contract_location' => $request->input('contract_location'),
            'status'            => 'pending',
        ]);

        return redirect()->route('owner.web.venues.transfers.history')
            ->with('success', 'Đã tạo hợp đồng chuyển nhượng thành công! Hợp đồng đã được thêm vào trang quản lý.');
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

        return response()->json([
            'success' => true,
            'name' => $receiver->name ?? $receiver->full_name ?? 'Chủ sân',
            'email' => $receiver->email,
            'phone' => $receiver->phone ?? $receiver->phone_number ?? 'N/A',
            'message' => 'Email tồn tại - Hợp pháp',
        ]);
    }

    /**
     * Hiển thị trang Quản lý Hợp đồng chuyển nhượng (Của cả Chủ cũ và Chủ mới)
     */
    public function history(Request $request)
    {
        $userId = auth()->id();
        
        $query = VenueTransferRequest::with(['venue', 'fromOwner', 'toOwner'])
            ->where(function ($q) use ($userId) {
                $q->where('from_owner_id', $userId)
                  ->orWhere('to_owner_id', $userId);
            });

        // Lọc theo Trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Lọc theo Tìm kiếm
        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchType = $request->input('search_type', 'all');

            $query->where(function ($q) use ($search, $searchType) {
                if ($searchType === 'code') {
                    $cleanId = preg_replace('/[^0-9]/', '', $search);
                    if ($cleanId !== '') {
                        $q->where('id', $cleanId);
                    } else {
                        $q->where('id', $search);
                    }
                } elseif ($searchType === 'venue') {
                    $q->whereHas('venue', function ($vq) use ($search) {
                        $vq->where('name', 'like', "%{$search}%");
                    });
                } else {
                    $cleanId = preg_replace('/[^0-9]/', '', $search);
                    if ($cleanId !== '') {
                        $q->where('id', $cleanId);
                    }
                    $q->orWhereHas('venue', function ($vq) use ($search) {
                        $vq->where('name', 'like', "%{$search}%");
                    });
                }
            });
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('owner.venues.transfers.history', compact('transfers'));
    }

    /**
     * Hiển thị Chi tiết văn bản Hợp đồng chuyển nhượng cơ sở thể thao
     */
    public function show(VenueTransferRequest $transfer)
    {
        $userId = auth()->id();
        if ($transfer->from_owner_id !== $userId && $transfer->to_owner_id !== $userId && optional(auth()->user())->role !== 'admin') {
            abort(403, 'Bạn không có quyền xem hợp đồng này.');
        }

        $transfer->load(['venue', 'fromOwner', 'toOwner']);

        return view('owner.venues.transfers.show', compact('transfer'));
    }

    /**
     * Gửi thông báo Hợp đồng chuyển nhượng đến Bên nhận
     */
    public function sendNotification(VenueTransferRequest $transfer)
    {
        // Chỉ Bên A (người tạo hợp đồng) mới được gửi
        if ($transfer->from_owner_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Chỉ cho phép gửi khi chưa gửi và trạng thái còn pending
        if ($transfer->notified_at || $transfer->status !== 'pending') {
            return redirect()->route('owner.web.venues.transfers.history')
                ->with('error', 'Hợp đồng này đã được gửi hoặc không ở trạng thái hợp lệ.');
        }

        $transfer->update([
            'notified_at' => now(),
        ]);

        // Gửi thông báo trong hệ thống cho Bên B
        try {
            $toOwner = $transfer->toOwner;
            if ($toOwner) {
                $toOwner->notify(new \App\Notifications\VenueTransferContractNotification($transfer));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gửi thông báo chuyển nhượng thất bại: ' . $e->getMessage());
        }

        return redirect()->route('owner.web.venues.transfers.history')
            ->with('success', 'Đã gửi hợp đồng thành công đến Bên nhận!');
    }

    /**
     * Hàm hiển thị Form điền pháp lý cho Chủ mới
     */
    public function showAcceptForm(VenueTransferRequest $transfer)
    {
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
            'business_license_number' => ['required', 'string', 'regex:/^[a-zA-Z0-9]+$/', 'max:50'],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'regex:/^[0-9]+$/', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'citizen_front_image' => ['required', 'image', 'max:5120'],
            'citizen_back_image' => ['required', 'image', 'max:5120'], 
            'business_license_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'rental_contract_file' => ['required_without:land_certificate_file', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'land_certificate_file' => ['required_without:rental_contract_file', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
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
            'status' => 'pending_admin',
            'receiver_data' => $validated 
        ]);

        return redirect()->route('owner.web.venues.transfers.history')
            ->with('success', 'Đã nộp thông tin liên hệ và hồ sơ pháp lý! Vui lòng chờ Admin phê duyệt để hoàn tất.');
    }
}