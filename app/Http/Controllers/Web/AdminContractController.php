<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contract;
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
}
