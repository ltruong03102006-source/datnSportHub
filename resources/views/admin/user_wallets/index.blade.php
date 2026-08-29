@extends('admin.layouts.app')

@push('styles')
<style>
    .finance-page {
        padding: 32px;
        background-color: #f8fafc;
        min-height: 100vh;
    }
    .finance-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }
    .eyebrow {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #10b981;
        margin-bottom: 4px;
    }
    .page-title {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .page-subtitle {
        font-size: 14px;
        color: #64748b;
        margin-top: 4px;
    }
    .actions {
        display: flex;
        gap: 12px;
    }
    .btn-soft {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-soft:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .btn-primary-soft {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #10b981;
        border: none;
        border-radius: 10px;
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-primary-soft:hover {
        background: #059669;
        color: #ffffff;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }
    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);
    }
    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .kpi-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
    }
    .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .bg-green { background: #dcfce7; color: #16a34a; }
    .bg-blue { background: #dbeafe; color: #2563eb; }
    .bg-purple { background: #f3e8ff; color: #9333ea; }
    .bg-red { background: #fee2e2; color: #dc2626; }

    .kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .kpi-note {
        font-size: 12px;
        color: #64748b;
    }

    /* Toolbar Filter */
    .filter-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #f1f5f9;
        margin-bottom: 28px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }
    .form-control-soft {
        height: 42px;
        padding: 0 14px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        font-size: 14px;
        color: #334155;
        outline: none;
    }
    .form-control-soft:focus {
        border-color: #10b981;
        background: #ffffff;
    }

    /* Table Container */
    .table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    .table-custom th {
        background: #f8fafc;
        padding: 14px 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    .table-custom td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #334155;
        vertical-align: middle;
    }
    .table-custom tr:hover {
        background: #f8fafc;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
    }
    .user-name {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .user-email {
        font-size: 12px;
        color: #64748b;
    }

    .badge-role-owner {
        background: #f3e8ff;
        color: #7e22ce;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    .badge-role-customer {
        background: #e0f2fe;
        color: #0369a1;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    .badge-status-active {
        background: #dcfce7;
        color: #15803d;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    .badge-status-locked {
        background: #fee2e2;
        color: #b91c1c;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }

    .balance-positive {
        font-weight: 800;
        color: #16a34a;
        font-size: 15px;
    }
    .balance-negative {
        font-weight: 800;
        color: #dc2626;
        font-size: 15px;
    }
    .balance-zero {
        font-weight: 700;
        color: #94a3b8;
        font-size: 15px;
    }
</style>
@endpush

@section('content')
@php
    $money = fn ($amount) => ((float) $amount < 0 ? '-' : '') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';
@endphp

<div class="finance-page">
    <!-- Header Page -->
    <div class="finance-header">
        <div>
            <div class="eyebrow">SportHub Admin Finance</div>
            <h2 class="page-title">Quản lý ví người dùng</h2>
            <p class="page-subtitle">Theo dõi tổng hợp số dư ví của tất cả Chủ sân và Khách hàng đặt sân trên hệ thống.</p>
        </div>

        <div class="actions">
            <a class="btn-soft" href="{{ route('admin.finance.index') }}">
                <i class="fa-solid fa-chart-line mr-2"></i>Tổng quan tài chính
            </a>
            @if(Route::has('admin.withdrawals.index'))
                <a class="btn-soft" href="{{ route('admin.withdrawals.index') }}">
                    <i class="fa-solid fa-list-check mr-2"></i>Yêu cầu rút tiền
                </a>
            @endif
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Tổng số dư tất cả ví</div>
                <div class="kpi-icon bg-green"><i class="fa-solid fa-wallet"></i></div>
            </div>
            <div class="kpi-value text-emerald-600">{{ $money($totalBalance) }}</div>
            <div class="kpi-note">Tổng <strong>{{ number_format($totalWalletsCount) }}</strong> tài khoản ví người dùng.</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Tổng ví Chủ Sân</div>
                <div class="kpi-icon bg-purple"><i class="fa-solid fa-user-tie"></i></div>
            </div>
            <div class="kpi-value text-purple-600">{{ $money($ownerTotalBalance) }}</div>
            <div class="kpi-note">Tổng <strong>{{ number_format($ownerWalletsCount) }}</strong> ví của Chủ sân.</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Tổng ví Khách Hàng</div>
                <div class="kpi-icon bg-blue"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="kpi-value text-blue-600">{{ $money($customerTotalBalance) }}</div>
            <div class="kpi-note">Tổng <strong>{{ number_format($customerWalletsCount) }}</strong> ví của Khách hàng đặt sân.</div>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="filter-card">
        <form class="filter-form" method="GET" action="{{ route('admin.user_wallets.index') }}">
            <input class="form-control-soft" style="min-width: 260px;" type="text" name="search" placeholder="Tìm theo Tên, Email, SĐT..." value="{{ $search }}">
            
            <select class="form-control-soft" name="role">
                <option value="all" @selected($role === 'all')>Tất cả vai trò</option>
                <option value="owner" @selected($role === 'owner')>Chủ sân (Owners)</option>
                <option value="customer" @selected($role === 'customer')>Khách hàng (Customers)</option>
            </select>

            <select class="form-control-soft" name="balance_type">
                <option value="all" @selected($balanceType === 'all')>Tất cả số dư</option>
                <option value="positive" @selected($balanceType === 'positive')>Số dư dương (>0đ)</option>
                <option value="debt" @selected($balanceType === 'debt')>Số dư âm nợ (<0đ)</option>
                <option value="zero" @selected($balanceType === 'zero')>Số dư bằng 0đ</option>
            </select>

            <select class="form-control-soft" name="status">
                <option value="all" @selected($status === 'all')>Tất cả trạng thái</option>
                <option value="active" @selected($status === 'active')>Đang hoạt động</option>
                <option value="locked" @selected($status === 'locked')>Đang khóa</option>
            </select>

            <select class="form-control-soft" name="sort">
                <option value="balance_desc" @selected($sort === 'balance_desc')>Số dư cao -> thấp</option>
                <option value="balance_asc" @selected($sort === 'balance_asc')>Số dư thấp -> cao</option>
                <option value="updated_desc" @selected($sort === 'updated_desc')>Mới cập nhật</option>
            </select>

            <button class="btn-primary-soft" type="submit"><i class="fa-solid fa-filter"></i> Lọc dữ liệu</button>
            <a class="btn-soft" href="{{ route('admin.user_wallets.index') }}">Xóa lọc</a>
        </form>
    </div>

    <!-- Data Table -->
    <div class="table-card">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Người dùng</th>
                    <th>Vai trò</th>
                    <th>Số dư ví</th>
                    <th>Khả dụng / Đang chờ</th>
                    <th>Trạng thái</th>
                    <th>Cập nhật gần nhất</th>
                    <th style="text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($wallets as $wallet)
                @php
                    $user = $wallet->owner;
                    $isOwner = $user?->role === 'owner';
                    $bal = (float) $wallet->balance;
                @endphp
                <tr>
                    <td>
                        <div class="user-info">
                            <img src="{{ $user?->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'User') . '&background=' . ($isOwner ? '7e22ce' : '0284c7') . '&color=fff' }}" alt="Avatar" class="user-avatar">
                            <div>
                                <div class="user-name">{{ $user?->name ?? 'N/A' }}</div>
                                <div class="user-email">{{ $user?->email }} @if($user?->phone) · {{ $user->phone }} @endif</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($isOwner)
                            <span class="badge-role-owner"><i class="fa-solid fa-user-tie me-1"></i>Chủ sân</span>
                        @else
                            <span class="badge-role-customer"><i class="fa-solid fa-user me-1"></i>Khách hàng</span>
                        @endif
                    </td>
                    <td>
                        <span class="{{ $bal > 0 ? 'balance-positive' : ($bal < 0 ? 'balance-negative' : 'balance-zero') }}">
                            {{ $money($bal) }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 13px; color: #334155;">
                            {{ $money($wallet->available_balance) }}
                        </div>
                        @if((float)$wallet->pending_balance > 0)
                            <div style="font-size: 11px; color: #94a3b8;">
                                Đang chờ: {{ $money($wallet->pending_balance) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($wallet->status === 'active' || !$wallet->status)
                            <span class="badge-status-active"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                        @else
                            <span class="badge-status-locked"><i class="fa-solid fa-lock me-1"></i>Đang khóa</span>
                        @endif
                    </td>
                    <td style="font-size: 13px; color: #64748b;">
                        {{ $wallet->updated_at ? $wallet->updated_at->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                    <td style="text-align: right;">
                        <button class="btn-soft btn-sm" type="button" onclick="openWalletDetail({{ $wallet->id }})">
                            <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử ví
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="fa-solid fa-wallet fa-2x mb-2" style="display: block;"></i>
                        Không tìm thấy ví người dùng phù hợp với bộ lọc.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($wallets->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9;">
            {{ $wallets->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Lịch sử ví người dùng -->
<div class="modal fade" id="walletDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px 24px;">
                <h5 class="modal-title" style="font-weight: 800; color: #0f172a;" id="modalUserName">Lịch sử ví người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div class="d-flex align-items-center justify-content-between p-3 mb-4" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-size: 12px; color: #64748b;" id="modalUserRole">Vai trò</div>
                        <div style="font-size: 16px; font-weight: 700; color: #0f172a;" id="modalUserEmail">email@example.com</div>
                    </div>
                    <div class="text-end">
                        <div style="font-size: 12px; color: #64748b;">Số dư ví hiện tại</div>
                        <div style="font-size: 20px; font-weight: 800; color: #16a34a;" id="modalWalletBalance">0đ</div>
                    </div>
                </div>

                <h6 style="font-weight: 700; color: #0f172a; margin-bottom: 12px;">Các giao dịch gần nhất</h6>
                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table align-middle" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Mã GD / Ngày</th>
                                <th>Loại giao dịch</th>
                                <th>Mô tả</th>
                                <th class="text-end">Số tiền</th>
                                <th class="text-end">Số dư sau</th>
                            </tr>
                        </thead>
                        <tbody id="modalTransactionsList">
                            <tr><td colspan="5" class="text-center text-muted py-3">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px;">
                <button type="button" class="btn-soft" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openWalletDetail(walletId) {
    const modalEl = document.getElementById('walletDetailModal');
    const modal = new bootstrap.Modal(modalEl);
    
    document.getElementById('modalUserName').innerText = 'Đang tải dữ liệu...';
    document.getElementById('modalUserRole').innerText = '';
    document.getElementById('modalUserEmail').innerText = '';
    document.getElementById('modalWalletBalance').innerText = '...';
    document.getElementById('modalTransactionsList').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Đang tải...</td></tr>';
    
    modal.show();

    fetch(`/admin/user-wallets/${walletId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        const w = data.wallet;
        document.getElementById('modalUserName').innerText = 'Lịch sử ví: ' + w.owner.name;
        document.getElementById('modalUserRole').innerText = 'Vai trò: ' + w.owner.role_label + ' (SĐT: ' + w.owner.phone + ')';
        document.getElementById('modalUserEmail').innerText = w.owner.email;
        document.getElementById('modalWalletBalance').innerText = w.formatted_balance;

        const listEl = document.getElementById('modalTransactionsList');
        if (data.transactions.length === 0) {
            listEl.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Chưa có giao dịch nào phát sinh.</td></tr>';
            return;
        }

        let html = '';
        data.transactions.forEach(t => {
            const isPos = t.amount > 0;
            html += `
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #334155;">${t.reference || ('#TXN-' + t.id)}</div>
                        <div style="font-size: 11px; color: #94a3b8;">${t.created_at}</div>
                    </td>
                    <td><span class="badge bg-light text-dark border">${t.type}</span></td>
                    <td style="max-width: 240px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">${t.description || '-'}</td>
                    <td class="text-end" style="font-weight: 700; color: ${isPos ? '#16a34a' : '#dc2626'};">${t.formatted_amount}</td>
                    <td class="text-end" style="color: #64748b;">${new Intl.NumberFormat('vi-VN').format(t.balance_after)}đ</td>
                </tr>
            `;
        });
        listEl.innerHTML = html;
    })
    .catch(err => {
        document.getElementById('modalTransactionsList').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Lỗi tải dữ liệu ví.</td></tr>';
    });
}
</script>
@endpush
