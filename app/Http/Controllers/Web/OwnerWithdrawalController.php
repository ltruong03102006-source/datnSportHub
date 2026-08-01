<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OwnerWithdrawalController extends Controller
{
    public function index(): View
    {
        $owner = Auth::user();
        $wallet = method_exists($owner, 'getOrCreateWallet') ? $owner->getOrCreateWallet() : $owner->wallet;

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
        $owner = Auth::user();
        $wallet = method_exists($owner, 'getOrCreateWallet')
            ? $owner->getOrCreateWallet()
            : $owner->wallet;

        $pendingWithdrawAmount = WithdrawalRequest::query()
            ->where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, (float) $wallet->balance - (float) $pendingWithdrawAmount);
        $minWithdraw = \App\Models\Setting::where('key', 'minimum_withdraw')->value('value') ?? 50000;

        $bankConfigured = filled($owner->bank_name)
            && filled($owner->bank_account_no)
            && filled($owner->bank_account_name);

        if (! $bankConfigured) {
            return redirect()
                ->route('owner.web.wallet.bank.edit')
                ->with('info', 'Vui lòng thiết lập tài khoản ngân hàng trước khi tạo yêu cầu rút tiền.');
        }

        return view('owner.withdrawals.create', compact(
            'wallet',
            'pendingWithdrawAmount',
            'availableBalance',
            'minWithdraw',
            'owner',
            'bankConfigured'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Lấy cấu hình Rút tối thiểu từ DB, nếu không có thì mặc định 50k
        $minWithdraw = \App\Models\Setting::where('key', 'minimum_withdraw')->value('value') ?? 50000;

        /** @var \App\Models\User $owner */
        $owner = Auth::user();
        $bankConfigured = filled($owner->bank_name)
            && filled($owner->bank_account_no)
            && filled($owner->bank_account_name);

        // 2. Ép rule validate theo biến $minWithdraw
        $rules = [
            'amount' => ['required', 'numeric', 'min:' . $minWithdraw],
            'owner_note' => ['nullable', 'string', 'max:1000'],
        ];

        if (! $bankConfigured) {
            $rules = array_merge($rules, [
                'bank_name' => ['required', 'string', 'max:255'],
                'bank_account_number' => ['required', 'string', 'max:50'],
                'bank_account_holder' => ['required', 'string', 'max:255'],
            ]);
        }

        $data = $request->validate($rules, [
            'amount.required' => 'Vui lòng nhập số tiền muốn rút.',
            'amount.numeric' => 'Số tiền rút không hợp lệ.',
            'amount.min' => 'Số tiền rút tối thiểu là ' . number_format($minWithdraw, 0, ',', '.') . 'đ.',
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
            'bank_account_number.required' => 'Vui lòng nhập số tài khoản.',
            'bank_account_holder.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $bankName = $owner->bank_name;
        $bankAccountNumber = $owner->bank_account_no;
        $bankAccountHolder = $owner->bank_account_name;
        $saveBankInfo = $request->has('save_bank_info');

        if (! $bankConfigured) {
            $bankName = $data['bank_name'];
            $bankAccountNumber = $data['bank_account_number'];
            $bankAccountHolder = $data['bank_account_holder'];
        }

        DB::transaction(function () use ($owner, $data, $bankName, $bankAccountNumber, $bankAccountHolder, $bankConfigured, $saveBankInfo): void {
            if (! $bankConfigured && $saveBankInfo) {
                $owner->bank_name = $bankName;
                $owner->bank_account_no = $bankAccountNumber;
                $owner->bank_account_name = $bankAccountHolder;
                $owner->save();
            }
            $wallet = method_exists($owner, 'getOrCreateWallet')
                ? $owner->getOrCreateWallet()
                : $owner->wallet;

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
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'bank_account_holder' => $bankAccountHolder,
                'bank_account_no' => $bankAccountNumber,
                'bank_account_name' => $bankAccountHolder,
                'owner_note' => $data['owner_note'] ?? null,
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('owner.web.withdrawals.index')
            ->with('success', 'Đã gửi yêu cầu rút tiền. Vui lòng chờ admin duyệt.');
    }
}
