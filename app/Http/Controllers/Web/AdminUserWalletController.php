<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class AdminUserWalletController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $role = $request->input('role', 'all');
        $status = $request->input('status', 'all');
        $balanceType = $request->input('balance_type', 'all');
        $sort = $request->input('sort', 'balance_desc');

        // BẮT ĐẦU TẠO CÁC VÍ CHO USER CHƯA CÓ VÍ
        $usersWithoutWallet = User::whereDoesntHave('wallet')->pluck('id');
        foreach ($usersWithoutWallet as $userId) {
            Wallet::firstOrCreate(
                ['owner_id' => $userId],
                [
                    'balance' => 0,
                    'available_balance' => 0,
                    'pending_balance' => 0,
                    'status' => 'active',
                    'currency' => 'VND',
                ]
            );
        }

        // TÍNH TOÁN CÁC CHỈ SỐ KPI TỔNG QUAN (Trước khi lọc)
        $totalWalletsCount = Wallet::count();
        $totalBalance = (float) Wallet::sum('balance');

        $ownerWalletsQuery = Wallet::whereHas('owner', fn ($q) => $q->where('role', 'owner'));
        $ownerWalletsCount = (clone $ownerWalletsQuery)->count();
        $ownerTotalBalance = (float) (clone $ownerWalletsQuery)->sum('balance');

        $customerWalletsQuery = Wallet::whereHas('owner', fn ($q) => $q->where('role', '!=', 'owner'));
        $customerWalletsCount = (clone $customerWalletsQuery)->count();
        $customerTotalBalance = (float) (clone $customerWalletsQuery)->sum('balance');

        $debtWalletsQuery = Wallet::where('balance', '<', 0);
        $debtWalletsCount = (clone $debtWalletsQuery)->count();
        $debtTotalAmount = abs((float) (clone $debtWalletsQuery)->sum('balance'));

        // TRUY VẤN VÍ VỚI BỘ LỌC
        $query = Wallet::query()->with([
            'owner',
            'transactions' => fn ($t) => $t->latest()->limit(10),
        ]);

        // Lọc theo vai trò (Owner / Customer)
        if ($role === 'owner') {
            $query->whereHas('owner', fn ($q) => $q->where('role', 'owner'));
        } elseif ($role === 'customer') {
            $query->whereHas('owner', fn ($q) => $q->where('role', '!=', 'owner'));
        }

        // Lọc theo từ khóa tìm kiếm (Tên, Email, SĐT)
        if ($search !== '') {
            $query->whereHas('owner', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái ví
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Lọc theo kiểu số dư (Dương, Âm, Bằng 0)
        if ($balanceType === 'positive') {
            $query->where('balance', '>', 0);
        } elseif ($balanceType === 'debt') {
            $query->where('balance', '<', 0);
        } elseif ($balanceType === 'zero') {
            $query->where('balance', 0);
        }

        // Sắp xếp
        match ($sort) {
            'balance_asc' => $query->orderBy('balance', 'asc'),
            'updated_desc' => $query->orderByDesc('updated_at'),
            'created_desc' => $query->orderByDesc('created_at'),
            default => $query->orderByDesc('balance'),
        };

        $wallets = $query->paginate(20)->withQueryString();

        return view('admin.user_wallets.index', compact(
            'wallets',
            'search',
            'role',
            'status',
            'balanceType',
            'sort',
            'totalWalletsCount',
            'totalBalance',
            'ownerWalletsCount',
            'ownerTotalBalance',
            'customerWalletsCount',
            'customerTotalBalance',
            'debtWalletsCount',
            'debtTotalAmount'
        ));
    }

    public function show(Wallet $wallet): JsonResponse
    {
        $wallet->load(['owner', 'transactions' => fn ($q) => $q->latest()->limit(20)]);

        return response()->json([
            'success' => true,
            'wallet' => [
                'id' => $wallet->id,
                'owner' => [
                    'id' => $wallet->owner?->id,
                    'name' => $wallet->owner?->name ?? 'N/A',
                    'email' => $wallet->owner?->email ?? 'N/A',
                    'phone' => $wallet->owner?->phone ?? 'N/A',
                    'role' => $wallet->owner?->role ?? 'customer',
                    'role_label' => $wallet->owner?->role === 'owner' ? 'Chủ sân' : ($wallet->owner?->role === 'admin' ? 'Quản trị viên' : 'Khách hàng'),
                    'avatar' => $wallet->owner?->avatar ? asset('storage/' . $wallet->owner->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($wallet->owner?->name ?? 'U'),
                ],
                'balance' => (float) $wallet->balance,
                'formatted_balance' => number_format((float) $wallet->balance, 0, ',', '.') . 'đ',
                'available_balance' => (float) $wallet->available_balance,
                'pending_balance' => (float) $wallet->pending_balance,
                'status' => $wallet->status,
                'created_at' => $wallet->created_at?->format('d/m/Y H:i'),
                'updated_at' => $wallet->updated_at?->format('d/m/Y H:i'),
            ],
            'transactions' => $wallet->transactions->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'reference' => $txn->reference,
                    'type' => (string) ($txn->type instanceof \BackedEnum ? $txn->type->value : $txn->type),
                    'amount' => (float) $txn->amount,
                    'formatted_amount' => ((float) $txn->amount > 0 ? '+' : '') . number_format((float) $txn->amount, 0, ',', '.') . 'đ',
                    'balance_before' => (float) $txn->balance_before,
                    'balance_after' => (float) $txn->balance_after,
                    'description' => $txn->description,
                    'created_at' => $txn->created_at?->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }
}
