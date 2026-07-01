@extends('admin.layouts.app')

@section('title', 'Quản lý gói đặt sân')

@section('content')
<div class="package-page">
    <div class="page-heading">
        <div>
            <h2>Quản lý gói đặt sân</h2>
            <p>Theo dõi cấu hình gói, trạng thái đăng ký, doanh thu đã thanh toán và doanh thu đang chờ thanh toán.</p>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <span>Tổng gói</span>
            <strong>{{ number_format($stats['total_packages'] ?? 0) }}</strong>
        </div>

        <div class="summary-card">
            <span>Gói đang bật</span>
            <strong>{{ number_format($stats['active_packages'] ?? 0) }}</strong>
        </div>

        <div class="summary-card">
            <span>Người đăng ký</span>
            <strong>{{ number_format($stats['registered_users'] ?? 0) }}</strong>
        </div>

        <div class="summary-card success">
            <span>Doanh thu đã thanh toán</span>
            <strong>{{ number_format($stats['package_revenue'] ?? 0, 0, ',', '.') }}đ</strong>
        </div>

        <div class="summary-card warning">
            <span>Chờ thanh toán</span>
            <strong>{{ number_format($stats['pending_payment_packages'] ?? 0) }}</strong>
        </div>

        <div class="summary-card warning">
            <span>Tiền chờ thanh toán</span>
            <strong>{{ number_format($stats['pending_revenue'] ?? 0, 0, ',', '.') }}đ</strong>
        </div>

        <div class="summary-card">
            <span>Đang hoạt động</span>
            <strong>{{ number_format($stats['active_booking_packages'] ?? 0) }}</strong>
        </div>

        <div class="summary-card">
            <span>Hoàn thành</span>
            <strong>{{ number_format($stats['completed_booking_packages'] ?? 0) }}</strong>
        </div>
    </div>

    <div class="status-grid">
        <div class="status-card">
            <span>Gói tạm dừng</span>
            <strong>{{ number_format($stats['paused_booking_packages'] ?? 0) }}</strong>
        </div>

        <div class="status-card">
            <span>Gói đã hủy / hết hạn</span>
            <strong>{{ number_format($stats['cancelled_booking_packages'] ?? 0) }}</strong>
        </div>

        <div class="status-card">
            <span>Người đã thanh toán</span>
            <strong>{{ number_format($stats['paid_users'] ?? 0) }}</strong>
        </div>

        <div class="status-card">
            <span>Tổng giảm giá</span>
            <strong>{{ number_format($stats['total_discount_amount'] ?? 0, 0, ',', '.') }}đ</strong>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Gói</th>
                    <th>Cơ sở</th>
                    <th>Chủ sân</th>
                    <th>Loại</th>
                    <th>Buổi/tuần</th>
                    <th>Giảm</th>
                    <th>Đăng ký</th>
                    <th>Doanh thu</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>

            <tbody>
                @forelse($packages as $package)
                    @php
                        $durationLabel = $package->type === 'week'
                            ? $package->duration . ' tuần'
                            : $package->duration . ' tháng';

                        $discountLabel = rtrim(rtrim(number_format((float) $package->discount_percent, 2), '0'), '.') . '%';

                        $maxSessionsPerWeek = (int) ($package->max_sessions_per_week ?? 7);

                        $totalRegistered = $package->booking_packages_count ?? 0;
                        $pendingCount = $package->pending_payment_count ?? 0;
                        $activeCount = $package->active_subscribers_count ?? 0;
                        $completedCount = $package->completed_subscribers_count ?? 0;
                        $cancelledCount = $package->cancelled_subscribers_count ?? 0;

                        $revenue = $package->revenue ?? 0;
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $package->name }}</strong>
                            <small>{{ $durationLabel }}</small>
                        </td>

                        <td>
                            <strong>{{ $package->venue?->name ?? '—' }}</strong>
                            <small>{{ $package->venue?->address ?? '' }}</small>
                        </td>

                        <td>
                            {{ $package->venue?->owner?->name ?? '—' }}
                        </td>

                        <td>
                            <span class="type-badge">
                                {{ $package->type === 'week' ? 'Theo tuần' : 'Theo tháng' }}
                            </span>
                        </td>

                        <td>
                            <strong>{{ $maxSessionsPerWeek }} buổi/tuần</strong>

                            @if($maxSessionsPerWeek === 7)
                                <small class="daily-note">Chơi mỗi ngày</small>
                            @endif
                        </td>

                        <td>
                            <strong class="discount-text">{{ $discountLabel }}</strong>
                        </td>

                        <td>
                            <div class="register-box">
                                <strong>{{ number_format($totalRegistered) }}</strong>

                                <small>
                                    Chờ TT: {{ number_format($pendingCount) }}
                                    · Hoạt động: {{ number_format($activeCount) }}
                                    · Xong: {{ number_format($completedCount) }}
                                    · Hủy: {{ number_format($cancelledCount) }}
                                </small>
                            </div>
                        </td>

                        <td>
                            <strong class="revenue-text">
                                {{ number_format($revenue, 0, ',', '.') }}đ
                            </strong>

                            <small>Đã thanh toán</small>
                        </td>

                        <td>
                            <span class="status {{ $package->status === 'active' ? 'active' : 'inactive' }}">
                                {{ $package->status === 'active' ? 'Đang bật' : 'Đang tắt' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">
                            Chưa có gói đặt sân nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $packages->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
    .package-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .page-heading h2 {
        font-size: 26px;
        font-weight: 800;
        margin: 0 0 6px;
        color: var(--text-dark);
    }

    .page-heading p {
        color: var(--text-muted);
        margin: 0;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .summary-card,
    .status-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .summary-card.success {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .summary-card.warning {
        border-color: #fde68a;
        background: #fffbeb;
    }

    .summary-card span,
    .status-card span {
        display: block;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-card strong,
    .status-card strong {
        display: block;
        margin-top: 10px;
        color: var(--text-dark);
        font-size: 24px;
        font-weight: 800;
    }

    .table-card {
        overflow-x: auto;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .table-card table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
    }

    .table-card th {
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-align: left;
    }

    .table-card th,
    .table-card td {
        padding: 15px 18px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: top;
    }

    .table-card td strong {
        display: block;
        color: var(--text-dark);
    }

    .table-card td small {
        display: block;
        margin-top: 4px;
        color: var(--text-muted);
        line-height: 1.45;
    }

    .type-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
        color: #0369a1;
        background: #e0f2fe;
    }

    .discount-text {
        color: #059669 !important;
    }

    .revenue-text {
        color: #047857 !important;
    }

    .daily-note {
        color: #059669 !important;
        font-weight: 700;
    }

    .register-box small {
        max-width: 230px;
    }

    .status {
        display: inline-flex;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .status.active {
        color: #047857;
        background: #d1fae5;
    }

    .status.inactive {
        color: #475569;
        background: #f1f5f9;
    }

    .empty {
        text-align: center;
        color: var(--text-muted);
        padding: 28px !important;
    }

    .pagination-wrap {
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 1200px) {
        .summary-grid,
        .status-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .summary-grid,
        .status-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush