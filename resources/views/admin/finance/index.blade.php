@extends('admin.layouts.app')

@push('styles')
<style>
    .finance-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .finance-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .eyebrow {
        color: #059669;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .page-title {
        margin: 6px 0 0;
        color: var(--text-dark);
        font-size: 30px;
        font-weight: 900;
        letter-spacing: 0;
    }

    .page-subtitle {
        margin: 8px 0 0;
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-soft,
    .btn-primary-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-soft {
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-dark);
    }

    .btn-primary-soft {
        border: 0;
        background: #059669;
        color: #fff;
        cursor: pointer;
    }

    .finance-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .form-control-soft {
        height: 42px;
        min-width: 165px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0 13px;
        color: var(--text-dark);
        background: #fff;
        font-size: 13px;
        outline: none;
    }

    .form-control-soft:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
    }

    .range-pill {
        display: inline-flex;
        align-items: center;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }

    .owner-filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-color);
        background: #fff;
    }

    .owner-filter-bar .form-control-soft {
        min-width: 180px;
    }

    .owner-filter-search {
        flex: 1 1 260px;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .kpi-card,
    .panel {
        border: 1px solid var(--border-color);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .kpi-card {
        min-height: 132px;
        padding: 16px;
    }

    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .kpi-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 900;
    }

    .kpi-value {
        margin-top: 13px;
        color: #0f172a;
        font-size: 25px;
        font-weight: 950;
        letter-spacing: 0;
    }

    .kpi-note {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    .tone-green { color: #047857; }
    .tone-red { color: #dc2626; }
    .tone-amber { color: #d97706; }
    .tone-blue { color: #2563eb; }
    .bg-green { background: #ecfdf5; color: #047857; }
    .bg-red { background: #fef2f2; color: #dc2626; }
    .bg-amber { background: #fffbeb; color: #d97706; }
    .bg-blue { background: #eff6ff; color: #2563eb; }

    .main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(360px, .8fr);
        gap: 16px;
        align-items: start;
    }

    .finance-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 4px;
    }

    .finance-section h3 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
    }

    .finance-section p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .owner-health-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        align-items: start;
    }

    .panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--border-color);
    }

    .panel-title {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
    }

    .panel-desc {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.45;
    }

    .panel-body {
        padding: 16px 18px 18px;
    }

    .chart-canvas-wrap {
        position: relative;
        height: 318px;
    }

    .chart-empty {
        margin-top: 14px;
        padding: 12px 14px;
        border: 1px solid #fde68a;
        border-radius: 12px;
        background: #fffbeb;
        color: #92400e;
        font-size: 13px;
        font-weight: 800;
    }

    .cash-stack {
        display: grid;
        gap: 12px;
    }

    .cash-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    .cash-row strong {
        color: #0f172a;
        font-size: 13px;
    }

    .cash-row span {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .cash-value {
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
        white-space: nowrap;
    }

    .section-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .mini-stat {
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .mini-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .mini-value {
        margin-top: 8px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
    }

    .table-card {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .compact-table {
        min-width: 0;
        table-layout: fixed;
    }

    .data-table th {
        padding: 11px 14px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid var(--border-color);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .05em;
        text-align: left;
        text-transform: uppercase;
    }

    .data-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        color: #0f172a;
        font-size: 12px;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .data-table tr:last-child td {
        border-bottom: 0;
    }

    .pagination-wrapper {
        padding: 0 20px 18px;
    }

    .muted {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-status,
    .tx-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .badge-status {
        padding: 5px 9px;
    }

    .tx-badge {
        padding: 5px 9px;
        background: #f1f5f9;
        color: #475569;
    }

    .status-good { background: #dcfce7; color: #166534; }
    .status-in_debt { background: #fef3c7; color: #92400e; }
    .status-warning { background: #ffedd5; color: #c2410c; }
    .status-over_limit { background: #fee2e2; color: #b91c1c; }

    .progress-track {
        width: 100%;
        max-width: 96px;
        height: 8px;
        overflow: hidden;
        margin-top: 7px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .progress-cell {
        min-width: 92px;
    }

    .progress-status {
        margin-top: 8px;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
        background: #10b981;
    }

    .progress-warning { background: #f59e0b; }
    .progress-over { background: #ef4444; }

    @media (max-width: 1180px) {
        .kpi-grid,
        .section-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .main-grid {
            grid-template-columns: 1fr;
        }

        .owner-health-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .finance-header,
        .finance-toolbar {
            display: block;
        }

        .actions,
        .filter-form {
            justify-content: flex-start;
            margin-top: 12px;
        }

        .kpi-grid,
        .section-grid,
        .mini-grid {
            grid-template-columns: 1fr;
        }

        .form-control-soft,
        .btn-soft,
        .btn-primary-soft {
            width: 100%;
        }

        .owner-filter-bar .form-control-soft {
            min-width: 0;
        }
    }
</style>
@endpush

@section('content')
@php
    $money = fn ($amount) => ((float) $amount < 0 ? '-' : '') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';
    $signedMoney = fn ($amount) => ((float) $amount < 0 ? '-' : '+') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';
    $statusLabels = [
        'good' => 'An toàn',
        'in_debt' => 'Đang nợ',
        'warning' => 'Gần hạn mức',
        'over_limit' => 'Vượt hạn mức',
    ];
    $statusClasses = [
        'good' => 'status-good',
        'in_debt' => 'status-in_debt',
        'warning' => 'status-warning',
        'over_limit' => 'status-over_limit',
    ];
    $transactionTypeLabels = [
        'booking_income' => 'Nhận tiền booking online',
        'booking_online_credit' => 'Nhận tiền booking online',
        'commission_fee' => 'Trừ hoa hồng COD',
        'commission_cod_debit' => 'Trừ hoa hồng COD',
        'topup' => 'Nạp tiền vào ví',
        'topup_credit' => 'Nạp tiền vào ví',
        'withdraw' => 'Rút tiền',
        'withdrawal_debit' => 'Rút tiền',
        'withdrawal_rejected_refund' => 'Hoàn lại yêu cầu rút',
        'manual_adjustment' => 'Điều chỉnh thủ công',
        'adjustment' => 'Điều chỉnh thủ công',
        'refund_debit' => 'Hoàn tiền',
        'refund' => 'Hoàn tiền',
    ];
    $platformTransactionTypeLabels = [
        'customer_online_payment_in' => 'Khách thanh toán booking online',
        'owner_topup_in' => 'Owner nạp tiền',
        'owner_withdrawal_out' => 'Chi tiền owner rút',
        'customer_refund_out' => 'Hoàn tiền khách',
        'manual_credit' => 'Cộng thủ công',
        'manual_debit' => 'Trừ thủ công',
    ];
    $typeValue = fn ($transaction) => $transaction->type instanceof \BackedEnum ? $transaction->type->value : (string) $transaction->type;
    $commissionHasData = collect($commissionChartRows ?? [])->contains(fn ($row) => (float) ($row['total_commission'] ?? 0) > 0);
    $filterLabel = $dateFrom || $dateTo
        ? (($dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Từ đầu') . ' - ' . ($dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Hiện tại'))
        : 'Toàn bộ dữ liệu';
    $ownerFilterLabel = $selectedOwner
        ? ('Chủ sân: ' . $selectedOwner->name)
        : 'Tất cả chủ sân';
@endphp

<div class="finance-page">
    <div class="finance-header">
        <div>
            <div class="eyebrow">Admin Finance</div>
            <h2 class="page-title">Tổng quan tài chính</h2>
            <p class="page-subtitle">Theo dõi GMV, hoa hồng, dòng tiền ví nền tảng, ví chủ sân và công nợ.</p>
        </div>

        <div class="actions">
            @if(Route::has('admin.debts.index'))
                <a class="btn-soft" href="{{ route('admin.debts.index') }}">Công nợ owner</a>
            @endif
            @if(Route::has('admin.withdrawals.index'))
                <a class="btn-soft" href="{{ route('admin.withdrawals.index') }}">Yêu cầu rút tiền</a>
            @endif
        </div>
    </div>

    <div class="finance-toolbar">
        <form class="filter-form" method="GET" action="{{ route('admin.finance.index') }}">
            <input class="form-control-soft" type="date" name="date_from" value="{{ $dateFrom }}">
            <input class="form-control-soft" type="date" name="date_to" value="{{ $dateTo }}">
            <select class="form-control-soft" name="owner_id">
                <option value="">Tất cả chủ sân</option>
                @foreach($ownerOptions as $ownerOption)
                    <option value="{{ $ownerOption->id }}" @selected((int) $ownerId === (int) $ownerOption->id)>
                        {{ $ownerOption->name }} - {{ $ownerOption->email }}
                    </option>
                @endforeach
            </select>
            <button class="btn-primary-soft" type="submit">Lọc dữ liệu</button>
            <a class="btn-soft" href="{{ route('admin.finance.index') }}">Xóa lọc</a>
        </form>

        <div class="range-pill">{{ $filterLabel }} · {{ $ownerFilterLabel }}</div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">GMV</div>
                <div class="kpi-icon bg-blue">₫</div>
            </div>
            <div class="kpi-value tone-blue">{{ $money($gmv) }}</div>
            <div class="kpi-note">Tổng giá trị booking đã đối soát trong khoảng lọc.</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Hoa hồng nền tảng</div>
                <div class="kpi-icon bg-green">%</div>
            </div>
            <div class="kpi-value tone-green">{{ $money($platformRevenue) }}</div>
            <div class="kpi-note">Doanh thu SportHub ghi nhận sau settlement.</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Ví nền tảng</div>
                <div class="kpi-icon bg-green">V</div>
            </div>
            <div class="kpi-value tone-green">{{ $money($platformWalletBalance ?? 0) }}</div>
            <div class="kpi-note">Số tiền thật SportHub đang giữ hiện tại.</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label">Công nợ owner</div>
                <div class="kpi-icon bg-red">!</div>
            </div>
            <div class="kpi-value {{ (float) $totalDebt > 0 ? 'tone-red' : 'tone-green' }}">{{ $money($totalDebt) }}</div>
            <div class="kpi-note">
                @if($ownerId)
                    {{ (float) $totalDebt > 0 ? 'Chủ sân này đang nợ.' : 'Chủ sân này không nợ.' }}
                @else
                    {{ number_format($ownersInDebt, 0, ',', '.') }} owner đang có số dư âm.
                @endif
            </div>
        </div>
    </div>

    <div class="main-grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Hoa hồng theo tháng</h3>
                    <p class="panel-desc">Tách hoa hồng online và hoa hồng COD để dễ kiểm soát dòng thu.</p>
                </div>
                <div class="range-pill">{{ $dateFrom || $dateTo ? $filterLabel : '6 tháng gần nhất' }}</div>
            </div>
            <div class="panel-body">
                <div class="chart-canvas-wrap">
                    <canvas id="commissionRevenueChart"></canvas>
                </div>

                @unless($commissionHasData)
                    <div class="chart-empty">Chưa có dữ liệu hoa hồng trong khoảng thời gian này.</div>
                @endunless
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Dòng tiền nền tảng</h3>
                    <p class="panel-desc">Cash in/out theo giao dịch ví nền tảng.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="cash-stack">
                    <div class="cash-row">
                        <div>
                            <strong>Tiền vào</strong>
                            <span>Khách online + owner nạp ví</span>
                        </div>
                        <div class="cash-value tone-green">{{ $money($platformCashIn ?? 0) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Khách thanh toán online</strong>
                            <span>Booking VNPay thành công</span>
                        </div>
                        <div class="cash-value tone-green">{{ $money($customerOnlinePaymentIn ?? 0) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Owner nạp tiền</strong>
                            <span>Nạp ví qua VNPay</span>
                        </div>
                        <div class="cash-value tone-green">{{ $money($ownerTopupIn ?? 0) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Tiền ra</strong>
                            <span>Admin duyệt rút cho owner</span>
                        </div>
                        <div class="cash-value tone-red">{{ $money($platformCashOut ?? 0) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Dòng tiền ròng</strong>
                            <span>Cash in - cash out</span>
                        </div>
                        <div class="cash-value {{ ($platformNetCashFlow ?? 0) < 0 ? 'tone-red' : 'tone-green' }}">
                            {{ $money($platformNetCashFlow ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Ví chủ sân</h3>
                    <p class="panel-desc">Số dư owner, tiền nạp, tiền rút và hoa hồng COD.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="mini-grid">
                    <div class="mini-stat">
                        <div class="mini-label">Tổng số dư dương</div>
                        <div class="mini-value tone-green">{{ $money($totalWalletBalance) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-label">Chờ rút tiền</div>
                        <div class="mini-value tone-amber">{{ $money($pendingWithdrawals) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-label">Đã duyệt rút</div>
                        <div class="mini-value tone-red">{{ $money($approvedWithdrawals) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-label">Owner nạp thành công</div>
                        <div class="mini-value tone-green">{{ $money($successfulTopups) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-label">Hoa hồng COD đã trừ</div>
                        <div class="mini-value tone-amber">{{ $money($codCommissionDebt) }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-label">Tiền online về owner</div>
                        <div class="mini-value tone-green">{{ $money($onlineBookingCredit) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Tóm tắt đối soát</h3>
                    <p class="panel-desc">Các chỉ số quan trọng để kiểm tra nhanh dòng tiền.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="cash-stack">
                    <div class="cash-row">
                        <div>
                            <strong>Booking đã đối soát</strong>
                            <span>Đơn đã ghi nhận hoa hồng</span>
                        </div>
                        <div class="cash-value">{{ number_format($settledBookingCount, 0, ',', '.') }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Tiền thuộc về owner</strong>
                            <span>Sau khi trừ hoa hồng nền tảng</span>
                        </div>
                        <div class="cash-value tone-green">{{ $money($ownerPayout) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Tiền ra khỏi ví owner</strong>
                            <span>Rút tiền + hoa hồng COD đã trừ</span>
                        </div>
                        <div class="cash-value tone-red">{{ $money((float) $approvedWithdrawals + (float) $codCommissionDebt) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Công nợ hiện tại</strong>
                            <span>Tổng phần âm của ví chủ sân</span>
                        </div>
                        <div class="cash-value tone-red">{{ $money($totalDebt) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="finance-section">
        <div>
            <h3>Sức khỏe ví chủ sân</h3>
            <p>Theo dõi số dư, công nợ và những owner cần ưu tiên xử lý.</p>
        </div>
    </div>

    <div class="owner-health-grid">
    <div class="panel table-card">
        <div class="panel-head">
            <div>
                <h3 class="panel-title">Tổng quan ví chủ sân</h3>
                <p class="panel-desc">Danh sách ví owner có phân trang, giúp theo dõi số dư, công nợ và hạn mức khi hệ thống có nhiều chủ sân.</p>
            </div>
            @if(Route::has('admin.debts.index'))
                <a class="btn-soft" href="{{ route('admin.debts.index') }}">Xem quản lý công nợ</a>
            @endif
        </div>
        <form class="owner-filter-bar" method="GET" action="{{ route('admin.finance.index') }}">
            @if($dateFrom)
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
            @endif
            @if($dateTo)
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
            @endif
            @if($ownerId)
                <input type="hidden" name="owner_id" value="{{ $ownerId }}">
            @endif

            <input
                class="form-control-soft owner-filter-search"
                type="search"
                name="owner_search"
                value="{{ $ownerSearch }}"
                placeholder="Tìm theo tên, email hoặc số điện thoại chủ sân..."
            >

            <select class="form-control-soft" name="owner_status">
                <option value="all" @selected($ownerStatus === 'all')>Tất cả trạng thái</option>
                <option value="good" @selected($ownerStatus === 'good')>An toàn</option>
                <option value="in_debt" @selected($ownerStatus === 'in_debt')>Đang nợ</option>
                <option value="warning" @selected($ownerStatus === 'warning')>Gần hạn mức</option>
                <option value="over_limit" @selected($ownerStatus === 'over_limit')>Vượt hạn mức</option>
            </select>

            <select class="form-control-soft" name="owner_sort">
                <option value="debt_desc" @selected($ownerSort === 'debt_desc')>Công nợ cao nhất</option>
                <option value="balance_asc" @selected($ownerSort === 'balance_asc')>Số dư thấp nhất</option>
                <option value="balance_desc" @selected($ownerSort === 'balance_desc')>Số dư cao nhất</option>
                <option value="owner_name" @selected($ownerSort === 'owner_name')>Tên chủ sân A-Z</option>
                <option value="newest" @selected($ownerSort === 'newest')>Ví mới nhất</option>
            </select>

            <button class="btn-primary-soft" type="submit">Lọc chủ sân</button>
            <a class="btn-soft" href="{{ route('admin.finance.index', array_filter([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'owner_id' => $ownerId,
            ])) }}">Xóa lọc chủ sân</a>
        </form>
        <table class="data-table" style="min-width:980px;">
            <thead>
                <tr>
                    <th>Chủ sân</th>
                    <th>Số dư ví</th>
                    <th>Công nợ</th>
                    <th>Hạn mức</th>
                    <th>Đã dùng hạn mức</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ownerWalletRows as $row)
                    @php
                        $wallet = $row['wallet'];
                        $owner = $row['owner'];
                        $summary = $row['summary'] ?? [];
                        $status = $summary['status'] ?? 'good';
                        $usageText = (float) ($summary['usage_percent'] ?? 0);
                        $usageBar = min(100, max(0, $usageText));
                        $balance = (float) $wallet->balance;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $owner?->name ?? 'Không rõ' }}</strong>
                            <div class="muted">{{ $owner?->email }}</div>
                        </td>
                        <td class="{{ $balance < 0 ? 'tone-red' : 'tone-green' }}">
                            <strong>{{ $money($balance) }}</strong>
                        </td>
                        <td class="{{ (float) ($summary['debt_amount'] ?? 0) > 0 ? 'tone-red' : '' }}">
                            <strong>{{ $money($summary['debt_amount'] ?? 0) }}</strong>
                        </td>
                        <td>{{ $money($summary['debt_limit'] ?? 0) }}</td>
                        <td class="progress-cell">
                            <strong>{{ number_format($usageText, 0, ',', '.') }}%</strong>
                            <div class="progress-track">
                                <div class="progress-bar {{ $status === 'over_limit' ? 'progress-over' : (($status === 'warning') ? 'progress-warning' : '') }}"
                                     style="width: {{ $usageBar }}%"></div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-status {{ $statusClasses[$status] ?? 'status-good' }}">
                                {{ $statusLabels[$status] ?? $status }}
                            </span>
                        </td>
                        <td>
                            @if(Route::has('admin.debts.index') && $owner)
                                <a class="btn-soft" href="{{ route('admin.debts.index', ['search' => $owner->email]) }}">Xem chi tiết</a>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:36px; text-align:center;">
                            <strong>Chưa có ví chủ sân nào.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($ownerWalletRows->hasPages())
            <div class="pagination-wrapper">
                {{ $ownerWalletRows->links('vendor.pagination.admin') }}
            </div>
        @endif
    </div>

        @unless($ownerId)
        <div class="panel table-card">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Top owner công nợ cao</h3>
                    <p class="panel-desc">Ưu tiên xử lý các ví owner có số dư âm.</p>
                </div>
            </div>
            <table class="data-table compact-table">
                <thead>
                    <tr>
                        <th style="width: 38%;">Chủ sân</th>
                        <th style="width: 20%;">Công nợ</th>
                        <th style="width: 20%;">Hạn mức</th>
                        <th style="width: 22%;">Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDebtOwners as $row)
                        @php
                            $wallet = $row['wallet'];
                            $owner = $row['owner'];
                            $summary = $row['summary'];
                            $status = $summary['status'] ?? 'in_debt';
                            $usageText = (float) ($summary['usage_percent'] ?? 0);
                            $usageBar = min(100, max(0, $usageText));
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $owner->name }}</strong>
                                <div class="muted">{{ $owner->email }}</div>
                            </td>
                            <td class="tone-red"><strong>{{ $money($summary['debt_amount'] ?? 0) }}</strong></td>
                            <td>{{ $money($summary['debt_limit'] ?? 0) }}</td>
                            <td class="progress-cell">
                                <strong>{{ number_format($usageText, 0, ',', '.') }}%</strong>
                                <div class="progress-track">
                                    <div class="progress-bar {{ $status === 'over_limit' ? 'progress-over' : (($status === 'warning') ? 'progress-warning' : '') }}"
                                         style="width: {{ $usageBar }}%"></div>
                                </div>
                                <div class="progress-status">
                                    <span class="badge-status {{ $statusClasses[$status] ?? 'status-in_debt' }}">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:28px; text-align:center;">
                                <strong>Không có chủ sân đang nợ.</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endunless
    </div>

    <div class="finance-section">
        <div>
            <h3>Nhật ký giao dịch</h3>
            <p>Kiểm tra các giao dịch mới nhất trên ví nền tảng và ví owner.</p>
        </div>
    </div>

    <div class="panel table-card">
        <div class="panel-head">
            <div>
                <h3 class="panel-title">Giao dịch ví nền tảng mới nhất</h3>
                <p class="panel-desc">Theo dõi tiền thật vào/ra ví nền tảng SportHub.</p>
            </div>
        </div>
        <table class="data-table" style="min-width:1120px;">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Loại giao dịch</th>
                    <th>Số tiền</th>
                    <th>Số dư trước</th>
                    <th>Số dư sau</th>
                    <th>Mô tả</th>
                    <th>Tham chiếu</th>
                    <th>Người thực hiện</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestPlatformTransactions ?? collect() as $transaction)
                    @php
                        $platformType = $typeValue($transaction);
                        $platformAmount = (float) $transaction->amount;
                        $platformIsDebit = $platformAmount < 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $transaction->created_at?->format('d/m/Y') }}</strong>
                            <div class="muted">{{ $transaction->created_at?->format('H:i') }}</div>
                        </td>
                        <td><span class="tx-badge">{{ $platformTransactionTypeLabels[$platformType] ?? $platformType }}</span></td>
                        <td class="{{ $platformIsDebit ? 'tone-red' : 'tone-green' }}">
                            <strong>{{ $signedMoney($platformAmount) }}</strong>
                        </td>
                        <td><strong>{{ $money($transaction->balance_before) }}</strong></td>
                        <td><strong>{{ $money($transaction->balance_after) }}</strong></td>
                        <td class="muted">{{ $transaction->description ?: 'Không có mô tả' }}</td>
                        <td>
                            @if($transaction->reference)
                                <strong>{{ $transaction->reference }}</strong>
                                @if($transaction->reference_type || $transaction->reference_id)
                                    <div class="muted">{{ $transaction->reference_type }} #{{ $transaction->reference_id }}</div>
                                @endif
                            @else
                                <span class="muted">Không có</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $transaction->performer?->name ?? 'Hệ thống' }}</strong>
                            @if($transaction->performer?->email)
                                <div class="muted">{{ $transaction->performer->email }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:36px; text-align:center;">
                            <strong>Chưa có giao dịch ví nền tảng.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel table-card">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Giao dịch ví owner mới nhất</h3>
                    <p class="panel-desc">Các thay đổi số dư ví chủ sân gần nhất.</p>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Owner</th>
                        <th>Loại</th>
                        <th>Số tiền</th>
                        <th>Số dư sau</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestTransactions as $transaction)
                        @php
                            $currentType = $typeValue($transaction);
                            $isDebit = in_array($currentType, ['commission_fee', 'commission_cod_debit', 'withdraw', 'withdrawal_debit', 'refund_debit', 'payment'], true);
                            $signedAmount = $isDebit ? -abs((float) $transaction->amount) : abs((float) $transaction->amount);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $transaction->created_at?->format('d/m/Y') }}</strong>
                                <div class="muted">{{ $transaction->created_at?->format('H:i') }}</div>
                            </td>
                            <td>
                                <strong>{{ $transaction->wallet?->owner?->name ?? 'Không rõ' }}</strong>
                                <div class="muted">{{ $transaction->wallet?->owner?->email }}</div>
                            </td>
                            <td><span class="tx-badge">{{ $transactionTypeLabels[$currentType] ?? $currentType }}</span></td>
                            <td class="{{ $isDebit ? 'tone-red' : 'tone-green' }}">
                                <strong>{{ $signedMoney($signedAmount) }}</strong>
                            </td>
                            <td><strong>{{ $money($transaction->balance_after) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:36px; text-align:center;">
                                <strong>Chưa có giao dịch ví owner.</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('commissionRevenueChart');

    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const labels = @json($commissionChartLabels ?? []);
    const onlineData = @json($commissionChartOnlineData ?? []);
    const codData = @json($commissionChartCodData ?? []);
    const totalData = @json($commissionChartTotalData ?? []);
    const moneyFormatter = new Intl.NumberFormat('vi-VN');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tổng hoa hồng',
                    data: totalData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.12)',
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 4,
                    fill: true
                },
                {
                    label: 'Hoa hồng online',
                    data: onlineData,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: false
                },
                {
                    label: 'Hoa hồng COD',
                    data: codData,
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.08)',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + moneyFormatter.format(Number(context.raw || 0)) + 'đ';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#eef2f7'
                    },
                    ticks: {
                        color: '#64748b',
                        callback: function (value) {
                            return moneyFormatter.format(Number(value || 0)) + 'đ';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
