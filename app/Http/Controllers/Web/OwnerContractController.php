<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\ContractLifecycleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class OwnerContractController extends Controller
{
    /**
     * Display a list of contracts belonging to the authenticated owner.
     *
     * @return View
     */
    public function index(): View
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = Contract::with('creator')
            ->where('owner_id', Auth::id())
            ->whereIn('status', ContractLifecycleService::OWNER_VISIBLE_STATUSES)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('owner.contracts.index', compact('contracts'));
    }

    /**
     * Show detailed information for a single owner contract.
     *
     * @param Contract $contract
     * @return View
     */
    public function show(Contract $contract): View
    {
        $this->authorize('view', $contract);

        if ($contract->owner_id !== Auth::id()) {
            abort(403);
        }

        $contract->load(['creator', 'venue']);

        return view('owner.contracts.show', compact('contract'));
    }

    /**
     * Accept a sent contract by the authenticated owner.
     *
     * @param Contract $contract
     * @return \Illuminate\Http\RedirectResponse
     */
    // Thêm Request $request vào tham số
public function accept(Request $request, Contract $contract, ContractLifecycleService $contracts)
{
    $this->authorize('accept', $contract);

    if ($contract->owner_id !== Auth::id()) {
        abort(403);
    }

    if ($contract->status !== 'sent') {
        return redirect()->route('owner.contracts.index')
            ->with('error', 'Hợp đồng này không thể xác nhận.');
    }

    $result = $contracts->accept($contract, $request);
        $contract = $result['contract'];

        if ($result['expired']) {
            return redirect()->route('owner.contracts.show', $contract)
                ->with('error', 'Hợp đồng đã quá hạn hiệu lực nên không thể xác nhận.');
        }

        // =========================================================================
        // THÊM ĐOẠN CODE NÀY VÀO ĐỂ "VẼ LẠI" VĂN BẢN (HIỂN THỊ CHỮ KÝ ĐIỆN TỬ)
        // =========================================================================
        $contract->load(['owner', 'venue']); // Load chắc chắn lại dữ liệu
        
        $contract->update([
            'content' => view('admin.contracts.partials.body', [
                'contract' => $contract,
                'owner'    => $contract->owner,
                'venue'    => $contract->venue,
            ])->render()
        ]);

        // =========================================================================
        // MỞ KHÓA CHO PHÉP KINH DOANH (CHUYỂN SANG ACTIVE)
        // =========================================================================
        if ($contract->venue && $contract->start_date <= now() && $contract->end_date >= now()) {
            $contract->venue->update([
                'status' => 'active', // CHÍNH THỨC HIỂN THỊ TRÊN WEB CHO KHÁCH ĐẶT
                'commission_rate' => $contract->commission_rate // Đồng bộ mức hoa hồng từ HĐ sang Cơ sở
            ]);
        }

    $message = $result['activated_venues'] > 0
        ? 'Bạn đã xác nhận hợp đồng thành công. Cơ sở đã được kích hoạt và áp dụng mức hoa hồng trong hợp đồng.'
        : 'Bạn đã xác nhận hợp đồng thành công. Cơ sở sẽ được kích hoạt khi đến ngày bắt đầu hiệu lực.';

    return redirect()->route('owner.contracts.show', $contract)
        ->with('success', $message);
}

    /**
     * Download the contract PDF for the authenticated owner.
     *
     * @param Contract $contract
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Contract $contract)
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
     * Reject a sent contract by the authenticated owner.
     *
     * @param \Illuminate\Http\Request $request
     * @param Contract $contract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, Contract $contract)
    {
        $this->authorize('reject', $contract);

        if ($contract->owner_id !== Auth::id()) {
            abort(403);
        }

        if ($contract->status !== 'sent') {
            return redirect()->route('owner.contracts.index')
                ->with('error', 'Hợp đồng này không thể từ chối.');
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $contract->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
            'rejected_at' => now(),
        ]);

        return redirect()->route('owner.contracts.show', $contract)
            ->with('success', 'Bạn đã từ chối hợp đồng.');
    }
}
