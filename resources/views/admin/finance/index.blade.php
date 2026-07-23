@extends('admin.layouts.app')

@push('styles')
<style>
    .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:20px; margin-bottom:24px; }
    .page-title { margin:0; color:var(--text-dark); font-size:24px; font-weight:900; }
    .page-subtitle { margin:6px 0 0; color:var(--text-muted); font-size:14px; }
    .btn-soft { display:inline-flex; align-items:center; justify-content:center; height:42px; padding:0 16px; border:1px solid var(--border-color); border-radius:10px; background:#fff; color:var(--text-dark); font-size:13px; font-weight:900; text-decoration:none; }
    .btn-primary-soft { display:inline-flex; align-items:center; justify-content:center; height:42px; padding:0 16px; border:0; border-radius:10px; background:var(--primary); color:#fff; font-size:13px; font-weight:900; cursor:pointer; }
    .actions { display:flex; flex-wrap:wrap; gap:10px; }
    .filter-card, .table-card, .metric-card { background:#fff; border:1px solid var(--border-color); border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.02); }
    .filter-card { padding:16px; margin-bottom:20px; }
    .filter-form { display:grid; grid-template-columns:180px 180px auto auto; gap:10px; align-items:center; }
    .form-control-soft { height:42px; border:1px solid var(--border-color); border-radius:10px; padding:0 14px; color:var(--text-dark); font-size:13px; outline:none; }
    .form-control-soft:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(46,204,113,.12); }
    .section-title { color:var(--text-dark); font-size:18px; font-weight:900; margin:28px 0 14px; }
    .metrics-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; }
    .metric-card { padding:18px; min-height:118px; }
    .metric-label { color:var(--text-muted); font-size:11px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; }
    .metric-value { margin-top:10px; color:var(--text-dark); font-size:24px; font-weight:900; }
    .metric-note { margin-top:6px; color:var(--text-muted); font-size:12px; font-weight:700; }
    .green { color:#047857; }
    .red { color:#dc2626; }
    .amber { color:#d97706; }
    .blue { color:#2563eb; }
    .table-card { overflow-x:auto; }
    .data-table { width:100%; min-width:900px; border-collapse:collapse; }
    .data-table th { padding:15px 18px; background:#f8fafc; color:var(--text-muted); border-bottom:1px solid var(--border-color); font-size:11px; font-weight:900; letter-spacing:.04em; text-align:left; text-transform:uppercase; }
    .data-table td { padding:16px 18px; border-bottom:1px solid var(--border-color); color:var(--text-dark); font-size:13px; vertical-align:middle; }
    .data-table tr:last-child td { border-bottom:0; }
    .muted { color:var(--text-muted); font-size:12px; }
    .badge-status { display:inline-flex; padding:6px 11px; border-radius:999px; font-size:12px; font-weight:900; }
    .status-good { background:#dcfce7; color:#166534; }
    .status-in_debt { background:#fef3c7; color:#92400e; }
    .status-warning { background:#ffedd5; color:#c2410c; }
    .status-over_limit { background:#fee2e2; color:#b91c1c; }
    .progress-track { width:120px; height:8px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-top:7px; }
    .progress-bar { height:100%; border-radius:999px; background:#10b981; }
    .progress-warning { background:#f59e0b; }
    .progress-over { background:#ef4444; }
    .tx-badge { display:inline-flex; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:900; background:#f1f5f9; color:#475569; }
    .chart-card { background:#fff; border:1px solid var(--border-color); border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.02); padding:20px; margin-top:18px; }
    .chart-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:16px; }
    .chart-title { margin:0; color:var(--text-dark); font-size:18px; font-weight:900; }
    .chart-desc { margin:6px 0 0; color:var(--text-muted); font-size:13px; line-height:1.5; }
    .chart-range { display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:#f8fafc; color:var(--text-muted); font-size:12px; font-weight:900; white-space:nowrap; }
    .chart-canvas-wrap { position:relative; height:320px; }
    .chart-empty { margin:14px 0 0; padding:12px 14px; border:1px solid #fde68a; border-radius:12px; background:#fffbeb; color:#92400e; font-size:13px; font-weight:800; }
    .commission-table-wrap { margin-top:18px; overflow-x:auto; border:1px solid var(--border-color); border-radius:12px; }
    .commission-table { width:100%; min-width:640px; border-collapse:collapse; }
    .commission-table th { padding:13px 16px; background:#f8fafc; color:var(--text-muted); border-bottom:1px solid var(--border-color); font-size:11px; font-weight:900; letter-spacing:.04em; text-align:left; text-transform:uppercase; }
    .commission-table td { padding:14px 16px; border-bottom:1px solid var(--border-color); color:var(--text-dark); font-size:13px; }
    .commission-table tr:last-child td { border-bottom:0; }
    @media (max-width:1100px) {
        .page-head, .filter-form { display:block; }
        .page-head > * + *, .filter-form > * + * { margin-top:10px; }
        .metrics-grid { grid-template-columns:repeat(2, minmax(0,1fr)); }
        .chart-head { display:block; }
        .chart-range { margin-top:10px; }
    }
</style>
@endpush

@section('content')
@php
    $money = fn ($amount) => ((float) $amount < 0 ? '-' : '') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';
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
    $typeValue = fn ($transaction) => $transaction->type instanceof \BackedEnum ? $transaction->type->value : (string) $transaction->type;
    $commissionHasData = collect($commissionChartRows ?? [])->contains(fn ($row) => (float) ($row['total_commission'] ?? 0) > 0);
@endphp

<div class="page-head">
    <div>
        <h2 class="page-title">Tổng quan tài chính</h2>
        <p class="page-subtitle">Theo dõi doanh thu, hoa hồng, ví chủ sân và công nợ.</p>
    </div>

    <div class="actions">
        @if(Route::has('admin.debts.index'))
            <a class="btn-soft" href="{{ route('admin.debts.index') }}">Quản lý công nợ</a>
        @endif
        @if(Route::has('admin.withdrawals.index'))
            <a class="btn-soft" href="{{ route('admin.withdrawals.index') }}">Yêu cầu rút tiền</a>
        @endif
    </div>
</div>

<div class="filter-card">
    <form class="filter-form" method="GET" action="{{ route('admin.finance.index') }}">
        <input class="form-control-soft" type="date" name="date_from" value="{{ $dateFrom }}">
        <input class="form-control-soft" type="date" name="date_to" value="{{ $dateTo }}">
        <button class="btn-primary-soft" type="submit">Lọc</button>
        <a class="btn-soft" href="{{ route('admin.finance.index') }}">Xóa lọc</a>
    </form>
</div>

<div class="section-title">Doanh thu nền tảng</div>
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">GMV</div>
        <div class="metric-value blue">{{ $money($gmv) }}</div>
        <div class="metric-note">Tổng giá trị booking đã tính.</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Platform Revenue</div>
        <div class="metric-value green">{{ $money($platformRevenue) }}</div>
        <div class="metric-note">Hoa hồng nền tảng.</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Owner Payout</div>
        <div class="metric-value green">{{ $money($ownerPayout) }}</div>
        <div class="metric-note">Tiền thuộc về chủ sân.</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Settled Bookings</div>
        <div class="metric-value">{{ number_format($settledBookingCount, 0, ',', '.') }}</div>
        <div class="metric-note">Số booking trong phạm vi lọc.</div>
    </div>
</div>

<div class="chart-card">
    <div class="chart-head">
        <div>
            <h3 class="chart-title">Biểu đồ doanh thu hoa hồng</h3>
            <p class="chart-desc">Theo dõi hoa hồng nền tảng theo từng tháng, tách theo online và COD.</p>
        </div>
        <div class="chart-range">
            {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '6 tháng gần nhất' }}
            @if($dateTo)
                - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @endif
        </div>
    </div>

    <div class="chart-canvas-wrap">
        <canvas id="commissionRevenueChart"></canvas>
    </div>

    @unless($commissionHasData)
        <div class="chart-empty">Chưa có dữ liệu hoa hồng trong khoảng thời gian này.</div>
    @endunless

    <div class="commission-table-wrap">
        <table class="commission-table">
            <thead>
                <tr>
                    <th>Tháng</th>
                    <th>Hoa hồng online</th>
                    <th>Hoa hồng COD</th>
                    <th>Tổng hoa hồng</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissionChartRows as $row)
                    <tr>
                        <td><strong>{{ $row['label'] }}</strong></td>
                        <td class="green"><strong>{{ $money($row['online_commission']) }}</strong></td>
                        <td class="amber"><strong>{{ $money($row['cod_commission']) }}</strong></td>
                        <td><strong>{{ $money($row['total_commission']) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:28px; text-align:center;">
                            <strong>Chưa có dữ liệu hoa hồng trong khoảng thời gian này.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="section-title">Ví và công nợ</div>
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Total Wallet Balance</div>
        <div class="metric-value green">{{ $money($totalWalletBalance) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Total Debt</div>
        <div class="metric-value red">{{ $money($totalDebt) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Owners In Debt</div>
        <div class="metric-value amber">{{ number_format($ownersInDebt, 0, ',', '.') }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Pending Withdrawals</div>
        <div class="metric-value amber">{{ $money($pendingWithdrawals) }}</div>
    </div>
</div>

<div class="section-title">Dòng tiền</div>
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Successful Topups</div>
        <div class="metric-value green">{{ $money($successfulTopups) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Approved Withdrawals</div>
        <div class="metric-value red">{{ $money($approvedWithdrawals) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">COD Commission Debt</div>
        <div class="metric-value amber">{{ $money($codCommissionDebt) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Online Booking Credit</div>
        <div class="metric-value green">{{ $money($onlineBookingCredit) }}</div>
    </div>
</div>

<div class="section-title">Tóm tắt dòng tiền</div>
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nhóm dòng tiền</th>
                <th>Số tiền</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Tiền vào ví chủ sân</strong></td>
                <td class="green"><strong>{{ $money((float) $successfulTopups + (float) $onlineBookingCredit) }}</strong></td>
                <td class="muted">Nạp tiền thành công và booking online credit.</td>
            </tr>
            <tr>
                <td><strong>Tiền ra khỏi ví chủ sân</strong></td>
                <td class="red"><strong>{{ $money((float) $approvedWithdrawals + (float) $codCommissionDebt) }}</strong></td>
                <td class="muted">Rút tiền đã duyệt và hoa hồng COD đã trừ.</td>
            </tr>
            <tr>
                <td><strong>Công nợ hiện tại</strong></td>
                <td class="red"><strong>{{ $money($totalDebt) }}</strong></td>
                <td class="muted">Tổng phần âm của ví chủ sân.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section-title">Top chủ sân công nợ cao</div>
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Chủ sân</th>
                <th>Số dư ví</th>
                <th>Công nợ</th>
                <th>Hạn mức</th>
                <th>Tỷ lệ sử dụng</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
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
                    <td class="red"><strong>{{ $money($wallet->balance) }}</strong></td>
                    <td class="red"><strong>{{ $money($summary['debt_amount'] ?? 0) }}</strong></td>
                    <td>{{ $money($summary['debt_limit'] ?? 0) }}</td>
                    <td>
                        <strong>{{ number_format($usageText, 0, ',', '.') }}%</strong>
                        <div class="progress-track">
                            <div class="progress-bar {{ $status === 'over_limit' ? 'progress-over' : (($status === 'warning') ? 'progress-warning' : '') }}"
                                 style="width: {{ $usageBar }}%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-status {{ $statusClasses[$status] ?? 'status-in_debt' }}">
                            {{ $statusLabels[$status] ?? $status }}
                        </span>
                    </td>
                    <td>
                        @if(Route::has('admin.debts.index'))
                            <a class="btn-soft" href="{{ route('admin.debts.index', ['search' => $owner->email]) }}">Xem công nợ</a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding:36px; text-align:center;">
                        <strong>Không có chủ sân đang nợ.</strong>
                        <div class="muted" style="margin-top:6px;">Khi ví owner âm, dữ liệu sẽ hiển thị ở đây.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section-title">Giao dịch ví mới nhất</div>
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Thời gian</th>
                <th>Chủ sân</th>
                <th>Loại giao dịch</th>
                <th>Số tiền</th>
                <th>Số dư sau</th>
                <th>Mô tả</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestTransactions as $transaction)
                @php
                    $currentType = $typeValue($transaction);
                    $isDebit = in_array($currentType, ['commission_fee', 'commission_cod_debit', 'withdraw', 'withdrawal_debit', 'refund_debit', 'payment'], true);
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
                    <td>
                        <span class="tx-badge">{{ $transactionTypeLabels[$currentType] ?? $currentType }}</span>
                    </td>
                    <td class="{{ $isDebit ? 'red' : 'green' }}">
                        <strong>{{ $isDebit ? '-' : '+' }}{{ $money($transaction->amount) }}</strong>
                    </td>
                    <td><strong>{{ $money($transaction->balance_after) }}</strong></td>
                    <td class="muted">{{ $transaction->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:36px; text-align:center;">
                        <strong>Chưa có giao dịch ví nào.</strong>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
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
