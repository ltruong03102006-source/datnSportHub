@extends('layouts.app')

@section('title', 'Chi tiết giao dịch | SportHub')

@section('content')
@php
    use Carbon\Carbon;

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
        $dateLabel = Carbon::parse($booking->slot_date)->format('d/m/Y');
    } elseif ($bookingPackage?->start_date && $bookingPackage?->end_date) {
        $dateLabel = Carbon::parse($bookingPackage->start_date)->format('d/m/Y')
            . ' - '
            . Carbon::parse($bookingPackage->end_date)->format('d/m/Y');
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
    .receipt-page {
        min-height: calc(100vh - 80px);
        background: #f6f8fb;
        padding: 32px 16px;
    }

    .receipt-container {
        max-width: 1080px;
        margin: 0 auto;
    }

    .receipt-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .receipt-back {
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
    }

    .receipt-back:hover {
        color: #047857;
        border-color: #a7f3d0;
        background: #ecfdf5;
    }

    .receipt-code-small {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    .receipt-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid #dbeafe;
        border-radius: 28px;
        background:
            radial-gradient(circle at 90% 10%, rgba(16, 185, 129, .17), transparent 32%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        padding: 26px;
        box-shadow: 0 20px 48px rgba(15, 23, 42, .07);
    }

    .receipt-hero-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 20px;
        align-items: center;
    }

    .receipt-kicker {
        margin: 0 0 8px;
        color: #059669;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .receipt-title {
        margin: 0;
        color: #0f172a;
        font-size: 30px;
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .receipt-subtitle {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .receipt-amount-box {
        min-width: 250px;
        border: 1px solid #d1fae5;
        border-radius: 22px;
        background: #ecfdf5;
        padding: 18px;
        text-align: right;
    }

    .receipt-amount-box span {
        display: block;
        color: #047857;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .receipt-amount-box strong {
        display: block;
        margin-top: 8px;
        color: #064e3b;
        font-size: 30px;
        line-height: 1;
        font-weight: 950;
    }

    .receipt-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 16px;
    }

    .receipt-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
    }

    .receipt-summary-card span {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .receipt-summary-card strong {
        display: block;
        margin-top: 7px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
        word-break: break-word;
    }

    .receipt-pill {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        border-radius: 999px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .receipt-pill.is-pending {
        color: #b45309;
        background: #fef3c7;
    }

    .receipt-pill.is-success {
        color: #047857;
        background: #d1fae5;
    }

    .receipt-pill.is-failed {
        color: #be123c;
        background: #ffe4e6;
    }

    .receipt-pill.is-refunded {
        color: #4338ca;
        background: #e0e7ff;
    }

    .receipt-pill.type-booking {
        color: #0369a1;
        background: #e0f2fe;
    }

    .receipt-pill.type-package {
        color: #047857;
        background: #d1fae5;
    }

    .receipt-pill.method {
        color: #334155;
        background: #f1f5f9;
    }

    .receipt-grid {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 16px;
        margin-top: 16px;
    }

    .receipt-card {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .receipt-card-head {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .receipt-card-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }

    .receipt-card-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .receipt-card-body {
        padding: 18px;
    }

    .receipt-info {
        display: grid;
        gap: 12px;
    }

    .receipt-info-row {
        display: grid;
        grid-template-columns: 145px 1fr;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .receipt-info-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .receipt-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 850;
    }

    .receipt-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        word-break: break-word;
    }

    .receipt-value.money {
        color: #047857;
        font-weight: 950;
    }

    .receipt-code {
        color: #0f172a;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        font-weight: 900;
    }

    .receipt-mini-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .receipt-mini-stat {
        border-radius: 16px;
        background: #f8fafc;
        padding: 12px;
    }

    .receipt-mini-stat span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .receipt-mini-stat strong {
        display: block;
        margin-top: 4px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
    }

    .receipt-timeline {
        display: grid;
        gap: 14px;
    }

    .receipt-timeline-item {
        position: relative;
        padding-left: 28px;
    }

    .receipt-timeline-item::before {
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

    .receipt-timeline-item::after {
        content: "";
        position: absolute;
        top: 22px;
        left: 10px;
        width: 2px;
        height: calc(100% + 3px);
        background: #e2e8f0;
    }

    .receipt-timeline-item:last-child::after {
        display: none;
    }

    .receipt-timeline-item strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .receipt-timeline-item span {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
    }

    .session-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .session-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px;
    }

    .session-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .session-card h4 {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .session-card p {
        margin: 8px 0 0;
        color: #475569;
        font-size: 13px;
        font-weight: 750;
    }

    .slot-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .slot-list span {
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

    @media (max-width: 1000px) {
        .receipt-hero-grid,
        .receipt-grid {
            grid-template-columns: 1fr;
        }

        .receipt-amount-box {
            text-align: left;
        }

        .receipt-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .receipt-page {
            padding: 24px 12px;
        }

        .receipt-topbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .receipt-title {
            font-size: 25px;
        }

        .receipt-summary,
        .receipt-mini-stats,
        .session-grid {
            grid-template-columns: 1fr;
        }

        .receipt-info-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>

<div class="receipt-page">
    <div class="receipt-container">
        <div class="receipt-topbar">
            <a href="{{ route('transactions.index') }}" class="receipt-back">
                ← Quay lại giao dịch
            </a>

            <div class="receipt-code-small">
                Mã GD: {{ $transaction->transaction_code ?? '—' }}
            </div>
        </div>

        <div class="receipt-hero">
            <div class="receipt-hero-grid">
                <div>
                    <p class="receipt-kicker">Biên lai thanh toán</p>

                    <h1 class="receipt-title">
                        Chi tiết giao dịch
                    </h1>

                    <p class="receipt-subtitle">
                        Giao dịch cho {{ strtolower($orderTypeLabel) }}
                        tại {{ $venue?->name ?? 'SportHub' }}.
                    </p>
                </div>

                <div class="receipt-amount-box">
                    <span>Số tiền thanh toán</span>
                    <strong>{{ number_format((float) $transaction->amount, 0, ',', '.') }}₫</strong>
                </div>
            </div>
        </div>

        <div class="receipt-summary">
            <div class="receipt-summary-card">
                <span>Trạng thái</span>
                <strong>
                    <span class="receipt-pill {{ $statusItem['class'] }}">
                        {{ $transaction->status_label ?? $statusItem['label'] }}
                    </span>
                </strong>
            </div>

            <div class="receipt-summary-card">
                <span>Loại đơn</span>
                <strong>
                    <span class="receipt-pill {{ $isPackageTransaction ? 'type-package' : 'type-booking' }}">
                        {{ $orderTypeLabel }}
                    </span>
                </strong>
            </div>

            <div class="receipt-summary-card">
                <span>Mã đơn</span>
                <strong>{{ $orderLabel }}</strong>
            </div>

            <div class="receipt-summary-card">
                <span>Thời gian</span>
                <strong>{{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}</strong>
            </div>
        </div>

        <div class="receipt-grid">
            <div class="receipt-card">
                <div class="receipt-card-head">
                    <h3>Thông tin đơn</h3>
                    <p>Chi tiết sân, ngày đặt và nội dung liên kết với giao dịch.</p>
                </div>

                <div class="receipt-card-body">
                    <div class="receipt-info">
                        <div class="receipt-info-row">
                            <div class="receipt-label">Cơ sở sân</div>
                            <div class="receipt-value">{{ $venue?->name ?? '—' }}</div>
                        </div>

                        <div class="receipt-info-row">
                            <div class="receipt-label">Địa chỉ</div>
                            <div class="receipt-value">{{ $venue?->address ?? '—' }}</div>
                        </div>

                        @if($booking)
                            <div class="receipt-info-row">
                                <div class="receipt-label">Sân con</div>
                                <div class="receipt-value">{{ $booking->court?->name ?? '—' }}</div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Ngày đặt</div>
                                <div class="receipt-value">{{ $dateLabel }}</div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Khung giờ</div>
                                <div class="receipt-value">{{ $timeLabel }}</div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Trạng thái booking</div>
                                <div class="receipt-value">{{ $booking->status ?? '—' }}</div>
                            </div>
                        @elseif($bookingPackage)
                            <div class="receipt-info-row">
                                <div class="receipt-label">Tên gói</div>
                                <div class="receipt-value">{{ $bookingPackage->package?->name ?? '—' }}</div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Thời gian gói</div>
                                <div class="receipt-value">{{ $dateLabel }}</div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Trạng thái gói</div>
                                <div class="receipt-value">
                                    {{ $packageStatusLabels[$bookingPackage->status] ?? $bookingPackage->status }}
                                </div>
                            </div>

                            <div class="receipt-mini-stats">
                                <div class="receipt-mini-stat">
                                    <span>Buổi/tuần</span>
                                    <strong>{{ $bookingPackage->weekly_sessions ?? 0 }}</strong>
                                </div>

                                <div class="receipt-mini-stat">
                                    <span>Tổng buổi</span>
                                    <strong>{{ $bookingPackage->total_sessions ?? 0 }}</strong>
                                </div>

                                <div class="receipt-mini-stat">
                                    <span>Đã dùng</span>
                                    <strong>{{ $bookingPackage->used_sessions ?? 0 }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="receipt-info-row">
                                <div class="receipt-label">Thông tin đơn</div>
                                <div class="receipt-value">Không tìm thấy đơn liên kết.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="receipt-card">
                <div class="receipt-card-head">
                    <h3>Thanh toán</h3>
                    <p>Thông tin phương thức, cổng thanh toán và người thanh toán.</p>
                </div>

                <div class="receipt-card-body">
                    <div class="receipt-info">
                        <div class="receipt-info-row">
                            <div class="receipt-label">Mã giao dịch</div>
                            <div class="receipt-value receipt-code">
                                {{ $transaction->transaction_code ?? '—' }}
                            </div>
                        </div>

                        <div class="receipt-info-row">
                            <div class="receipt-label">Phương thức</div>
                            <div class="receipt-value">
                                <span class="receipt-pill method">
                                    {{ $transaction->payment_method ?: '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="receipt-info-row">
                            <div class="receipt-label">Cổng thanh toán</div>
                            <div class="receipt-value">
                                {{ $transaction->payment_gateway ?: '—' }}
                            </div>
                        </div>

                        <div class="receipt-info-row">
                            <div class="receipt-label">Người thanh toán</div>
                            <div class="receipt-value">
                                {{ $transaction->user?->name ?? '—' }}
                            </div>
                        </div>

                        <div class="receipt-info-row">
                            <div class="receipt-label">Email</div>
                            <div class="receipt-value">
                                {{ $transaction->user?->email ?? '—' }}
                            </div>
                        </div>

                        <div class="receipt-info-row">
                            <div class="receipt-label">Ghi chú</div>
                            <div class="receipt-value">
                                {{ $transaction->note ?: 'Không có' }}
                            </div>
                        </div>

                        @if($bookingPackage)
                            <div class="receipt-info-row">
                                <div class="receipt-label">Giá gốc</div>
                                <div class="receipt-value">
                                    {{ number_format((float) $bookingPackage->total_amount, 0, ',', '.') }}₫
                                </div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Giảm giá</div>
                                <div class="receipt-value money">
                                    {{ number_format((float) $bookingPackage->discount_amount, 0, ',', '.') }}₫
                                </div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Thanh toán</div>
                                <div class="receipt-value money">
                                    {{ number_format((float) $bookingPackage->final_amount, 0, ',', '.') }}₫
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="receipt-grid">
            <div class="receipt-card">
                <div class="receipt-card-head">
                    <h3>Dòng thời gian</h3>
                    <p>Các mốc xử lý quan trọng của giao dịch.</p>
                </div>

                <div class="receipt-card-body">
                    <div class="receipt-timeline">
                        <div class="receipt-timeline-item">
                            <strong>Tạo giao dịch</strong>
                            <span>{{ optional($transaction->created_at)->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>

                        <div class="receipt-timeline-item">
                            <strong>Cập nhật gần nhất</strong>
                            <span>{{ optional($transaction->updated_at)->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>

                        <div class="receipt-timeline-item">
                            <strong>Thời gian thanh toán</strong>
                            <span>{{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}</span>
                        </div>

                        @if($bookingPackage?->paid_at)
                            <div class="receipt-timeline-item">
                                <strong>Gói được kích hoạt</strong>
                                <span>{{ $bookingPackage->paid_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($bookingPackage && $bookingPackage->sessions && $bookingPackage->sessions->isNotEmpty())
                <div class="receipt-card">
                    <div class="receipt-card-head">
                        <h3>Buổi cố định trong gói</h3>
                        <p>Lịch chơi cố định đã đăng ký trong gói.</p>
                    </div>

                    <div class="receipt-card-body">
                        <div class="session-grid">
                            @foreach($bookingPackage->sessions as $session)
                                @php
                                    $slotRows = collect();

                                    if (method_exists($session, 'slots')) {
                                        $slotRows = $session->slots->sortBy('slot_order')->values();
                                    }
                                @endphp

                                <div class="session-card">
                                    <div class="session-card-top">
                                        <h4>Buổi {{ $session->session_order }}</h4>

                                        <span class="receipt-pill method">
                                            {{ $weekdayLabels[(int) $session->weekday] ?? '—' }}
                                        </span>
                                    </div>

                                    <p>{{ $session->court?->name ?? '—' }}</p>

                                    <div class="slot-list">
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

                                    <p class="receipt-value money" style="margin-top: 10px;">
                                        {{ number_format((float) $session->price_per_session, 0, ',', '.') }}₫ / buổi
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="receipt-card">
                    <div class="receipt-card-head">
                        <h3>Ghi chú</h3>
                        <p>Thông tin bổ sung của giao dịch.</p>
                    </div>

                    <div class="receipt-card-body">
                        <div class="receipt-info">
                            <div class="receipt-info-row">
                                <div class="receipt-label">Nội dung</div>
                                <div class="receipt-value">
                                    {{ $transaction->note ?: 'Không có ghi chú bổ sung cho giao dịch này.' }}
                                </div>
                            </div>

                            <div class="receipt-info-row">
                                <div class="receipt-label">Hỗ trợ</div>
                                <div class="receipt-value">
                                    Nếu giao dịch có vấn đề, vui lòng liên hệ cơ sở sân hoặc bộ phận hỗ trợ SportHub.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection