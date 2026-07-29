<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class AdminContractController extends Controller
{
    /**
     * Display a paginated list of contracts for admin management.
     *
     * @return View
     */
    public function index(): View
    {
        $contracts = Contract::with(['owner', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.contracts.index', compact('contracts'));
    }

    /**
     * Show the form to create a new contract.
     *
     * @return View
     */
    public function create(): View
    {
        // Load all owners for contract assignment.
        $owners = User::where('role', 'owner')->get();

        return view('admin.contracts.create', compact('owners'));
    }

    /**
     * Store a new contract into the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'max:255'],
            'content' => ['required'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'note' => ['nullable'],
        ]);

        // Generate sequential contract code with zero padding.
        $latestContract = Contract::latest('id')->first();
        $nextNumber = $latestContract ? ((int) preg_replace('/[^0-9]/', '', $latestContract->contract_code) + 1) : 1;
        $data['contract_code'] = sprintf('HD%06d', $nextNumber);
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        Contract::create($data);

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
        if (!in_array($contract->status, ['draft', 'rejected'], true)) {
            return Redirect::route('admin.contracts.index')
                ->with('error', 'Hợp đồng này không thể chỉnh sửa.');
        }

        $owners = User::where('role', 'owner')->get();

        return view('admin.contracts.edit', compact('contract', 'owners'));
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
        if (!in_array($contract->status, ['draft', 'rejected'], true)) {
            return Redirect::route('admin.contracts.index')
                ->with('error', 'Hợp đồng này không thể chỉnh sửa.');
        }

        $data = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'max:255'],
            'content' => ['required'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'note' => ['nullable'],
        ]);

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
        $contract->load(['owner', 'creator']);

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
        $contract->load(['owner', 'creator']);

        // Generate PDF directly from a Blade view.
        $pdf = Pdf::loadView('contracts.pdf', compact('contract'));

        return $pdf->download("HopDong_{$contract->contract_code}.pdf");
    }

    /**
     * Send a contract by changing its status from draft/rejected to sent.
     *
     * @param Contract $contract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Contract $contract)
    {
        if (!in_array($contract->status, ['draft', 'rejected'], true)) {
            return Redirect::route('admin.contracts.index')
                ->with('error', 'Hợp đồng này không thể gửi.');
        }

        $contract->update(['status' => 'sent']);

        return Redirect::route('admin.contracts.index')
            ->with('success', 'Hợp đồng đã được gửi thành công.');
    }
}
