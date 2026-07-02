@extends('admin.layouts.app')

@section('content')
@php
    $booking = $transaction->booking;
    $bookingPackage = $transaction->bookingPackage;

    $isPackageTransaction = filled($transaction->booking_package_id) || $bookingPackage;
    $venue = $booking?->court?->venue ?? $bookingPackage?->venue;

    $orderLabel = $booking
        ? '#' . $booking->id
        : ($bookingPackage ? 'Gói #' . $bookingPackage->id : '—');

    $orderTypeLabel = $isPackageTransaction ? 'Gói đặt sân' : 'Đặt sân lẻ';

    $dateLabel = '—';

    if ($booking?->slot_date) {
        $dateLabel = $booking->slot_date->format('d/m/Y');
    } elseif ($bookingPackage?->start_date && $bookingPackage?->end_date) {
        $dateLabel = $bookingPackage->start_date->format('d/m/Y') . ' - ' . $bookingPackage->end_date->format('d/m/Y');
    }

    $timeLabel = $booking
        ? substr((string) $booking->start_time, 0, 5) . ' - ' . substr((string) $booking->end_time, 0, 5)
        : ($bookingPackage ? (($bookingPackage->total_sessions ?? 0) . ' buổi trong gói') : '—');

    $paymentStatus = $transaction->payment_status ?? $transaction->status ?? 'pending';

    $statusConfig = [
        'pending' => ['label' => 'Đang chờ', 'class' => 'is-pending'],
        'success' => ['label' => 'Thành công', 'class' => 'is-success'],
        'paid' => ['label' => 'Đã thanh toán', 'class' => 'is-success'],
        'completed' => ['label' => 'Hoàn thành', 'class' => 'is-success'],
        'failed' => ['label' => 'Thất bại', 'class' => 'is-failed'],
        'refunded' => ['label' => 'Hoàn tiền', 'class' => 'is-refunded'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'is-failed'],
    ];

    $statusItem = $statusConfig[$paymentStatus] ?? [
        'label' => $transaction->status_label ?? $paymentStatus,
        'class' => 'is-pending',
    ];

    $packageStatusLabels = [
        'pending_payment' => 'Chờ thanh toán',
        'active' => 'Đang hoạt động',
        'paused' => 'Tạm dừng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'expired' => 'Hết hạn',
    ];

    $packageStatusClasses = [
        'pending_payment' => 'is-pending',
        'active' => 'is-success',
        'paused' => 'is-info',
        'completed' => 'is-refunded',
        'cancelled' => 'is-failed',
        'expired' => 'is-muted',
    ];

    $weekdayLabels = [
        0 => 'Chủ nhật',
        1 => 'Thứ 2',
        2 => 'Thứ 3',
        3 => 'Thứ 4',
        4 => 'Thứ 5',
        5 => 'Thứ 6',
        6 => 'Thứ 7',
    ];
@endphp

<style>
    .admin-receipt-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .admin-receipt-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }

    .admin-receipt-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        background: #fff;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        transition: .18s ease;
    }

    .admin-receipt-back:hover {
        color: #047857;
        border-color: #a7f3d0;
        background: #ecfdf5;
    }

    .admin-receipt-code {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    .admin-receipt-hero {
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(16, 185, 129, .15), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        padding: 24px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .06);
    }

    .admin-receipt-hero-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 22px;
        align-items: center;
    }

    .admin-receipt-kicker {
        margin: 0 0 6px;
        color: #059669;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .admin-receipt-title {
        margin: 0;
        color: #0f172a;
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .admin-receipt-desc {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .admin-receipt-amount {
        min-width: 250px;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        background: #f0fdf4;
        padding: 18px;
        text-align: right;
    }

    .admin-receipt-amount span {
        display: block;
        color: #047857;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .admin-receipt-amount strong {
        display: block;
        margin-top: 8px;
        color: #064e3b;
        font-size: 30px;
        font-weight: 950;
        line-height: 1;
    }

    .admin-receipt-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .admin-receipt-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .04);
    }

    .admin-receipt-summary-card span {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .admin-receipt-summary-card strong {
        display: block;
        margin-top: 7px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
        word-break: break-word;
    }

    .admin-receipt-grid {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 18px;
    }

    .admin-receipt-card {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .admin-receipt-card-head {
        padding: 17px 20px;
        border-bottom: 1px solid #eef2f7;
    }

    .admin-receipt-card-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }

    .admin-receipt-card-head p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .admin-receipt-card-body {
        padding: 20px;
    }

    .admin-receipt-info {
        display: grid;
        gap: 12px;
    }

    .admin-receipt-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .admin-receipt-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .admin-receipt-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 850;
    }

    .admin-receipt-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        word-break: break-word;
    }

    .admin-receipt-value.money {
        color: #047857;
        font-weight: 950;
    }

    .admin-receipt-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .admin-receipt-pill {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        border-radius: 999px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .admin-receipt-pill.is-pending {
        color: #b45309;
        background: #fef3c7;
    }

    .admin-receipt-pill.is-success {
        color: #047857;
        background: #d1fae5;
    }

    .admin-receipt-pill.is-failed {
        color: #be123c;
        background: #ffe4e6;
    }

    .admin-receipt-pill.is-refunded {
        color: #4338ca;
        background: #e0e7ff;
    }

    .admin-receipt-pill.is-info {
        color: #0369a1;
        background: #e0f2fe;
    }

    .admin-receipt-pill.is-muted {
        color: #475569;
        background: #f1f5f9;
    }

    .admin-receipt-pill.type-booking {
        color: #0369a1;
        background: #e0f2fe;
    }

    .admin-receipt-pill.type-package {
        color: #047857;
        background: #d1fae5;
    }

    .admin-receipt-pill.method {
        color: #334155;
        background: #f1f5f9;
    }

    .admin-mini-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .admin-mini-stat {
        border-radius: 16px;
        background: #f8fafc;
        padding: 12px;
    }

    .admin-mini-stat span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .admin-mini-stat strong {
        display: block;
        margin-top: 4px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
    }

    .admin-receipt-timeline {
        display: grid;
        gap: 14px;
    }

    .admin-timeline-item {
        position: relative;
        padding-left: 28px;
    }

    .admin-timeline-item::before {
        content: "";
        position: absolute;
        top: 4px;
        left: 5px;
        width: 11px;
        height: 11px;
        border-radius: 999px;
        background: #10b981;
        box-shadow: 0 0 0 4px #d1fae5;
    }

    .admin-timeline-item::after {
        content: "";
        position: absolute;
        top: 22px;
        left: 10px;
        width: 2px;
        height: calc(100% + 3px);
        background: #e2e8f0;
    }

    .admin-timeline-item:last-child::after {
        display: none;
    }

    .admin-timeline-item strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .admin-timeline-item span {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .admin-session-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .admin-session-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px;
    }

    .admin-session-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .admin-session-card h4 {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .admin-session-card p {
        margin: 8px 0 0;
        color: #475569;
        font-size: 13px;
        font-weight: 750;
    }

    .admin-slot-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .admin-slot-list span {
        display: inline-flex;
        width: fit-content;
        border-radius: 999px;
        padding: 5px 9px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 1100px) {
        .admin-receipt-hero-grid,
        .admin-receipt-grid {
            grid-template-columns: 1fr;
        }

        .admin-receipt-amount {
            text-align: left;
        }

        .admin-receipt-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .admin-receipt-top {
            flex-direction: column;
            align-items: flex-start;
        }

        .admin-receipt-title {
            font-size: 24px;
        }

        .admin-receipt-summary,
        .admin-mini-stats,
        .admin-session-grid {
            grid-template-columns: 1fr;
        }

        .admin-receipt-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>

<div class="admin-receipt-page">
    <div class="admin-receipt-top">
        <a href="{{ route('admin.transactions.index') }}" class="admin-receipt-back">
            ← Quay lại danh sách
        </a>

        <div class="admin-receipt-code">
            Mã GD: {{ $transaction->transaction_code ?? '—' }}
        </div>
    </div>

    <div class="admin-receipt-hero">
        <div class="admin-receipt-hero-grid">
            <div>
                <p class="admin-receipt-kicker">Admin Transaction</p>

                <h1 class="admin-receipt-title">
                    Chi tiết giao dịch
                </h1>

                <p class="admin-receipt-desc">
                    Giao dịch {{ strtolower($orderTypeLabel) }}
                    của {{ $transaction->user?->name ?? 'khách hàng' }}
                    tại {{ $venue?->name ?? 'SportHub' }}.
                </p>
            </div>

            <div class="admin-receipt-amount">
                <span>Số tiền giao dịch</span>
                <strong>{{ number_format((float) $transaction->amount, 0, ',', '.') }}₫</strong>
            </div>
        </div>
    </div>

    <div class="admin-receipt-summary">
        <div class="admin-receipt-summary-card">
            <span>Trạng thái</span>
            <strong>
                <span class="admin-receipt-pill {{ $statusItem['class'] }}">
                    {{ $transaction->status_label ?? $statusItem['label'] }}
                </span>
            </strong>
        </div>

        <div class="admin-receipt-summary-card">
            <span>Loại đơn</span>
            <strong>
                <span class="admin-receipt-pill {{ $isPackageTransaction ? 'type-package' : 'type-booking' }}">
                    {{ $orderTypeLabel }}
                </span>
            </strong>
        </div>

        <div class="admin-receipt-summary-card">
            <span>Mã đơn</span>
            <strong>{{ $orderLabel }}</strong>
        </div>

        <div class="admin-receipt-summary-card">
            <span>Thời gian</span>
            <strong>{{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}</strong>
        </div>
    </div>

    <div class="admin-receipt-grid">
        <div class="admin-receipt-card">
            <div class="admin-receipt-card-head">
                <h3>Thông tin đơn</h3>
                <p>Thông tin sân, ngày đặt và đơn liên kết với giao dịch.</p>
            </div>

            <div class="admin-receipt-card-body">
                <div class="admin-receipt-info">
                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Cơ sở sân</div>
                        <div class="admin-receipt-value">{{ $venue?->name ?? '—' }}</div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Địa chỉ</div>
                        <div class="admin-receipt-value">{{ $venue?->address ?? '—' }}</div>
                    </div>

                    @if($booking)
                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Sân con</div>
                            <div class="admin-receipt-value">{{ $booking->court?->name ?? '—' }}</div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Ngày đặt</div>
                            <div class="admin-receipt-value">{{ $dateLabel }}</div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Khung giờ</div>
                            <div class="admin-receipt-value">{{ $timeLabel }}</div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Trạng thái booking</div>
                            <div class="admin-receipt-value">{{ $booking->status ?? '—' }}</div>
                        </div>
                    @elseif($bookingPackage)
                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Tên gói</div>
                            <div class="admin-receipt-value">{{ $bookingPackage->package?->name ?? '—' }}</div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Thời gian gói</div>
                            <div class="admin-receipt-value">{{ $dateLabel }}</div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Trạng thái gói</div>
                            <div class="admin-receipt-value">
                                <span class="admin-receipt-pill {{ $packageStatusClasses[$bookingPackage->status] ?? 'is-muted' }}">
                                    {{ $packageStatusLabels[$bookingPackage->status] ?? $bookingPackage->status }}
                                </span>
                            </div>
                        </div>

                        <div class="admin-mini-stats">
                            <div class="admin-mini-stat">
                                <span>Buổi/tuần</span>
                                <strong>{{ $bookingPackage->weekly_sessions ?? 0 }}</strong>
                            </div>

                            <div class="admin-mini-stat">
                                <span>Tổng buổi</span>
                                <strong>{{ $bookingPackage->total_sessions ?? 0 }}</strong>
                            </div>

                            <div class="admin-mini-stat">
                                <span>Đã dùng</span>
                                <strong>{{ $bookingPackage->used_sessions ?? 0 }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Thông tin đơn</div>
                            <div class="admin-receipt-value">Không tìm thấy đơn liên kết.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-receipt-card">
            <div class="admin-receipt-card-head">
                <h3>Thanh toán & khách hàng</h3>
                <p>Thông tin giao dịch, phương thức thanh toán và khách hàng.</p>
            </div>

            <div class="admin-receipt-card-body">
                <div class="admin-receipt-info">
                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Mã giao dịch</div>
                        <div class="admin-receipt-value admin-receipt-mono">
                            {{ $transaction->transaction_code ?? '—' }}
                        </div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Phương thức</div>
                        <div class="admin-receipt-value">
                            <span class="admin-receipt-pill method">
                                {{ $transaction->payment_method ?: '—' }}
                            </span>
                        </div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Cổng thanh toán</div>
                        <div class="admin-receipt-value">
                            {{ $transaction->payment_gateway ?: '—' }}
                        </div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Khách hàng</div>
                        <div class="admin-receipt-value">
                            {{ $transaction->user?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Email</div>
                        <div class="admin-receipt-value">
                            {{ $transaction->user?->email ?? '—' }}
                        </div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Số điện thoại</div>
                        <div class="admin-receipt-value">
                            {{ $transaction->user?->phone ?? '—' }}
                        </div>
                    </div>

                    <div class="admin-receipt-row">
                        <div class="admin-receipt-label">Ghi chú</div>
                        <div class="admin-receipt-value">
                            {{ $transaction->note ?: 'Không có' }}
                        </div>
                    </div>

                    @if($bookingPackage)
                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Giá gốc</div>
                            <div class="admin-receipt-value">
                                {{ number_format((float) $bookingPackage->total_amount, 0, ',', '.') }}₫
                            </div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Giảm giá</div>
                            <div class="admin-receipt-value money">
                                {{ number_format((float) $bookingPackage->discount_amount, 0, ',', '.') }}₫
                            </div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Thanh toán</div>
                            <div class="admin-receipt-value money">
                                {{ number_format((float) $bookingPackage->final_amount, 0, ',', '.') }}₫
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="admin-receipt-grid">
        <div class="admin-receipt-card">
            <div class="admin-receipt-card-head">
                <h3>Dòng thời gian</h3>
                <p>Các mốc xử lý chính của giao dịch.</p>
            </div>

            <div class="admin-receipt-card-body">
                <div class="admin-receipt-timeline">
                    <div class="admin-timeline-item">
                        <strong>Tạo giao dịch</strong>
                        <span>{{ optional($transaction->created_at)->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>

                    <div class="admin-timeline-item">
                        <strong>Cập nhật gần nhất</strong>
                        <span>{{ optional($transaction->updated_at)->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>

                    <div class="admin-timeline-item">
                        <strong>Thời gian thanh toán</strong>
                        <span>{{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}</span>
                    </div>

                    @if($bookingPackage?->paid_at)
                        <div class="admin-timeline-item">
                            <strong>Gói được kích hoạt</strong>
                            <span>{{ $bookingPackage->paid_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($bookingPackage && $bookingPackage->sessions && $bookingPackage->sessions->isNotEmpty())
            <div class="admin-receipt-card">
                <div class="admin-receipt-card-head">
                    <h3>Buổi cố định trong gói</h3>
                    <p>Lịch chơi cố định khách đã đăng ký.</p>
                </div>

                <div class="admin-receipt-card-body">
                    <div class="admin-session-grid">
                        @foreach($bookingPackage->sessions as $session)
                            @php
                                $slotRows = collect();

                                if (method_exists($session, 'slots')) {
                                    $slotRows = $session->slots->sortBy('slot_order')->values();
                                }
                            @endphp

                            <div class="admin-session-card">
                                <div class="admin-session-top">
                                    <h4>Buổi {{ $session->session_order }}</h4>

                                    <span class="admin-receipt-pill method">
                                        {{ $weekdayLabels[(int) $session->weekday] ?? '—' }}
                                    </span>
                                </div>

                                <p>{{ $session->court?->name ?? '—' }}</p>

                                <div class="admin-slot-list">
                                    @if($slotRows->isNotEmpty())
                                        @foreach($slotRows as $slotRow)
                                            <span>
                                                {{ substr($slotRow->timeSlot?->start_time, 0, 5) }}
                                                -
                                                {{ substr($slotRow->timeSlot?->end_time, 0, 5) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span>
                                            {{ substr($session->timeSlot?->start_time, 0, 5) }}
                                            -
                                            {{ substr($session->timeSlot?->end_time, 0, 5) }}
                                        </span>
                                    @endif
                                </div>

                                <p class="admin-receipt-value money" style="margin-top: 10px;">
                                    {{ number_format((float) $session->price_per_session, 0, ',', '.') }}₫ / buổi
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="admin-receipt-card">
                <div class="admin-receipt-card-head">
                    <h3>Ghi chú quản trị</h3>
                    <p>Thông tin hỗ trợ kiểm tra giao dịch.</p>
                </div>

                <div class="admin-receipt-card-body">
                    <div class="admin-receipt-info">
                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Nội dung</div>
                            <div class="admin-receipt-value">
                                {{ $transaction->note ?: 'Không có ghi chú bổ sung cho giao dịch này.' }}
                            </div>
                        </div>

                        <div class="admin-receipt-row">
                            <div class="admin-receipt-label">Kiểm tra</div>
                            <div class="admin-receipt-value">
                                Admin có thể đối chiếu giao dịch với đơn đặt sân, gói đặt sân và trạng thái thanh toán trong hệ thống.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection