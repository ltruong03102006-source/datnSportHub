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
        $venues = Venue::where('owner_id', auth()->id())
            ->where('status', 'active')
            ->get();

        if ($venues->isEmpty()) {
            return redirect()->route('owner.web.venues.index')
                ->with('error', 'Chỉ những cơ sở ở trạng thái Hoạt động mới được phép chuyển nhượng. Bạn không có cơ sở nào đang hoạt động.');
        }

        if ($venue && $venue->exists) {
            if ($venue->owner_id !== auth()->id()) {
                abort(403, 'Bạn không có quyền truy cập cơ sở này.');
            }
            if ($venue->status !== 'active') {
                return redirect()->route('owner.web.venues.index')
                    ->with('error', 'Cơ sở "' . $venue->name . '" chưa ở trạng thái Hoạt động. Chỉ cơ sở đang Hoạt động mới được phép chuyển nhượng.');
            }
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
        $targetVenue = Venue::where('id', $venueId)
            ->where('owner_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$targetVenue) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cơ sở được chọn không tồn tại hoặc chưa ở trạng thái Hoạt động.');
        }

        $receiver = User::where('email', $request->receiver_email)->where('role', 'owner')->firstOrFail();

        $transfer = VenueTransferRequest::create([
            'venue_id'              => $targetVenue->id,
            'from_owner_id'         => auth()->id(),
            'to_owner_id'           => $receiver->id,
            'price'                 => $request->input('price', 0),
            'contract_date'         => $request->input('contract_date'),
            'contract_location'     => $request->input('contract_location'),
            'sender_data'           => [
                'owner_name' => $request->input('sender_owner_name'),
                'dob'        => $request->input('sender_dob'),
                'address'    => $request->input('sender_address'),
            ],
            'sender_signed_at'      => now(),
            'sender_signed_ip'      => $request->ip(),
            'sender_signed_account' => auth()->user()->email ?? auth()->user()->name,
            'status'                => 'draft',
        ]);

        return redirect()->route('owner.web.venues.transfers.show', $transfer->id)
            ->with('success', 'Đã tạo hợp đồng nháp thành công! Bạn có thể xem trước văn bản hợp đồng hoặc bấm "Gửi hợp đồng cho Bên B" khi đã sẵn sàng.');
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
        $userRole = strtolower(optional(auth()->user())->role ?? '');
        if ($transfer->from_owner_id !== $userId && $transfer->to_owner_id !== $userId && $userRole !== 'admin') {
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

        // Cho phép gửi khi trạng thái đang là draft hoặc pending
        if (!in_array($transfer->status, ['draft', 'pending'])) {
            return redirect()->back()
                ->with('error', 'Hợp đồng này đã được gửi hoặc không còn ở trạng thái nháp.');
        }

        $transfer->update([
            'status' => 'sent',
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

        return redirect()->back()
            ->with('success', 'Đã gửi hợp đồng chuyển nhượng thành công đến Bên nhận!');
    }

    public function showAcceptForm(VenueTransferRequest $transfer)
    {
        if ($transfer->to_owner_id !== auth()->id()) {
            return redirect()->route('owner.web.venues.transfers.history')
                ->with('error', 'Form nhận chuyển nhượng này chỉ dành cho tài khoản Bên nhận (' . ($transfer->toOwner->email ?? 'Bên B') . '). Vui lòng đăng nhập đúng tài khoản Bên nhận để thực hiện.');
        }

        if (!in_array($transfer->status, ['sent', 'pending', 'draft'])) {
            return redirect()->route('owner.web.venues.transfers.history')
                ->with('error', 'Hồ sơ nhận chuyển nhượng cho cơ sở này đã được nộp hoặc không ở trạng thái chờ điền.');
        }

        return view('owner.venues.transfers.accept_form', compact('transfer'));
    }

    /**
     * Hàm xử lý khi Chủ mới nộp Form pháp lý
     */
    public function submitAcceptForm(Request $request, VenueTransferRequest $transfer)
    {
        if ($transfer->to_owner_id !== auth()->id()) {
            return redirect()->route('owner.web.venues.transfers.history')
                ->with('error', 'Bạn không có quyền nộp hồ sơ này.');
        }

        if (!in_array($transfer->status, ['sent', 'pending', 'draft'])) {
            return redirect()->route('owner.web.venues.transfers.history')
                ->with('error', 'Hồ sơ này đã được nộp trước đó.');
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:255'],
            'citizen_id' => ['required', 'digits:12'],
            'citizen_front_image' => ['required', 'image', 'max:5120'],
            'citizen_back_image' => ['required', 'image', 'max:5120'], 
        ], [
            'dob.required' => 'Vui lòng chọn ngày sinh.',
            'dob.date' => 'Ngày sinh không đúng định dạng.',
            'dob.before' => 'Ngày sinh phải là một ngày trong quá khứ.',
            'address.required' => 'Vui lòng nhập chỗ ở hiện tại.',
        ]);

        $fileFields = ['citizen_front_image', 'citizen_back_image'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('venue-documents/temp_transfers', 'public');
            }
        }

        $updateData = [
            'status' => 'filled',
            'receiver_data' => $validated 
        ];

        $transfer->update($updateData);

        return redirect()->route('owner.web.venues.transfers.show', $transfer->id)
            ->with('success', 'Đã điền thông tin và nộp hồ sơ nhận sân thành công! Vui lòng kiểm tra lại văn bản hợp đồng và bấm "KÝ HỢP ĐỒNG" để hoàn tất.');
    }

    /**
     * Xử lý Ký hợp đồng chuyển nhượng (Dành cho Bên B)
     */
    public function signContract(Request $request, VenueTransferRequest $transfer)
    {
        if ($transfer->to_owner_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện ký hợp đồng này.');
        }

        if ($transfer->receiver_signed_at && in_array($transfer->status, ['signed', 'pending_admin', 'approved'])) {
            return redirect()->back()->with('info', 'Hợp đồng này đã được bạn ký điện tử trước đó.');
        }

        // Chỉ được ký khi đã điền hồ sơ (filled) hoặc sent/pending
        if (!in_array($transfer->status, ['filled', 'sent', 'pending'])) {
            return redirect()->back()->with('error', 'Vui lòng điền thông tin hồ sơ nhận sân trước khi thực hiện ký hợp đồng.');
        }

        $transfer->update([
            'status'                  => 'signed',
            'receiver_signed_at'      => now(),
            'receiver_signed_ip'      => $request->ip(),
            'receiver_signed_account' => auth()->user()->email ?? auth()->user()->name,
        ]);

        try {
            app(\App\Services\NotificationService::class)->notifyAdminVenueTransfer($transfer);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gửi thông báo chuyển nhượng cho admin thất bại: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã ký hợp đồng điện tử thành công! Hợp đồng đã được chuyển tới Admin phê duyệt.');
    }

    /**
     * Hủy yêu cầu chuyển nhượng (Bên A hủy gửi/hủy hợp đồng hoặc Bên B từ chối nhận)
     */
    public function cancelTransfer(Request $request, VenueTransferRequest $transfer)
    {
        $userId = auth()->id();

        if ($transfer->from_owner_id !== $userId && $transfer->to_owner_id !== $userId) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện hủy hợp đồng chuyển nhượng này.');
        }

        if (in_array($transfer->status, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', 'Hợp đồng chuyển nhượng này đã kết thúc hoặc đã bị hủy trước đó.');
        }

        $reason = $request->input('reason') ?: ($transfer->from_owner_id === $userId ? 'Đã hủy chuyển nhượng bởi Bên chuyển nhượng.' : 'Đã bị từ chối bởi Bên nhận.');

        $transfer->update([
            'status' => 'rejected',
            'admin_note' => $reason,
        ]);

        return redirect()->route('owner.web.venues.transfers.history')
            ->with('success', 'Đã hủy hợp đồng chuyển nhượng thành công. Cơ sở hiện không còn trong quá trình chuyển nhượng.');
    }
}