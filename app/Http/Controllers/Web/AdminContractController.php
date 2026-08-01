<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use App\Models\Venue;
use App\Services\ContractLifecycleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminContractController extends Controller
{
    /**
     * Display a paginated list of contracts for admin management.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contract::class);

        // Load owners for the owner filter dropdown.
        $owners = User::where('role', 'owner')->orderBy('name')->get();

        $contracts = Contract::with(['owner', 'creator', 'venue'])
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($sub) use ($keyword) {
                    $sub->where('contract_code', 'like', "%{$keyword}%")
                        ->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhereHas('owner', function ($ownerQuery) use ($keyword) {
                            $ownerQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->owner_id, function ($query, $ownerId) {
                $query->where('owner_id', $ownerId);
            })
            ->when($request->start_date_from, function ($query, $startDate) {
                $query->whereDate('start_date', '>=', $startDate);
            })
            ->when($request->start_date_to, function ($query, $endDate) {
                $query->whereDate('start_date', '<=', $endDate);
            })
            ->when($request->sort === 'oldest', function ($query) {
                $query->orderBy('created_at', 'asc');
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate(10)
            ->withQueryString();

        return view('admin.contracts.index', compact('contracts', 'owners'));
    }

    /**
     * Show the form to create a new contract.
     *
     * @return View
     */
    public function create(Request $request)
    {
        $owners = \App\Models\User::where('role', 'owner')->get(); 
        
        // 1. Tìm các ID cơ sở đang bị vướng hợp đồng (Nháp, Đã gửi, hoặc Đã hiệu lực)
        $busyVenueIds = \App\Models\Contract::whereIn('status', ['draft', 'sent', 'accepted'])
                            ->whereNotNull('venue_id')
                            ->pluck('venue_id');

        // 2. Load các cơ sở hợp lệ (Bao gồm: Đã duyệt, Đang hoạt động, hoặc Tạm ngừng do chấm dứt HĐ)
        $venues = \App\Models\Venue::whereIn('status', ['approved', 'active', 'inactive'])
                                   ->whereNotIn('id', $busyVenueIds)
                                   ->get();

        return view('admin.contracts.create', compact('owners', 'venues'));
    }

    /**
     * Store a new contract into the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $this->validateContractData($request);

        $owner = User::findOrFail($data['owner_id']);
        $venue = $this->resolveVenue($data);

        DB::transaction(function () use ($data, $owner, $venue) {
            $data['contract_code'] = $this->nextContractCode();
            $data['created_by'] = Auth::id();
            $data['status'] = 'draft';
            $data['content'] = $this->renderContractContent($data, $owner, $venue);

            Contract::create($data);
        });

        return Redirect::route('admin.contracts.index')
            ->with('success', 'Tạo hợp đồng thành công.');
    }

    /**
     * Show the form to edit an existing contract.
     *
     * @param Contract $contract
     * @return View
     */
    public function edit(Contract $contract): View
    {
        $this->authorize('update', $contract);

        if (!in_array($contract->status, ['draft', 'rejected'], true)) {
            return Redirect::route('admin.contracts.index')
                ->with('error', 'Hợp đồng này không thể chỉnh sửa.');
        }

        $owners = User::where('role', 'owner')->get();
        $venues = $this->availableVenues($contract);

        return view('admin.contracts.edit', compact('contract', 'owners', 'venues'));
    }

    /**
     * Update an existing contract if it is editable.
     *
     * @param Request $request
     * @param Contract $contract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Contract $contract)
    {
        $this->authorize('update', $contract);

        if (!in_array($contract->status, ['draft', 'rejected'], true)) {
            return Redirect::route('admin.contracts.index')
                ->with('error', 'Hợp đồng này không thể chỉnh sửa.');
        }

        $data = $this->validateContractData($request);

        $owner = User::findOrFail($data['owner_id']);
        $venue = $this->resolveVenue($data);
        $data['content'] = $this->renderContractContent($data, $owner, $venue, $contract);

        // Preserve immutable fields and update editable values only.
        $contract->update($data);

        return Redirect::route('admin.contracts.index')
            ->with('success', 'Cập nhật hợp đồng thành công.');
    }

    /**
     * Display the details of a single contract.
     *
     * @param Contract $contract
     * @return View
     */
    public function show(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load(['owner', 'creator', 'venue']);

        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Export the contract to PDF for admin download.
     *
     * @param Contract $contract
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPdf(Contract $contract)
    {
        $this->authorize('download', $contract);

       // Nếu hợp đồng đã ký và có file cứng trên server -> Tải file bất biến
    if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
        return Storage::disk('public')->download($contract->pdf_path, "HopDong_{$contract->contract_code}.pdf");
    }

    // Nếu chỉ là bản nháp/đang gửi (chưa ký) -> Load động để xem thử
    $contract->load(['owner', 'creator', 'venue']);
    $pdf = Pdf::loadView('admin.contracts.partials.body', [
        'contract' => $contract,
        'owner' => $contract->owner,
        'venue' => $contract->venue,
    ]);

    return $pdf->download("HopDong_BanNhap_{$contract->contract_code}.pdf");
    }

    /**
     * Send a contract by changing its status from draft/rejected to sent.
     *
     * @param Contract $contract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Contract $contract, ContractLifecycleService $contracts)
    {
        $this->authorize('send', $contract);

        if (!in_array($contract->status, ['draft', 'rejected'], true)) {
            return Redirect::route('admin.contracts.index')
                ->with('error', 'Hợp đồng này không thể gửi.');
        }

        $contracts->send($contract);

        return Redirect::route('admin.contracts.index')
            ->with('success', 'Hợp đồng đã được gửi thành công.');
    }

    private function validateContractData(Request $request): array
    {
        return $request->validate([
            'owner_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'owner')),
            ],
            'venue_id' => [
                'nullable',
                Rule::exists('venues', 'id')->where(
                    fn ($query) => $query->where('owner_id', $request->input('owner_id'))
                ),
            ],
            'title' => ['required', 'max:255'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'note' => ['nullable'],
        ], [
            'owner_id.exists' => 'Chủ sân được chọn không hợp lệ.',
            'venue_id.exists' => 'Cơ sở được chọn không thuộc chủ sân này.',
            'venue_id.required' => 'Vui lòng chọn cơ sở liên kết cho hợp đồng.', // <--- Thêm dòng thông báo lỗi này
        ]);
    }

    private function resolveVenue(array $data): ?Venue
    {
        if (empty($data['venue_id'])) {
            return null;
        }

        return Venue::where('owner_id', $data['owner_id'])->findOrFail($data['venue_id']);
    }

    private function renderContractContent(array $data, User $owner, ?Venue $venue, ?Contract $existingContract = null): string
    {
        $contractData = new Contract(array_merge([
            'contract_code' => $existingContract?->contract_code,
            'created_by' => $existingContract?->created_by ?? Auth::id(),
            'status' => $existingContract?->status ?? 'draft',
        ], $data));

        $contractData->start_date = Carbon::parse($data['start_date']);
        $contractData->end_date = Carbon::parse($data['end_date']);
        $contractData->created_at = $existingContract?->created_at ?? now();

        return Contract::renderTemplate($contractData, $owner, $venue);
    }

    private function nextContractCode(): string
    {
        $latestContract = Contract::query()
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $nextNumber = $latestContract
            ? ((int) preg_replace('/[^0-9]/', '', $latestContract->contract_code) + 1)
            : 1;

        return sprintf('HD%06d', $nextNumber);
    }

    private function availableVenues(?Contract $contract = null)
    {
        // 1. Tìm các ID cơ sở ĐANG BỊ VƯỚNG hợp đồng (Nháp, Đã gửi, hoặc Đã hiệu lực)
        // LƯU Ý: Tuyệt đối không đưa 'terminated' vào đây để giải phóng cơ sở cũ
        $busyVenueIds = Contract::whereIn('status', ['draft', 'sent', 'accepted'])
            ->when($contract, function ($q) use ($contract) {
                // Nếu đang ở trang Edit, thì bỏ qua chính cái hợp đồng đang sửa này
                $q->where('id', '!=', $contract->id);
            })
            ->pluck('venue_id')
            ->filter()
            ->toArray();

        // 2. Chỉ hiển thị các Cơ sở chưa bị vướng hợp đồng
        return Venue::with('owner')
            // BẮT BUỘC PHẢI THÊM 'inactive' VÀO ĐÂY ĐỂ SÂN BỊ CHẤM DỨT VẪN ĐƯỢC LÊN DANH SÁCH
            ->whereIn('status', ['approved', 'active', 'inactive']) 
            ->whereNotIn('id', $busyVenueIds) 
            ->orderBy('name')
            ->get();
    }
    public function terminate(Request $request, Contract $contract)
    {
        $this->authorize('terminate', $contract);

        if ($contract->status !== 'accepted') {
            return redirect()->back()->with('error', 'Chỉ có thể chấm dứt hợp đồng đang có hiệu lực.');
        }

        // ==========================================
        // 1. KIỂM TRA LỊCH ĐẶT ĐỂ BẢO VỆ KHÁCH HÀNG
        // ==========================================
        if ($contract->venue) {
            $courtIds = $contract->venue->courts()->pluck('id');
            
            $hasUpcomingBookings = \App\Models\Booking::whereIn('court_id', $courtIds)
                ->where('slot_date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($hasUpcomingBookings) {
                return back()->with(
                    'error', 
                    'Không thể chấm dứt! Cơ sở này đang có lịch đặt của khách trong tương lai. Yêu cầu Chủ sân xử lý (hủy/hoàn tiền) trước.'
                );
            }
        }

        // 2. Validate lý do chấm dứt (Giữ nguyên của bạn)
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000']
        ], [
            'reason.required' => 'Vui lòng nhập lý do chấm dứt hợp đồng.',
            'reason.min' => 'Lý do chấm dứt phải có ít nhất 10 ký tự.'
        ]);

        // 3. Cập nhật trạng thái hợp đồng và lưu vết lý do
        $contract->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'note' => $contract->note . "\n\n[Lý do chấm dứt - " . now()->format('H:i d/m/Y') . "]\n" . $request->reason,
        ]);

        // ==========================================
        // 4. SỬA LỖI LOGIC: ĐƯA VỀ "TẠM NGỪNG", KHÔNG ĐƯA VỀ "CHỜ DUYỆT"
        // ==========================================
        if ($contract->venue) {
            $contract->venue->update(['status' => 'inactive']); 
        }

        return redirect()->back()->with('success', 'Đã chấm dứt hợp đồng thành công!');
    }
}
