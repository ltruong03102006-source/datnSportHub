@extends('layouts.app')

@section('title', 'Lịch sử giao dịch | SportHub')

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
    .tx-page {
        min-height: calc(100vh - 80px);
        background: #f6f8fb;
        padding: 32px 16px;
    }

    .tx-container {
        max-width: 1120px;
        margin: 0 auto;
    }

    .tx-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .tx-kicker {
        margin: 0 0 6px;
        color: #059669;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .tx-title {
        margin: 0;
        color: #0f172a;
        font-size: 30px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .tx-desc {
        margin: 8px 0 0;
        max-width: 620px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .tx-back {
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
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        transition: .18s ease;
        white-space: nowrap;
    }

    .tx-back:hover {
        color: #047857;
        border-color: #a7f3d0;
        background: #ecfdf5;
    }

    .tx-alert {
        margin-bottom: 14px;
        padding: 13px 15px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 700;
    }

    .tx-alert.success {
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .tx-alert.error {
        border: 1px solid #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .tx-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .tx-stat {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        background: #fff;
        padding: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .045);
    }

    .tx-stat span {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .tx-stat strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 23px;
        font-weight: 900;
    }

    .tx-stat.money strong,
    .tx-stat.success strong {
        color: #047857;
    }

    .tx-stat.pending strong {
        color: #b45309;
    }

    .tx-panel {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .055);
        overflow: hidden;
    }

    .tx-filter {
        padding: 18px;
        margin-bottom: 16px;
    }

    .tx-filter-grid {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr .9fr .9fr auto;
        gap: 12px;
        align-items: end;
    }

    .tx-field label {
        display: block;
        margin-bottom: 7px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .tx-input,
    .tx-select {
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

    .tx-input:focus,
    .tx-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
    }

    .tx-actions {
        display: flex;
        gap: 8px;
    }

    .tx-btn {
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

    .tx-btn.primary {
        background: #059669;
        color: #fff;
    }

    .tx-btn.primary:hover {
        background: #047857;
    }

    .tx-btn.light {
        background: #fff;
        color: #475569;
        border-color: #dbe3ef;
    }

    .tx-btn.light:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .tx-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #eef2f7;
    }

    .tx-list-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
    }

    .tx-list-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .tx-range {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .tx-table-wrap {
        overflow-x: auto;
    }

    .tx-table {
        width: 100%;
        min-width: 920px;
        border-collapse: collapse;
    }

    .tx-table th {
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

    .tx-table td {
        padding: 16px 18px;
        color: #334155;
        font-size: 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .tx-table tr:hover td {
        background: #f8fafc;
    }

    .tx-code {
        color: #0f172a;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        font-weight: 900;
    }

    .tx-main strong {
        display: block;
        color: #0f172a;
        font-weight: 900;
    }

    .tx-main span {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tx-pill {
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

    .tx-pill.booking {
        color: #0369a1;
        background: #e0f2fe;
    }

    .tx-pill.package {
        color: #047857;
        background: #d1fae5;
    }

    .tx-pill.method {
        color: #334155;
        background: #f1f5f9;
    }

    .tx-pill.is-pending {
        color: #b45309;
        background: #fef3c7;
    }

    .tx-pill.is-success {
        color: #047857;
        background: #d1fae5;
    }

    .tx-pill.is-failed {
        color: #be123c;
        background: #ffe4e6;
    }

    .tx-pill.is-refunded {
        color: #4338ca;
        background: #e0e7ff;
    }

    .tx-amount {
        color: #047857;
        font-size: 15px;
        font-weight: 900;
        white-space: nowrap;
    }

    .tx-date {
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .tx-detail {
        color: #2563eb;
        border: 1px solid #bfdbfe;
        background: #fff;
        min-height: 34px;
        padding: 0 11px;
        border-radius: 11px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .tx-detail:hover {
        background: #eff6ff;
        border-color: #60a5fa;
    }

    .tx-empty {
        padding: 58px 20px;
        text-align: center;
    }

    .tx-empty-icon {
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

    .tx-empty h4 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
    }

    .tx-empty p {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .tx-pagination {
        display: flex;
        justify-content: center;
        padding: 18px;
        border-top: 1px solid #eef2f7;
    }

    @media (max-width: 1100px) {
        .tx-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .tx-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .tx-actions {
            grid-column: span 2;
        }
    }

    @media (max-width: 720px) {
        .tx-page {
            padding: 24px 12px;
        }

        .tx-header {
            flex-direction: column;
        }

        .tx-title {
            font-size: 25px;
        }

        .tx-stats,
        .tx-filter-grid {
            grid-template-columns: 1fr;
        }

        .tx-actions {
            grid-column: auto;
        }

        .tx-list-head {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="tx-page">
    <div class="tx-container">
        <div class="tx-header">
            <div>
                <p class="tx-kicker">Thanh toán</p>
                <h1 class="tx-title">Lịch sử giao dịch</h1>
                <p class="tx-desc">
                    Theo dõi các khoản thanh toán đặt sân lẻ và gói đặt sân của bạn tại SportHub.
                </p>
            </div>

            <a href="{{ route('account.bookings.index') }}" class="tx-back">
                ← Lịch sử đặt sân
            </a>
        </div>

        @if (session('success'))
            <div class="tx-alert success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="tx-alert error">
                {{ session('error') }}
            </div>
        @endif

        <div class="tx-stats">
            <div class="tx-stat">
                <span>Tổng giao dịch</span>
                <strong>{{ number_format($totalTransactions) }}</strong>
            </div>

            <div class="tx-stat money">
                <span>Tổng tiền trang này</span>
                <strong>{{ number_format((float) $currentPageTotal, 0, ',', '.') }}₫</strong>
            </div>

            <div class="tx-stat success">
                <span>Thành công</span>
                <strong>{{ number_format($currentPageSuccess) }}</strong>
            </div>

            <div class="tx-stat pending">
                <span>Đang chờ</span>
                <strong>{{ number_format($currentPagePending) }}</strong>
            </div>
        </div>

        <form method="GET" class="tx-panel tx-filter">
            <div class="tx-filter-grid">
                <div class="tx-field">
                    <label>Mã giao dịch</label>
                    <input type="text"
                           name="search_code"
                           class="tx-input"
                           value="{{ request('search_code') }}"
                           placeholder="VD: TXN001">
                </div>

                <div class="tx-field">
                    <label>Mã đơn</label>
                    <input type="text"
                           name="search_booking"
                           class="tx-input"
                           value="{{ request('search_booking') }}"
                           placeholder="VD: 15 hoặc PKG15">
                </div>

                <div class="tx-field">
                    <label>Trạng thái</label>
                    <select name="status" class="tx-select">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Thành công</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Hoàn tiền</option>
                    </select>
                </div>

                <div class="tx-field">
                    <label>Phương thức</label>
                    <select name="payment_method" class="tx-select">
                        <option value="">Tất cả</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="tx-actions">
                    <button type="submit" class="tx-btn primary">
                        Lọc
                    </button>

                    <a href="{{ route('transactions.index') }}" class="tx-btn light">
                        Xóa
                    </a>
                </div>
            </div>
        </form>

        <div class="tx-panel">
            <div class="tx-list-head">
                <div>
                    <h3>Danh sách giao dịch</h3>
                    <p>Các giao dịch mới nhất được hiển thị theo bộ lọc hiện tại.</p>
                </div>

                <div class="tx-range">
                    @if(method_exists($transactions, 'firstItem') && $transactions->firstItem())
                        {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} / {{ $transactions->total() }}
                    @endif
                </div>
            </div>

            @if ($transactions->isEmpty())
                <div class="tx-empty">
                    <div class="tx-empty-icon">₫</div>
                    <h4>Chưa có giao dịch nào</h4>
                    <p>Không tìm thấy giao dịch phù hợp với bộ lọc hiện tại.</p>
                </div>
            @else
                <div class="tx-table-wrap">
                    <table class="tx-table">
                        <thead>
                            <tr>
                                <th>Giao dịch</th>
                                <th>Loại đơn</th>
                                <th>Thông tin đơn</th>
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
                                    $booking = $transaction->booking;
                                    $bookingPackage = $transaction->bookingPackage;
                                    $isPackageTransaction = filled($transaction->booking_package_id) || $bookingPackage;

                                    $venue = $booking?->court?->venue ?? $bookingPackage?->venue;

                                    $orderLabel = $booking
                                        ? '#' . $booking->id
                                        : ($bookingPackage ? 'Gói #' . $bookingPackage->id : '—');

                                    $dateLabel = '—';

                                    if ($booking?->slot_date) {
                                        $dateLabel = \Carbon\Carbon::parse($booking->slot_date)->format('d/m/Y');
                                    } elseif ($bookingPackage?->start_date && $bookingPackage?->end_date) {
                                        $dateLabel = \Carbon\Carbon::parse($bookingPackage->start_date)->format('d/m/Y')
                                            . ' - '
                                            . \Carbon\Carbon::parse($bookingPackage->end_date)->format('d/m/Y');
                                    }

                                    $paymentStatus = $transaction->payment_status ?? $transaction->status ?? 'pending';

                                    $statusItem = $statusConfig[$paymentStatus] ?? [
                                        'label' => $transaction->status_label ?? $paymentStatus,
                                        'class' => 'is-pending',
                                    ];
                                @endphp

                                <tr>
                                    <td>
                                        <span class="tx-code">{{ $transaction->transaction_code ?? '—' }}</span>
                                    </td>

                                    <td>
                                        <span class="tx-pill {{ $isPackageTransaction ? 'package' : 'booking' }}">
                                            {{ $isPackageTransaction ? 'Gói đặt sân' : 'Đặt sân lẻ' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="tx-main">
                                            <strong>{{ $orderLabel }} · {{ $venue?->name ?? '—' }}</strong>
                                            <span>{{ $dateLabel }}{{ $venue?->address ? ' · ' . $venue->address : '' }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="tx-amount">
                                            {{ number_format((float) $transaction->amount, 0, ',', '.') }}₫
                                        </span>
                                    </td>

                                    <td>
                                        <span class="tx-pill method">
                                            {{ $transaction->payment_method ?: '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="tx-pill {{ $statusItem['class'] }}">
                                            {{ $transaction->status_label ?? $statusItem['label'] }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="tx-date">
                                            {{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? '—' }}
                                        </span>
                                    </td>

                                    <td style="text-align: right;">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="tx-btn tx-detail">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tx-pagination">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection