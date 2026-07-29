<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OwnerContractController extends Controller
{
    /**
     * Display a list of contracts belonging to the authenticated owner.
     *
     * @return View
     */
    public function index(): View
    {
        $contracts = Contract::with('creator')
            ->where('owner_id', Auth::id())
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
        if ($contract->owner_id !== Auth::id()) {
            abort(403);
        }

        $contract->load('creator');

        return view('owner.contracts.show', compact('contract'));
    }

    /**
     * Accept a sent contract by the authenticated owner.
     *
     * @param Contract $contract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Contract $contract)
    {
        if ($contract->owner_id !== Auth::id()) {
            abort(403);
        }

        if ($contract->status !== 'sent') {
            return redirect()->route('owner.contracts.index')
                ->with('error', 'Hợp đồng này không thể xác nhận.');
        }

        $contract->update([
            'status' => 'accepted',
            'signed_at' => now(),
        ]);

        return redirect()->route('owner.contracts.show', $contract)
            ->with('success', 'Bạn đã xác nhận hợp đồng thành công.');
    }
}
