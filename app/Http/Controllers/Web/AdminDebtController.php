<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Wallet;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AdminDebtController extends Controller
{
    public function index(Request $request, DebtService $debtService): View
    {
        $search = $request->query('search');
        $debtStatus = $request->query('debt_status', 'all');

        $query = Wallet::query()
            ->with('owner')
            ->orderBy('balance');

        if ($search) {
            $query->whereHas('owner', function ($ownerQuery) use ($search): void {
                $ownerQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $rows = $query->get()
            ->map(function (Wallet $wallet) use ($debtService): ?array {
                $owner = $wallet->owner;

                if (! $owner) {
                    return null;
                }

                $summary = $debtService->getOwnerDebtSummary($owner->id);

                $venuesQuery = Venue::query()->where('owner_id', $owner->id);
                $venueCount = (clone $venuesQuery)->count();
                $activeVenueCount = (clone $venuesQuery)->whereIn('status', ['active', 'approved'])->count();
                $suspendedVenueCount = (clone $venuesQuery)->where('status', 'suspended')->count();

                return [
                    'wallet' => $wallet,
                    'owner' => $owner,
                    'summary' => $summary,
                    'venue_count' => $venueCount,
                    'active_venue_count' => $activeVenueCount,
                    'suspended_venue_count' => $suspendedVenueCount,
                ];
            })
            ->filter()
            ->values();

        if ($debtStatus && $debtStatus !== 'all') {
            $rows = $rows
                ->filter(fn (array $row): bool => ($row['summary']['status'] ?? null) === $debtStatus)
                ->values();
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;

        $paginatedRows = new LengthAwarePaginator(
            $rows->forPage($currentPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $allWallets = Wallet::query()
            ->with('owner')
            ->get();

        $allSummaries = $allWallets
            ->map(function (Wallet $wallet) use ($debtService): ?array {
                return $wallet->owner
                    ? $debtService->getOwnerDebtSummary($wallet->owner->id)
                    : null;
            })
            ->filter();

        $totalDebt = $allSummaries->sum('debt_amount');
        $ownersInDebt = $allSummaries->where('is_in_debt', true)->count();
        $nearLimitCount = $allSummaries->where('status', 'warning')->count();
        $overLimitCount = $allSummaries->where('status', 'over_limit')->count();
        $totalPositiveBalance = $allWallets
            ->filter(fn (Wallet $wallet): bool => (float) $wallet->balance > 0)
            ->sum(fn (Wallet $wallet): float => (float) $wallet->balance);
        $totalWallets = $allWallets->count();

        return view('admin.debts.index', [
            'rows' => $paginatedRows,
            'search' => $search,
            'debtStatus' => $debtStatus,
            'totalDebt' => $totalDebt,
            'ownersInDebt' => $ownersInDebt,
            'nearLimitCount' => $nearLimitCount,
            'overLimitCount' => $overLimitCount,
            'totalPositiveBalance' => $totalPositiveBalance,
            'totalWallets' => $totalWallets,
        ]);
    }
}
