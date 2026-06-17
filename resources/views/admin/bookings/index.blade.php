@extends('admin.layouts.app')

@section('title', 'Quản lý lịch đặt sân')

@push('styles')
<style>
    .booking-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .page-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
    }

    .page-heading h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
    }

    .page-heading p {
        color: var(--text-muted);
        font-size: 14px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }

    .summary-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 18px;
        min-height: 112px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    .summary-card .summary-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .summary-card .summary-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .summary-card .summary-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        color: #27ae60;
        background: var(--primary-light);
    }

    .summary-card .summary-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .summary-card.revenue {
        border-color: rgba(39, 174, 96, 0.35);
    }

    .summary-card.revenue .summary-value {
        color: #27ae60;
    }

    .booking-panel {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        overflow: hidden;
    }

    .booking-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .booking-toolbar h3 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .booking-toolbar span {
        color: var(--text-muted);
        font-size: 12px;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .filter-control {
        min-height: 38px;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-dark);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 13px;
        outline: none;
    }

    .search-box {
        position: relative;
        width: 280px;
    }

    .search-box i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 13px;
    }

    .search-box input {
        width: 100%;
        padding-left: 36px;
    }

    .btn-filter,
    .btn-reset {
        min-height: 38px;
        border-radius: 8px;
        padding: 0 14px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-filter {
        background: #27ae60;
        color: #fff;
    }

    .btn-reset {
        background: #fff;
        color: var(--text-muted);
        border-color: var(--border-color);
    }

    .booking-table-wrap {
        overflow-x: auto;
    }

    .booking-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .booking-table th {
        text-align: left;
        padding: 14px 20px;
        background: #fbfcfc;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .booking-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        font-size: 13px;
    }

    .booking-table tr:last-child td {
        border-bottom: none;
    }

    .booking-id {
        font-weight: 800;
        color: #27ae60;
    }

    .cell-title {
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .cell-subtitle {
        color: var(--text-muted);
        font-size: 12px;
    }

    .time-stack {
        display: grid;
        gap: 5px;
    }

    .time-stack div {
        display: flex;
        align-items: center;
        gap: 7px;
        color: var(--text-dark);
    }

    .price-text {
        color: #27ae60;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge,
    .payment-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 28px;
        border-radius: 999px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-pending { background: #fff8e1; color: #b7791f; }
    .status-confirmed { background: #e8f4fd; color: #2471a3; }
    .status-completed { background: #eafaf1; color: #229954; }
    .status-cancelled { background: #fdedec; color: #c0392b; }
    .status-neutral { background: #f2f3f4; color: #5d6d7e; }
    .payment-paid { background: #eafaf1; color: #229954; }
    .payment-unpaid { background: #f2f3f4; color: #5d6d7e; }

    .empty-state {
        padding: 48px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        display: block;
        font-size: 30px;
        margin-bottom: 12px;
        color: #bdc3c7;
    }

    .booking-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        border-top: 1px solid var(--border-color);
        color: var(--text-muted);
        font-size: 12px;
    }

    .booking-footer nav {
        display: flex;
        justify-content: flex-end;
    }

    .pagination {
        display: flex;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-link {
        min-width: 34px;
        min-height: 34px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        display: grid;
        place-items: center;
        color: var(--text-dark);
        padding: 0 10px;
        font-weight: 700;
        font-size: 12px;
    }

    .page-item.active .page-link {
        background: #27ae60;
        border-color: #27ae60;
        color: #fff;
    }

    .page-item.disabled .page-link {
        color: #bdc3c7;
        background: #f8f9fa;
    }

    @media (max-width: 1180px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .booking-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .filter-form,
        .search-box {
            width: 100%;
        }
    }

    @media (max-width: 640px) {
        .page-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .booking-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="booking-page">
    <div class="page-heading">
        <div>
            <h2>Quản lý lịch đặt sân</h2>
            <p>Theo dõi toàn bộ booking, trạng thái xử lý và doanh thu đặt sân.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-reset">
            <i class="fa-solid fa-arrow-left"></i>
            Dashboard
        </a>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-top">
                <div class="summary-label">Tổng booking</div>
                <div class="summary-icon"><i class="fa-regular fa-calendar-check"></i></div>
            </div>
            <div class="summary-value">{{ number_format($bookingStats['total']) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-top">
                <div class="summary-label">Chờ duyệt</div>
                <div class="summary-icon"><i class="fa-regular fa-clock"></i></div>
            </div>
            <div class="summary-value">{{ number_format($bookingStats['pending']) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-top">
                <div class="summary-label">Đã xác nhận</div>
                <div class="summary-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="summary-value">{{ number_format($bookingStats['confirmed']) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-top">
                <div class="summary-label">Hoàn thành</div>
                <div class="summary-icon"><i class="fa-solid fa-flag-checkered"></i></div>
            </div>
            <div class="summary-value">{{ number_format($bookingStats['completed']) }}</div>
        </div>

        <div class="summary-card revenue">
            <div class="summary-top">
                <div class="summary-label">Doanh thu</div>
                <div class="summary-icon"><i class="fa-solid fa-wallet"></i></div>
            </div>
            <div class="summary-value">{{ number_format($bookingStats['revenue']) }}đ</div>
        </div>
    </div>

    <div class="booking-panel">
        <div class="booking-toolbar">
            <div>
                <h3>Danh sách booking</h3>
                <span>{{ number_format($bookings->total()) }} kết quả phù hợp</span>
            </div>

            <form action="{{ route('admin.bookings.index') }}" method="GET" class="filter-form">
                <select name="status" class="filter-control" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="search"
                        class="filter-control"
                        placeholder="Tên khách, email, mã booking..."
                        value="{{ request('search') }}"
                    >
                </div>

                <button class="btn-filter" type="submit">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.bookings.index') }}" class="btn-reset">
                        <i class="fa-solid fa-rotate-left"></i>
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        <div class="booking-table-wrap">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách đặt</th>
                        <th>Sân / Cơ sở</th>
                        <th>Thời gian</th>
                        <th>Số tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $statusClass = match ($booking->status) {
                                'pending' => 'status-pending',
                                'confirmed' => 'status-confirmed',
                                'completed' => 'status-completed',
                                'cancelled', 'rejected' => 'status-cancelled',
                                default => 'status-neutral',
                            };

                            $statusText = match ($booking->status) {
                                'pending' => 'Chờ duyệt',
                                'confirmed' => 'Đã xác nhận',
                                'completed' => 'Đã hoàn thành',
                                'cancelled' => 'Đã hủy',
                                'rejected' => 'Từ chối',
                                default => ucfirst($booking->status),
                            };

                            $paymentStatus = $booking->payment_status ?? 'unpaid';
                            $paymentText = $paymentStatus === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
                            $paymentClass = $paymentStatus === 'paid' ? 'payment-paid' : 'payment-unpaid';
                        @endphp
                        <tr>
                            <td>
                                <div class="booking-id">#BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</div>
                                <div class="cell-subtitle">{{ optional($booking->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                @if($booking->user)
                                    <div class="cell-title">{{ $booking->user->name }}</div>
                                    <div class="cell-subtitle">{{ $booking->user->email }}</div>
                                @else
                                    <div class="cell-title">N/A</div>
                                    <div class="cell-subtitle">Không có thông tin khách</div>
                                @endif
                            </td>
                            
                            <td>
                                @if($booking->court && $booking->court->venue)
                                    <div class="cell-title">{{ $booking->court->venue->name }}</div>
                                    <div class="cell-subtitle">Sân con: {{ $booking->court->name }}</div>
                                @else
                                    <div class="cell-title">Sân không tồn tại</div>
                                    <div class="cell-subtitle">Dữ liệu sân đã bị thay đổi</div>
                                @endif
                            </td>
                            
                            <td>
                                <div class="time-stack">
                                    <div>
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $booking->slot_date ? $booking->slot_date->format('d/m/Y') : 'N/A' }}
                                    </div>
                                    <div>
                                        <i class="fa-regular fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-text">{{ number_format($booking->total_price) }}đ</div>
                            </td>
                            <td>
                                <span class="payment-badge {{ $paymentClass }}">{{ $paymentText }}</span>
                            </td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa-regular fa-calendar-xmark"></i>
                                    Không tìm thấy booking nào.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="booking-footer">
            <div>
                Hiển thị {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} trong {{ number_format($bookings->total()) }} booking
            </div>
            @if($bookings->hasPages())
                {{ $bookings->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>
</div>
@endsection