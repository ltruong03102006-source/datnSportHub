@extends('admin.layouts.app')

@push('styles')
<style>
    .finance-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
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

    /* Section Tier Header Badges */
    .tier-header-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 6px;
        margin-bottom: 2px;
    }

    .tier-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .tier-chip-1 { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .tier-chip-2 { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .tier-chip-3 { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

    /* KPI Grid Styling */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
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
        padding: 18px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
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
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 900;
    }

    .kpi-value {
        margin-top: 12px;
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
    .tone-purple { color: #7c3aed; }
    
    .bg-green { background: #ecfdf5; color: #047857; }
    .bg-red { background: #fef2f2; color: #dc2626; }
    .bg-amber { background: #fffbeb; color: #d97706; }
    .bg-blue { background: #eff6ff; color: #2563eb; }
    .bg-purple { background: #f3e8ff; color: #7c3aed; }

    /* Solvency Panel Styling */
    .solvency-panel {
        padding: 20px 22px;
        border-radius: 16px;
        border: 1px solid #cbd5e1;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .solvency-grid {
        display: grid;
        grid-template-columns: 1fr 1.15fr 1.15fr;
        gap: 16px;
        margin-top: 16px;
    }

    .solvency-box {
        padding: 18px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
    }

    .solvency-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06);
    }

    .box-tag {
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .box-title {
        font-size: 15px;
        font-weight: 900;
        color: #0f172a;
    }

    .box-value {
        font-size: 26px;
        font-weight: 950;
        margin: 10px 0;
    }

    .box-desc {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        line-height: 1.4;
    }

    .box-sub-items {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #cbd5e1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .box-sub-items .sub-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #475569;
    }

    .box-sub-items .sub-item strong {
        color: #0f172a;
    }

    .main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(360px, .85fr);
        gap: 16px;
        align-items: start;
    }

    .panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 20px;
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
        padding: 18px 20px;
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
        padding: 12px 14px;
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
        padding: 14px;
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

    .data-table th {
        padding: 12px 16px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid var(--border-color);
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .05em;
        text-align: left;
        text-transform: uppercase;
    }

    .data-table td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--border-color);
        color: #0f172a;
        font-size: 12px;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .data-table tr:last-child td {
        border-bottom: 0;
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
        padding: 5px 10px;
        background: #f1f5f9;
        color: #475569;
    }

    .status-good { background: #dcfce7; color: #166534; }

    @media (max-width: 1180px) {
        .kpi-grid,
        .section-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .main-grid {
            grid-template-columns: 1fr;
        }

        .solvency-grid {
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
    }
</style>
@endpush

@section('content')
@php
    $money = fn ($amount) => ((float) $amount < 0 ? '-' : '') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';
    $signedMoney = fn ($amount) => ((float) $amount < 0 ? '-' : '+') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';

    $transactionTypeLabels = [
        'booking_income' => 'Nhận tiền booking online',
        'booking_online_credit' => 'Nhận tiền booking online',
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
        'owner_withdrawal_out' => 'Chi tiền owner rút',
        'customer_refund_out' => 'Hoàn tiền khách',
        'owner_topup_in' => 'Chủ sân nạp tiền ví',
        'admin_revenue_withdrawal' => 'Admin rút lợi nhuận',
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

    $ownerWalletLiability = $ownerWalletLiability ?? 0;
    $customerWalletLiability = $customerWalletLiability ?? 0;
    $totalSystemLiability = $totalSystemLiability ?? ($ownerWalletLiability + $customerWalletLiability);
    $unsettledFunds = $unsettledFunds ?? 0;
    $displaySafeAmount = $displaySafeAmount ?? 0;
@endphp

<div class="finance-page">
    <!-- Header Page -->
    <div class="finance-header">
        <div>
            <div class="eyebrow">SportHub Admin Finance</div>
            <h2 class="page-title">Tổng quan tài chính</h2>
            <p class="page-subtitle">Quản lý GMV, doanh thu hoa hồng, sức khỏe ví nền tảng và phân luồng dòng tiền.</p>
        </div>

        <div class="actions">
            @if(Route::has('admin.withdrawals.index'))
                <a class="btn-soft" href="{{ route('admin.withdrawals.index') }}">
                    <i class="fa-solid fa-list-check mr-2"></i>Yêu cầu rút tiền
                </a>
            @endif
        </div>
    </div>

    <!-- Toolbar bộ lọc -->
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
            <button class="btn-primary-soft" type="submit"><i class="fa-solid fa-filter mr-1"></i> Lọc dữ liệu</button>
            <a class="btn-soft" href="{{ route('admin.finance.index') }}">Xóa lọc</a>
        </form>
        <div class="range-pill">{{ $filterLabel }} · {{ $ownerFilterLabel }}</div>
    </div>

    <!-- TẦNG 1: HIỆU NĂNG KINH DOANH -->
    <div>
        <div class="tier-header-bar">
            <span class="tier-chip tier-chip-1"><i class="fa-solid fa-chart-line"></i>Hiệu Năng Kinh Doanh</span>
        </div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-label">Tổng GMV</div>
                    <div class="kpi-icon bg-blue"><i class="fa-solid fa-futbol"></i></div>
                </div>
                <div class="kpi-value tone-blue">{{ $money($gmv) }}</div>
                <div class="kpi-note">
                    <span class="muted">Đặt lẻ:</span> <strong>{{ $money($singleGmv) }}</strong> · 
                    <span class="muted">Đặt Gói:</span> <strong class="tone-purple">{{ $money($packageGmv) }}</strong>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-label">Hoa hồng nền tảng</div>
                    <div class="kpi-icon bg-green"><i class="fa-solid fa-coins"></i></div>
                </div>
                <div class="kpi-value tone-green">{{ $money($platformRevenue) }}</div>
                <div class="kpi-note">
                    <span class="muted">Đặt lẻ:</span> <strong>{{ $money($singleCommission) }}</strong> · 
                    <span class="muted">Đặt Gói:</span> <strong class="tone-purple">{{ $money($packageCommission) }}</strong>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-label">Doanh thu Đặt Lẻ</div>
                    <div class="kpi-icon bg-blue"><i class="fa-solid fa-calendar-day"></i></div>
                </div>
                <div class="kpi-value tone-blue">{{ $money($singleGmv) }}</div>
                <div class="kpi-note">
                    Doanh thu từ lượt đặt sân lẻ.
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-label">Doanh thu Đặt Gói</div>
                    <div class="kpi-icon bg-purple"><i class="fa-solid fa-boxes-packing"></i></div>
                </div>
                <div class="kpi-value tone-purple">{{ $money($totalPackageSalesAmount) }}</div>
                <div class="kpi-note">
                    <strong>{{ $activePackageCount }}</strong> gói đang chạy · <strong>{{ $completedPackageCount }}</strong> gói hoàn thành.
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-label">Booking hoàn tất</div>
                    <div class="kpi-icon bg-blue" style="background:#f0fdf4; color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div>
                </div>
                <div class="kpi-value" style="color:#16a34a;">{{ number_format(($singleSettledCount ?? 0) + ($totalPackageCount ?? 0), 0, ',', '.') }}</div>
                <div class="kpi-note">
                    <span class="muted">Đặt lẻ:</span> <strong>{{ number_format($singleSettledCount ?? 0, 0, ',', '.') }} ca</strong> · 
                    <span class="muted">Đặt Gói:</span> <strong class="tone-purple">{{ number_format($totalPackageCount ?? 0, 0, ',', '.') }} gói</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- TẦNG 2: PHÂN TÍCH SỨC KHỎE VÍ & LỢI NHUẬN AN TOÀN -->
    <div>
        <div class="tier-header-bar">
            <span class="tier-chip tier-chip-2"><i class="fa-solid fa-shield-halved"></i>Ví Nền Tảng & An Toàn Tài Chính</span>
        </div>
        <div class="panel solvency-panel">
            <div class="panel-head" style="padding:0 0 16px 0; border-bottom: 1px dashed #cbd5e1;">
                <div>
                    <h3 class="panel-title">
                        <i class="fa-solid fa-scale-balanced tone-green mr-2"></i> 
                        {{ $selectedOwner ? ('Minh Bạch Dòng Tiền - Chủ Sân: ' . $selectedOwner->name) : 'Minh Bạch Dòng Tiền & Lợi Nhuận An Toàn' }}
                    </h3>
                    <p class="panel-desc">
                        {{ $selectedOwner ? ('Phân tích nghĩa vụ giữ hộ tiền và khả năng rút tiền của ' . $selectedOwner->name) : 'Phân biệt rạch ròi giữa Tiền thuộc về Admin và Tiền giữ hộ cho Chủ sân & Khách hàng.' }}
                    </p>
                </div>
                <div class="range-pill" style="background:#ecfdf5; color:#047857; font-weight:800;">
                    <i class="fa-solid fa-lock mr-1"></i> Đảm bảo khả năng thanh toán 100%
                </div>
            </div>

            <div class="solvency-grid">
                <!-- BOX 1: Ví nền tảng -->
                <div class="solvency-box">
                    <div>
                        <div class="box-tag tone-blue">TỔNG TÀI SẢN THỰC TẾ</div>
                        <div class="box-title">Ví Tổng Nền Tảng (Bank/VNPay)</div>
                    </div>
                    <div class="box-value tone-blue">{{ $money($platformWalletBalance ?? 0) }}</div>
                    <div class="box-desc">Số tiền thật SportHub đang nắm giữ trong tài khoản thanh toán.</div>
                </div>

                <!-- BOX 2: Nghĩa vụ tạm giữ -->
                <div class="solvency-box">
                    <div>
                        <div class="box-tag tone-amber">{{ $selectedOwner ? 'TIỀN GIỮ HỘ CHO CHỦ SÂN' : 'NGHĨA VỤ TẠM GIỮ HỘ' }}</div>
                        <div class="box-title">{{ $selectedOwner ? ('Tiền Nền Tảng Giữ Cho ' . $selectedOwner->name) : 'Tiền Giữ Hộ' }}</div>
                    </div>
                    <div class="box-value tone-amber">{{ $money($totalSystemLiability + $unsettledFunds) }}</div>
                    <div class="box-sub-items">
                        <div class="sub-item">
                            <span><i class="fa-solid fa-wallet text-amber mr-1"></i> Số dư ví Chủ sân (Khả dụng):</span>
                            <strong>{{ $money($ownerWalletLiability) }}</strong>
                        </div>
                        @if(!$selectedOwner)
                        <div class="sub-item">
                            <span><i class="fa-solid fa-user-large text-amber mr-1"></i> Số dư ví Khách hàng:</span>
                            <strong class="tone-blue">{{ $money($customerWalletLiability) }}</strong>
                        </div>
                        @endif
                        <div class="sub-item">
                            <span><i class="fa-solid fa-futbol text-amber mr-1"></i> Booking lẻ chờ đối soát (Chưa đá):</span>
                            <strong>{{ $money($unsettledFunds) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- BOX 3: Lợi nhuận an toàn hoặc Thu nhập khả dụng của Owner -->
                <div class="solvency-box">
                    @if($selectedOwner)
                    <div>
                        <div class="box-tag tone-green">SỐ DƯ CÓ THỂ RÚT NGAY</div>
                        <div class="box-title">Tiền Chủ Sân Có Thể Rút</div>
                    </div>
                    <div class="box-value tone-green">{{ $money($ownerWalletLiability) }}</div>
                    <div class="box-desc">
                        <i class="fa-solid fa-circle-check tone-green mr-1"></i> Số tiền {{ $selectedOwner->name }} có thể tạo yêu cầu rút về ngân hàng.
                    </div>
                    @else
                    <div>
                        <div class="box-tag tone-green">LỢI NHUẬN KHẢ DỤNG THỰC TẾ</div>
                        <div class="box-title">Lợi Nhuận An Toàn Thuộc Admin</div>
                    </div>
                    <div class="box-value tone-green">{{ $money($displaySafeAmount) }}</div>
                    <div class="box-desc">
                        <i class="fa-solid fa-circle-check tone-green mr-1"></i> Tiền thực tế Admin sở hữu an toàn (Ví Tổng − Tiền Giữ Hộ).
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG THỐNG KÊ CHI TIẾT THEO TỪNG CƠ SỞ (KHI LỌC CHỦ SÂN) -->
    @if($selectedOwner)
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3 class="panel-title">Phân bổ doanh thu theo Cơ sở</h3>
                <p class="panel-desc">Chi tiết đóng góp GMV và Hoa hồng từ các sân của {{ $selectedOwner->name }}.</p>
            </div>
            <div class="range-pill">{{ $dateFrom || $dateTo ? $filterLabel : 'Toàn thời gian' }}</div>
        </div>
        <div class="panel-body" style="padding: 0;">
            @php
                $gmvCol = \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'gross_amount') ? 'gross_amount' : 'total_price';
                $feeCol = \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'platform_fee') ? 'platform_fee' : 'commission_amount';
                $dateCol = \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'settled_at') ? 'settled_at' : 'created_at';

                $ownerVenues = \App\Models\Venue::where('owner_id', $selectedOwner->id)->get();
                $venueStats = [];

                foreach($ownerVenues as $venue) {
                    $vBookings = \App\Models\Booking::whereHas('court', function($q) use ($venue) {
                        $q->where('venue_id', $venue->id);
                    })
                    ->when($dateFrom, fn($q) => $q->whereDate($dateCol, '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate($dateCol, '<=', $dateTo));

                    if (\Illuminate\Support\Facades\Schema::hasColumn('bookings', 'settlement_status')) {
                        $vBookings->where('settlement_status', 'settled');
                    } else {
                        $vBookings->whereIn('status', ['completed', 'confirmed']);
                        if (\Illuminate\Support\Facades\Schema::hasColumn('bookings', 'payment_status')) {
                            $vBookings->where('payment_status', 'paid');
                        }
                    }

                    $vGmv = (float) (clone $vBookings)->sum($gmvCol);
                    $vFee = (float) (clone $vBookings)->sum($feeCol);
                    $vCount = (clone $vBookings)->count();

                    if ($vCount > 0 || $venue->status === 'active') {
                        $venueStats[] = [
                            'name' => $venue->name,
                            'status' => $venue->status,
                            'count' => $vCount,
                            'gmv' => $vGmv,
                            'fee' => $vFee,
                            'net' => $vGmv - $vFee
                        ];
                    }
                }
                
                usort($venueStats, fn($a, $b) => $b['gmv'] <=> $a['gmv']);
            @endphp

            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>TÊN CƠ SỞ</th>
                            <th style="text-align: center;">SỐ LƯỢNG ĐƠN</th>
                            <th style="text-align: right;">GMV (DOANH THU)</th>
                            <th style="text-align: right;">HOA HỒNG ADMIN</th>
                            <th style="text-align: right;">TIỀN THU HỘ CỦA SÂN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($venueStats as $stat)
                            <tr>
                                <td>
                                    <strong style="color: #0f172a; font-size: 13px;">{{ $stat['name'] }}</strong>
                                    <div style="margin-top: 4px;">
                                        @if($stat['status'] === 'active')
                                            <span class="badge-status status-good">Đang hoạt động</span>
                                        @else
                                            <span class="badge-status" style="background: #e2e8f0; color: #475569;">Tạm ngừng</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 700;">{{ $stat['count'] }}</td>
                                <td style="text-align: right;" class="tone-blue"><strong>{{ $money($stat['gmv']) }}</strong></td>
                                <td style="text-align: right;" class="tone-green"><strong>{{ $money($stat['fee']) }}</strong></td>
                                <td style="text-align: right;" class="tone-amber"><strong>{{ $money($stat['net']) }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">
                                    Chủ sân này chưa có cơ sở nào phát sinh doanh thu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TẦNG 3: BIỂU ĐỒ & DÒNG TIỀN LUÂN CHUYỂN -->
    <div>
        <div class="tier-header-bar">
            <span class="tier-chip tier-chip-3"><i class="fa-solid fa-arrows-left-right"></i>Biểu Đồ & Dòng Tiền Luân Chuyển</span>
        </div>
        <div class="main-grid">
            <!-- Biểu đồ -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Hoa hồng theo tháng</h3>
                        <p class="panel-desc">Biểu đồ tổng doanh thu hoa hồng từ thanh toán online.</p>
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

            <!-- Phân luồng dòng tiền -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Dòng tiền nền tảng (Cash In / Out)</h3>
                        <p class="panel-desc">Phân luồng dòng tiền vào và ra trên tài khoản tổng.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="cash-stack">
                        <div class="cash-row">
                            <div>
                                <strong><i class="fa-solid fa-arrow-down-left text-emerald mr-1"></i> Tiền vào (Khách thanh toán)</strong>
                                <span>Booking VNPay thành công</span>
                            </div>
                            <div class="cash-value tone-green">{{ $money($customerOnlinePaymentIn ?? 0) }}</div>
                        </div>

                        @if(($ownerTopupIn ?? 0) > 0)
                        <div class="cash-row">
                            <div>
                                <strong><i class="fa-solid fa-arrow-down-left text-emerald mr-1"></i> Tiền vào (Chủ sân nạp ví)</strong>
                                <span>Nạp tiền vào ví hệ thống</span>
                            </div>
                            <div class="cash-value tone-green">{{ $money($ownerTopupIn) }}</div>
                        </div>
                        @endif

                        @if(($ownerWithdrawalOut ?? 0) > 0)
                        <div class="cash-row">
                            <div>
                                <strong><i class="fa-solid fa-arrow-up-right text-rose mr-1"></i> Tiền ra (Chuyển trả Owner)</strong>
                                <span>Admin đã duyệt rút cho Chủ sân</span>
                            </div>
                            <div class="cash-value tone-red">{{ $money($ownerWithdrawalOut ?? 0) }}</div>
                        </div>
                        @endif

                        <div class="cash-row">
                            <div>
                                <strong><i class="fa-solid fa-rotate-left text-rose mr-1"></i> Tiền ra (Hoàn tiền Khách)</strong>
                                <span>Khách hủy đơn đặt sân hợp lệ</span>
                            </div>
                            <div class="cash-value tone-red">{{ $money($customerRefundOut ?? 0) }}</div>
                        </div>

                        @if(($adminRevenueWithdrawal ?? 0) > 0)
                        <div class="cash-row">
                            <div>
                                <strong><i class="fa-solid fa-arrow-up-right text-rose mr-1"></i> Tiền ra (Admin rút lợi nhuận)</strong>
                                <span>Rút tiền lợi nhuận nền tảng</span>
                            </div>
                            <div class="cash-value tone-red">{{ $money($adminRevenueWithdrawal) }}</div>
                        </div>
                        @endif

                        @php
                            $displayedCashIn = ($customerOnlinePaymentIn ?? 0) + ($ownerTopupIn ?? 0);
                            $displayedCashOut = ($ownerWithdrawalOut ?? 0) + ($customerRefundOut ?? 0) + ($adminRevenueWithdrawal ?? 0);
                            $displayedNetCashFlow = $displayedCashIn - $displayedCashOut;
                        @endphp
                        <div class="cash-row" style="border-top: 2px dashed #cbd5e1; margin-top: 6px; background:#f1f5f9;">
                            <div>
                                <strong><i class="fa-solid fa-calculator mr-1"></i> Dòng tiền ròng</strong>
                                <span>Tổng tiền vào − Tổng tiền ra</span>
                            </div>
                            <div class="cash-value {{ $displayedNetCashFlow < 0 ? 'tone-red' : 'tone-green' }}">
                                {{ $money($displayedNetCashFlow) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TẦNG 4: THỐNG KÊ VÍ CHỦ SÂN, ĐẶT GÓI & TÓM TẮT ĐỐI SOÁT -->
    <div class="section-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title"><i class="fa-solid fa-wallet tone-green mr-1"></i> Thống kê Ví Người Dùng</h3>
                    <p class="panel-desc">Số dư và trạng thái ví của Chủ sân & Khách hàng.</p>
                </div>
            </div>
            <div class="panel-body" style="display:flex; flex-direction:column; gap:12px;">
                <!-- PHẦN 1: VÍ CHỦ SÂN -->
                <div>
                    <div style="font-size:11px; font-weight:800; color:#334155; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        <span class="badge-status status-good" style="padding:2px 7px; font-size:10px;"><i class="fa-solid fa-store mr-1"></i> VÍ CHỦ SÂN</span>
                    </div>
                    <div class="mini-grid" style="gap:8px;">
                        <div class="mini-stat" style="padding:8px 10px;">
                            <div class="mini-label" style="font-size:10px;">Dư khả dụng</div>
                            <div class="mini-value tone-green" style="font-size:14px; font-weight:800;">{{ $money($ownerWalletLiability ?? $totalWalletBalance) }}</div>
                        </div>
                        <div class="mini-stat" style="padding:8px 10px;">
                            <div class="mini-label" style="font-size:10px;">Online về ví</div>
                            <div class="mini-value tone-green" style="font-size:14px; font-weight:800;">{{ $money($onlineBookingCredit) }}</div>
                        </div>
                        <div class="mini-stat" style="padding:8px 10px;">
                            <div class="mini-label" style="font-size:10px;">Chờ duyệt rút</div>
                            <div class="mini-value tone-amber" style="font-size:14px; font-weight:800;">{{ $money($pendingWithdrawals) }}</div>
                        </div>
                        <div class="mini-stat" style="padding:8px 10px;">
                            <div class="mini-label" style="font-size:10px;">Đã duyệt rút</div>
                            <div class="mini-value tone-red" style="font-size:14px; font-weight:800;">{{ $money($approvedWithdrawals) }}</div>
                        </div>
                    </div>
                </div>

                <!-- PHẦN 2: VÍ KHÁCH HÀNG -->
                @if(!$selectedOwner)
                <div style="border-top:1px dashed #cbd5e1; padding-top:10px;">
                    <div style="font-size:11px; font-weight:800; color:#334155; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        <span class="badge-status" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:2px 7px; font-size:10px;"><i class="fa-solid fa-user mr-1"></i> VÍ KHÁCH HÀNG</span>
                    </div>
                    <div class="mini-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap:8px;">
                        <div class="mini-stat" style="padding:8px 10px;">
                            <div class="mini-label" style="font-size:10px;">Tổng dư ví khách</div>
                            <div class="mini-value tone-blue" style="font-size:14px; font-weight:800;">{{ $money($customerWalletLiability ?? 0) }}</div>
                        </div>
                        <div class="mini-stat" style="padding:8px 10px;">
                            <div class="mini-label" style="font-size:10px;">Ví có số dư</div>
                            <div class="mini-value" style="font-size:14px; font-weight:800; color:#2563eb;">{{ number_format($customerWalletCount ?? 0, 0, ',', '.') }} ví</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title"><i class="fa-solid fa-credit-card tone-purple mr-1"></i> Phân Tích Phương Thức Thanh Toán</h3>
                    <p class="panel-desc">Phân luồng GMV theo từng kênh thanh toán.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="cash-stack">
                    <div class="cash-row">
                        <div>
                            <strong><i class="fa-solid fa-globe text-emerald mr-1"></i> VNPay / Online</strong>
                            <span>Thanh toán cổng VNPay & Thẻ</span>
                        </div>
                        <div class="cash-value tone-green">{{ $money($onlinePaymentGmv) }}</div>
                    </div>
                    @if(($codPaymentGmv ?? 0) > 0)
                    <div class="cash-row">
                        <div>
                            <strong><i class="fa-solid fa-money-bill-1-wave text-amber mr-1"></i> Tiền mặt / COD tại sân</strong>
                            <span>Khách thanh toán trực tiếp tại sân</span>
                        </div>
                        <div class="cash-value tone-amber">{{ $money($codPaymentGmv) }}</div>
                    </div>
                    @endif
                    <div class="cash-row">
                        <div>
                            <strong><i class="fa-solid fa-wallet text-blue mr-1"></i> Ví Khách Hàng</strong>
                            <span>Trừ tiền trực tiếp từ ví cá nhân</span>
                        </div>
                        <div class="cash-value tone-blue">{{ $money($walletPaymentGmv) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong><i class="fa-solid fa-boxes-packing text-purple mr-1"></i> Đặt theo Gói</strong>
                            <span>Dùng suất từ gói đã mua</span>
                        </div>
                        <div class="cash-value tone-purple">{{ $money($packageGmv) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Tóm tắt đối soát đơn</h3>
                    <p class="panel-desc">Chỉ số kiểm tra sổ sách đối soát.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="cash-stack">
                    <div class="cash-row">
                        <div>
                            <strong>Booking đã đối soát</strong>
                            <span>Hoàn tất & chia phế</span>
                        </div>
                        <div class="cash-value">{{ number_format(($singleSettledCount ?? 0) + ($totalPackageCount ?? 0), 0, ',', '.') }} giao dịch</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Tiền thuộc về Owner</strong>
                            <span>Sau khi trừ hoa hồng</span>
                        </div>
                        <div class="cash-value tone-green">{{ $money($onlineBookingCredit ?? $ownerPayout) }}</div>
                    </div>
                    <div class="cash-row">
                        <div>
                            <strong>Tiền đã chi khỏi Ví Owner</strong>
                            <span>Số tiền Admin đã duyệt rút</span>
                        </div>
                        <div class="cash-value tone-red">{{ $money($approvedWithdrawals) }}</div>
                    </div>
                    <div class="cash-row" style="background:#f8fafc; border-top:1px dashed #cbd5e1;">
                        <div>
                            <strong>Tiền Owner chưa rút</strong>
                            <span>Còn lại trong ví các sân</span>
                        </div>
                        <div class="cash-value tone-blue">{{ $money($ownerWalletLiability) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NHẬT KÝ GIAO DỊCH VÍ NỀN TẢNG -->
    <div class="panel table-card">
        <div class="panel-head">
            <div>
                <h3 class="panel-title"><i class="fa-solid fa-list-ol tone-green mr-2"></i> Nhật ký giao dịch Ví Nền Tảng mới nhất</h3>
                <p class="panel-desc">Kiểm tra 10 giao dịch gần đây nhất phát sinh trên ví tổng nền tảng.</p>
            </div>
        </div>
        <table class="data-table" style="min-width:1050px;">
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
                            @else
                                <span class="muted">Không có</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $transaction->performer?->name ?? 'Hệ thống' }}</strong>
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
    const totalData = @json($commissionChartTotalData ?? []);
    const moneyFormatter = new Intl.NumberFormat('vi-VN');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Doanh thu hoa hồng',
                    data: totalData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.12)',
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + moneyFormatter.format(Number(context.raw || 0)) + 'đ';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
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
