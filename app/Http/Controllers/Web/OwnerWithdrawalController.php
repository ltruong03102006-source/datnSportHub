<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OwnerWithdrawalController extends Controller
{
    public function index(): View
    {
        $owner = auth()->user();
        $wallet = $owner->wallet;

        $withdrawals = WithdrawalRequest::query()
            ->where('owner_id', $owner->id)
            ->latest()
            ->paginate(10);

        $pendingWithdrawalCount = WithdrawalRequest::query()
            ->where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->count();

        return view('owner.withdrawals.index', compact('wallet', 'withdrawals', 'pendingWithdrawalCount'));
    }

    public function create(): View|RedirectResponse
    {
        $owner = auth()->user();
        $wallet = $owner->wallet;

        if (! $wallet) {
            return redirect()
                ->route('owner.web.wallet.topup.create')
                ->with('error', 'Bạn chưa có ví để thực hiện yêu cầu rút tiền.');
        }

        $pendingWithdrawAmount = WithdrawalRequest::query()
            ->where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, (float) $wallet->balance - (float) $pendingWithdrawAmount);
        // 1. THÊM DÒNG NÀY ĐỂ LẤY HẠN MỨC TỪ DB (Giống hệt hàm store):
        $minWithdraw = \App\Models\Setting::where('key', 'minimum_withdraw')->value('value') ?? 50000;
        return view('owner.withdrawals.create', compact(
            'wallet',
            'pendingWithdrawAmount',
            'availableBalance', 'minWithdraw'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Lấy cấu hình Rút tối thiểu từ DB, nếu không có thì mặc định 50k
        $minWithdraw = \App\Models\Setting::where('key', 'minimum_withdraw')->value('value') ?? 50000;

        // 2. Ép rule validate theo biến $minWithdraw
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $minWithdraw],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'owner_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'amount.required' => 'Vui lòng nhập số tiền muốn rút.',
            'amount.numeric' => 'Số tiền rút không hợp lệ.',
            // 3. Render câu thông báo lỗi linh động
            'amount.min' => 'Số tiền rút tối thiểu là ' . number_format($minWithdraw, 0, ',', '.') . 'đ.',
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
            'bank_account_number.required' => 'Vui lòng nhập số tài khoản.',
            'bank_account_holder.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $owner = auth()->user();

        DB::transaction(function () use ($owner, $data): void {
            $wallet = $owner->wallet()
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => 'Bạn chưa có ví để thực hiện yêu cầu rút tiền.',
                ]);
            }

            if ((float) $wallet->balance <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Ví không có số dư khả dụng để rút.',
                ]);
            }

            $pendingWithdrawAmount = \App\Models\WithdrawalRequest::query()
                ->where('owner_id', $owner->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->sum('amount');

            $availableBalance = max(0, (float) $wallet->balance - (float) $pendingWithdrawAmount);

            if ((float) $data['amount'] > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'Số tiền rút không được lớn hơn số dư khả dụng.',
                ]);
            }

            \App\Models\WithdrawalRequest::create([
                'owner_id' => $owner->id,
                'wallet_id' => $wallet->id,
                'code' => 'WD-' . now()->format('YmdHis') . '-' . $owner->id . '-' . Str::upper(Str::random(4)),
                'amount' => $data['amount'],
                'bank_name' => $data['bank_name'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_account_holder' => $data['bank_account_holder'],
                'bank_account_no' => $data['bank_account_number'],
                'bank_account_name' => $data['bank_account_holder'],
                'owner_note' => $data['owner_note'] ?? null,
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('owner.web.withdrawals.index')
            ->with('success', 'Đã gửi yêu cầu rút tiền. Vui lòng chờ admin duyệt.');
    }
}
