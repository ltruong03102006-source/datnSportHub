@extends('admin.layouts.app')

@section('content')
@php
    $collection = method_exists($transactions, 'getCollection')
        ? $transactions->getCollection()
        : collect($transactions);

    $totalTransactions = method_exists($transactions, 'total')
        ? $transactions->total()
        : $collection->count();

    $currentPageTotal = $collection->sum('amount');

    $currentPageSuccess = $collection->filter(function ($transaction) {
        return in_array($transaction->payment_status ?? $transaction->status, ['success', 'paid', 'completed'], true);
    })->count();

    $currentPagePending = $collection->filter(function ($transaction) {
        return ($transaction->payment_status ?? $transaction->status) === 'pending';
    })->count();

    $statusConfig = [
        'pending' => ['label' => 'Đang chờ', 'class' => 'is-pending'],
        'success' => ['label' => 'Thành công', 'class' => 'is-success'],
        'paid' => ['label' => 'Đã thanh toán', 'class' => 'is-success'],
        'completed' => ['label' => 'Hoàn thành', 'class' => 'is-success'],
        'failed' => ['label' => 'Thất bại', 'class' => 'is-failed'],
        'refunded' => ['label' => 'Hoàn tiền', 'class' => 'is-refunded'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'is-failed'],
    ];
@endphp

<style>
    .admin-tx-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .admin-tx-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background:
            radial-gradient(circle at top right, rgba(16, 185, 129, .12), transparent 30%),
            linear-gradient(135deg, #fff 0%, #f8fafc 100%);
        padding: 22px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .055);
    }

    .admin-tx-kicker {
        margin: 0 0 6px;
        color: #059669;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .admin-tx-title {
        margin: 0;
        color: #0f172a;
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .admin-tx-desc {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .admin-tx-refresh {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 14px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #fff;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .18s ease;
    }

    .admin-tx-refresh:hover {
        color: #047857;
        border-color: #a7f3d0;
        background: #ecfdf5;
    }

    .admin-tx-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .admin-tx-stat {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #fff;
        padding: 17px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .04);
    }

    .admin-tx-stat span {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .admin-tx-stat strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 23px;
        font-weight: 900;
    }

    .admin-tx-stat.money strong,
    .admin-tx-stat.success strong {
        color: #047857;
    }

    .admin-tx-stat.pending strong {
        color: #b45309;
    }

    .admin-tx-panel {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .admin-tx-filter {
        padding: 18px;
    }

    .admin-tx-filter-grid {
        display: grid;
        grid-template-columns: 1.4fr .9fr .9fr .8fr .8fr auto;
        gap: 12px;
        align-items: end;
    }

    .admin-tx-field label {
        display: block;
        margin-bottom: 7px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .admin-tx-input,
    .admin-tx-select {
        width: 100%;
        min-height: 42px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        background: #fff;
        padding: 0 12px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 650;
        outline: none;
    }

    .admin-tx-input:focus,
    .admin-tx-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
    }

    .admin-tx-actions {
        display: flex;
        gap: 8px;
    }

    .admin-tx-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 14px;
        border-radius: 13px;
        border: 1px solid transparent;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        transition: .18s ease;
    }

    .admin-tx-btn.primary {
        background: #059669;
        color: #fff;
    }

    .admin-tx-btn.primary:hover {
        background: #047857;
    }

    .admin-tx-btn.light {
        background: #fff;
        color: #475569;
        border-color: #dbe3ef;
    }

    .admin-tx-btn.light:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .admin-tx-list-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #eef2f7;
    }

    .admin-tx-list-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
    }

    .admin-tx-list-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .admin-tx-range {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .admin-tx-table-wrap {
        overflow-x: auto;
    }

    .admin-tx-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .admin-tx-table th {
        padding: 13px 18px;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .admin-tx-table td {
        padding: 16px 18px;
        color: #334155;
        font-size: 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .admin-tx-table tr:hover td {
        background: #f8fafc;
    }

    .admin-tx-code {
        color: #0f172a;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        font-weight: 900;
    }

    .admin-tx-main strong {
        display: block;
        color: #0f172a;
        font-weight: 900;
    }

    .admin-tx-main span {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        max-width: 270px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-tx-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        border-radius: 999px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .admin-tx-pill.booking {
        color: #0369a1;
        background: #e0f2fe;
    }

    .admin-tx-pill.package {
        color: #047857;
        background: #d1fae5;
    }

    .admin-tx-pill.method {
        color: #334155;
        background: #f1f5f9;
    }

    .admin-tx-pill.is-pending {
        color: #b45309;
        background: #fef3c7;
    }

    .admin-tx-pill.is-success {
        color: #047857;
        background: #d1fae5;
    }

    .admin-tx-pill.is-failed {
        color: #be123c;
        background: #ffe4e6;
    }

    .admin-tx-pill.is-refunded {
        color: #4338ca;
        background: #e0e7ff;
    }

    .admin-tx-amount {
        color: #047857;
        font-size: 15px;
        font-weight: 900;
        white-space: nowrap;
    }

    .admin-tx-date {
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .admin-tx-detail {
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid #bfdbfe;
        border-radius: 11px;
        background: #fff;
        color: #2563eb;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .admin-tx-detail:hover {
        background: #eff6ff;
        border-color: #60a5fa;
    }

    .admin-tx-empty {
        padding: 58px 20px;
        text-align: center;
    }

    .admin-tx-empty-icon {
        display: grid;
        width: 60px;
        height: 60px;
        margin: 0 auto 14px;
        place-items: center;
        border-radius: 20px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 28px;
        font-weight: 900;
    }

    .admin-tx-empty h4 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
    }

    .admin-tx-empty p {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .admin-tx-pagination {
        display: flex;
        justify-content: center;
        padding: 18px;
        border-top: 1px solid #eef2f7;
    }

    @media (max-width: 1200px) {
        .admin-tx-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .admin-tx-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .admin-tx-actions {
            grid-column: span 2;
        }
    }

    @media (max-width: 720px) {
        .admin-tx-header,
        .admin-tx-list-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .admin-tx-title {
            font-size: 24px;
        }

        .admin-tx-stats,
        .admin-tx-filter-grid {
            grid-template-columns: 1fr;
        }

        .admin-tx-actions {
            grid-column: auto;
        }
    }
</style>

<div class="admin-tx-page">
    <div class="admin-tx-header">
        <div>
            <p class="admin-tx-kicker">Admin Payment</p>

            <h1 class="admin-tx-title">
                Lịch sử giao dịch
            </h1>

            <p class="admin-tx-desc">
                Theo dõi toàn bộ giao dịch đặt sân lẻ và giao dịch đặt sân theo gói trong hệ thống.
            </p>
        </div>

        <a href="{{ route('admin.transactions.index') }}" class="admin-tx-refresh">
            Làm mới
        </a>
    </div>

    <div class="admin-tx-stats">
        <div class="admin-tx-stat">
            <span>Tổng giao dịch</span>
            <strong>{{ number_format($totalTransactions) }}</strong>
        </div>

        <div class="admin-tx-stat money">
            <span>Tổng tiền trang này</span>
            <strong>{{ number_format((float) $currentPageTotal, 0, ',', '.') }}₫</strong>
        </div>

        <div class="admin-tx-stat success">
            <span>Thành công</span>
            <strong>{{ number_format($currentPageSuccess) }}</strong>
        </div>

        <div class="admin-tx-stat pending">
            <span>Đang chờ</span>
            <strong>{{ number_format($currentPagePending) }}</strong>
        </div>
    </div>

    <form method="GET" class="admin-tx-panel admin-tx-filter">
        <div class="admin-tx-filter-grid">
            <div class="admin-tx-field">
                <label>Tìm kiếm</label>
                <input type="text"
                       name="search"
                       class="admin-tx-input"
                       value="{{ request('search') }}"
                       placeholder="Mã GD, khách hàng, email, mã đơn">
            </div>

            <div class="admin-tx-field">
                <label>Trạng thái</label>
                <select name="status" class="admin-tx-select">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Thành công</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Hoàn tiền</option>
                </select>
            </div>

            <div class="admin-tx-field">
                <label>Phương thức</label>
                <select name="payment_method" class="admin-tx-select">
                    <option value="">Tất cả</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>
                            {{ $method }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-tx-field">
                <label>Từ ngày</label>
                <input type="date"
                       name="date_from"
                       class="admin-tx-input"
                       value="{{ request('date_from') }}">
            </div>

            <div class="admin-tx-field">
                <label>Đến ngày</label>
                <input type="date"
                       name="date_to"
                       class="admin-tx-input"
                       value="{{ request('date_to') }}">
            </div>

            <div class="admin-tx-actions">
                <button type="submit" class="admin-tx-btn primary">
                    Lọc
                </button>

                <a href="{{ route('admin.transactions.index') }}" class="admin-tx-btn light">
                    Xóa
                </a>
            </div>
        </div>
    </form>

    <div class="admin-tx-panel">
        <div class="admin-tx-list-head">
            <div>
                <h3>Danh sách giao dịch</h3>
                <p>Các giao dịch mới nhất được hiển thị theo bộ lọc hiện tại.</p>
            </div>

            <div class="admin-tx-range">
                @if(method_exists($transactions, 'firstItem') && $transactions->firstItem())
                    {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} / {{ $transactions->total() }}
                @endif
            </div>
        </div>

        @if ($transactions->isEmpty())
            <div class="admin-tx-empty">
                <div class="admin-tx-empty-icon">₫</div>
                <h4>Chưa có giao dịch nào</h4>
                <p>Không tìm thấy giao dịch phù hợp với bộ lọc hiện tại.</p>
            </div>
        @else
            <div class="admin-tx-table-wrap">
                <table class="admin-tx-table">
                    <thead>
                        <tr>
                            <th>Giao dịch</th>
                            <th>Khách hàng / Đơn</th>
                            <th>Loại</th>
                            <th>Số tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $isPackageTransaction = filled($transaction->booking_package_id) || $transaction->bookingPackage;

                                $orderLabel = $transaction->booking
                                    ? '#' . $transaction->booking->id
                                    : ($transaction->bookingPackage ? 'Gói #' . $transaction->bookingPackage->id : '—');

                                $paymentStatus = $transaction->payment_status ?? $transaction->status ?? 'pending';

                                $statusItem = $statusConfig[$paymentStatus] ?? [
                                    'label' => $transaction->status_label ?? $paymentStatus,
                                    'class' => 'is-pending',
                                ];

                                $customerName = $transaction->user?->name ?? '—';
                                $customerEmail = $transaction->user?->email ?? '—';
                            @endphp

                            <tr>
                                <td>
                                    <span class="admin-tx-code">
                                        {{ $transaction->transaction_code ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="admin-tx-main">
                                        <strong>{{ $customerName }} · {{ $orderLabel }}</strong>
                                        <span>{{ $customerEmail }}</span>
                                    </div>
                                </td>

                                <td>
                                    <span class="admin-tx-pill {{ $isPackageTransaction ? 'package' : 'booking' }}">
                                        {{ $isPackageTransaction ? 'Gói đặt sân' : 'Đặt sân lẻ' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-tx-amount">
                                        {{ number_format((float) $transaction->amount, 0, ',', '.') }}₫
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-tx-pill method">
                                        {{ $transaction->payment_method ?: '—' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-tx-pill {{ $statusItem['class'] }}">
                                        {{ $transaction->status_label ?? $statusItem['label'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-tx-date">
                                        {{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? '—' }}
                                    </span>
                                </td>

                                <td style="text-align: right;">
                                    <a href="{{ route('admin.transactions.show', $transaction) }}" class="admin-tx-btn admin-tx-detail">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="admin-tx-pagination">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection