@extends('admin.layouts.app')

@push('styles')
<style>
    .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
    .page-title { color: var(--text-dark); font-size: 24px; font-weight: 900; margin: 0; }
    .page-subtitle { color: var(--text-muted); font-size: 14px; margin: 6px 0 0; }
    .btn-soft { align-items: center; border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-dark); display: inline-flex; font-size: 13px; font-weight: 800; height: 42px; justify-content: center; padding: 0 16px; text-decoration: none; background: #fff; }
    .stats-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
    .stat-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.02); padding: 18px; }
    .stat-label { color: var(--text-muted); font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .stat-value { color: var(--text-dark); font-size: 22px; font-weight: 900; margin-top: 8px; }
    .money-green { color: #047857; }
    .money-red { color: #dc2626; }
    .money-amber { color: #d97706; }
    .filter-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.02); margin-bottom: 18px; padding: 16px; }
    .filter-form { display: grid; grid-template-columns: 1fr 220px auto auto; gap: 10px; }
    .form-control-soft { border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-dark); font-size: 13px; height: 42px; outline: none; padding: 0 14px; width: 100%; }
    .form-control-soft:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(46,204,113,.12); }
    .btn-primary-soft { align-items: center; background: var(--primary); border: 0; border-radius: 10px; color: #fff; cursor: pointer; display: inline-flex; font-size: 13px; font-weight: 900; height: 42px; justify-content: center; padding: 0 16px; white-space: nowrap; }
    .table-wrap { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.02); overflow-x: auto; }
    .debt-table { border-collapse: collapse; min-width: 1180px; width: 100%; }
    .debt-table th { background: #f8fafc; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 11px; font-weight: 900; letter-spacing: .04em; padding: 15px 18px; text-align: left; text-transform: uppercase; }
    .debt-table td { border-bottom: 1px solid var(--border-color); color: var(--text-dark); font-size: 13px; padding: 16px 18px; vertical-align: middle; }
    .debt-table tr:last-child td { border-bottom: 0; }
    .muted { color: var(--text-muted); font-size: 12px; }
    .badge-status { border-radius: 999px; display: inline-flex; font-size: 12px; font-weight: 900; padding: 6px 11px; }
    .status-good { background: #dcfce7; color: #166534; }
    .status-in_debt { background: #fef3c7; color: #92400e; }
    .status-warning { background: #ffedd5; color: #c2410c; }
    .status-over_limit { background: #fee2e2; color: #b91c1c; }
    .progress-track { background: #e2e8f0; border-radius: 999px; height: 9px; margin-top: 7px; overflow: hidden; width: 130px; }
    .progress-bar { border-radius: 999px; height: 100%; }
    .progress-good { background: #10b981; }
    .progress-warning { background: #f59e0b; }
    .progress-over_limit { background: #ef4444; }
    .action-stack { display: flex; flex-wrap: wrap; gap: 8px; }
    .mini-btn { align-items: center; background: #fff; border: 1px solid var(--border-color); border-radius: 9px; color: var(--text-dark); display: inline-flex; font-size: 12px; font-weight: 800; height: 34px; padding: 0 11px; text-decoration: none; }
    @media (max-width: 1100px) {
        .page-head, .filter-form { display: block; }
        .page-head > * + *, .filter-form > * + * { margin-top: 10px; }
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
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
    $progressClasses = [
        'good' => 'progress-good',
        'in_debt' => 'progress-warning',
        'warning' => 'progress-warning',
        'over_limit' => 'progress-over_limit',
    ];
@endphp

<div class="page-head">
    <div>
        <h2 class="page-title">Quản lý công nợ chủ sân</h2>
        <p class="page-subtitle">Theo dõi số dư ví, công nợ và hạn mức của từng chủ sân.</p>
    </div>
    <a class="btn-soft" href="{{ route('admin.debts.index') }}">Làm mới</a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Tổng công nợ</div>
        <div class="stat-value money-red">{{ $money($totalDebt) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Chủ sân đang nợ</div>
        <div class="stat-value money-amber">{{ number_format($ownersInDebt, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Gần hạn mức</div>
        <div class="stat-value money-amber">{{ number_format($nearLimitCount, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vượt hạn mức</div>
        <div class="stat-value money-red">{{ number_format($overLimitCount, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ví dương</div>
        <div class="stat-value money-green">{{ $money($totalPositiveBalance) }}</div>
        <div class="muted">{{ number_format($totalWallets, 0, ',', '.') }} ví owner</div>
    </div>
</div>

<div class="filter-card">
    <form class="filter-form" method="GET" action="{{ route('admin.debts.index') }}">
        <input class="form-control-soft"
               name="search"
               value="{{ $search }}"
               placeholder="Tìm theo tên hoặc email chủ sân...">

        <select class="form-control-soft" name="debt_status">
            <option value="all" @selected($debtStatus === 'all')>Tất cả</option>
            <option value="good" @selected($debtStatus === 'good')>An toàn</option>
            <option value="in_debt" @selected($debtStatus === 'in_debt')>Đang nợ</option>
            <option value="warning" @selected($debtStatus === 'warning')>Gần hạn mức</option>
            <option value="over_limit" @selected($debtStatus === 'over_limit')>Vượt hạn mức</option>
        </select>

        <button class="btn-primary-soft" type="submit">Lọc</button>
        <a class="btn-soft" href="{{ route('admin.debts.index') }}">Xóa lọc</a>
    </form>
</div>

<div class="table-wrap">
    <table class="debt-table">
        <thead>
            <tr>
                <th>Chủ sân</th>
                <th>Số dư ví</th>
                <th>Công nợ</th>
                <th>Hạn mức</th>
                <th>Tỷ lệ sử dụng</th>
                <th>Trạng thái</th>
                <th>Cơ sở</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $wallet = $row['wallet'];
                    $owner = $row['owner'];
                    $summary = $row['summary'];
                    $status = $summary['status'] ?? 'good';
                    $usageText = (float) ($summary['usage_percent'] ?? 0);
                    $usageBar = min(100, max(0, $usageText));
                @endphp
                <tr>
                    <td>
                        <strong>{{ $owner->name }}</strong>
                        <div class="muted">{{ $owner->email }}</div>
                    </td>
                    <td>
                        <strong class="{{ (float) $wallet->balance < 0 ? 'money-red' : 'money-green' }}">
                            {{ $money($wallet->balance) }}
                        </strong>
                    </td>
                    <td class="money-red">
                        <strong>{{ $money($summary['debt_amount'] ?? 0) }}</strong>
                    </td>
                    <td>
                        <strong>{{ $money($summary['debt_limit'] ?? 0) }}</strong>
                    </td>
                    <td>
                        <strong>{{ number_format($usageText, 0, ',', '.') }}%</strong>
                        <div class="progress-track">
                            <div class="progress-bar {{ $progressClasses[$status] ?? 'progress-good' }}"
                                 style="width: {{ $usageBar }}%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-status {{ $statusClasses[$status] ?? 'status-good' }}">
                            {{ $statusLabels[$status] ?? $status }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ number_format($row['venue_count'], 0, ',', '.') }} cơ sở</strong>
                        <div class="muted">Hoạt động: {{ number_format($row['active_venue_count'], 0, ',', '.') }}</div>
                        <div class="muted">Tạm khóa: {{ number_format($row['suspended_venue_count'], 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div class="action-stack">
                            <a class="mini-btn" href="{{ route('admin.venues.index', ['search' => $owner->email]) }}">
                                Xem cơ sở
                            </a>
                            <span class="muted">Ví #{{ $wallet->id }}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding: 42px; text-align: center;">
                        <strong>Chưa có dữ liệu ví chủ sân.</strong>
                        <div class="muted" style="margin-top: 6px;">Khi owner có ví, dữ liệu công nợ sẽ hiển thị ở đây.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $rows->links() }}
</div>
@endsection
