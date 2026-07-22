<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = WithdrawalRequest::query()
            ->with(['owner', 'wallet'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search): void {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('owner', function ($ownerQuery) use ($search): void {
                        $ownerQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $withdrawals = $query->paginate(15)->withQueryString();

        $totalPendingAmount = WithdrawalRequest::query()
            ->where('status', 'pending')
            ->sum('amount');

        $pendingCount = WithdrawalRequest::query()
            ->where('status', 'pending')
            ->count();

        $approvedCount = WithdrawalRequest::query()
            ->where('status', 'approved')
            ->count();

        $rejectedCount = WithdrawalRequest::query()
            ->where('status', 'rejected')
            ->count();

        return view('admin.withdrawals.index', compact(
            'withdrawals',
            'status',
            'search',
            'totalPendingAmount',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function show(WithdrawalRequest $withdrawal): View
    {
        $withdrawal->load(['owner', 'wallet', 'approver']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(WithdrawalRequest $withdrawal): RedirectResponse
    {
        if ($this->statusValue($withdrawal) !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        return back()->with('error', 'Chức năng duyệt rút tiền và trừ ví sẽ được xử lý ở commit tiếp theo.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'admin_note.max' => 'Ghi chú admin không được vượt quá 1000 ký tự.',
        ]);

        if ($this->statusValue($withdrawal) !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $withdrawal->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? null,
            'rejected_at' => now(),
        ]);

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Đã từ chối yêu cầu rút tiền.');
    }

    private function statusValue(WithdrawalRequest $withdrawal): string
    {
        return $withdrawal->status instanceof \BackedEnum
            ? $withdrawal->status->value
            : (string) $withdrawal->status;
    }
}
