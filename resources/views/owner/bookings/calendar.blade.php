<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lịch đặt sân | SportHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --owner-bg: #f6f8fb;
            --owner-card: #ffffff;
            --owner-border: #e5e7eb;
            --owner-text: #0f172a;
            --owner-muted: #64748b;
            --owner-soft: #f1f5f9;
            --owner-green: #059669;
            --owner-green-soft: #ecfdf5;
            --owner-amber: #d97706;
            --owner-blue: #2563eb;
            --owner-red: #dc2626;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--owner-bg);
            color: var(--owner-text);
        }

        a {
            text-decoration: none;
        }
        .sporthub-nav {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}

.sporthub-nav-left {
    display: flex;
    align-items: center;
    gap: 24px;
}

.sporthub-logo {
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
    background: linear-gradient(to right, #059669, #14b8a6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    white-space: nowrap;
}

.sporthub-breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-left: 20px;
    border-left: 1px solid #e2e8f0;
    font-size: 14px;
    color: #64748b;
}

.sporthub-breadcrumb a,
.sporthub-nav-right a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
    transition: color .2s ease;
}

.sporthub-breadcrumb a:hover,
.sporthub-nav-right a:hover {
    color: #059669;
    text-decoration: none;
}

.sporthub-breadcrumb span:last-child {
    color: #1e293b;
    font-weight: 700;
}

.sporthub-nav-right {
    display: flex;
    align-items: center;
    gap: 28px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .sporthub-nav {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .sporthub-nav-left {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .sporthub-breadcrumb {
        border-left: 0;
        padding-left: 0;
        flex-wrap: wrap;
    }

    .sporthub-nav-right {
        gap: 18px;
        flex-wrap: wrap;
    }
}

        .page-shell {
            max-width: 1560px;
        }

        .topbar {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid var(--owner-border);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--owner-green);
            color: #fff;
            font-weight: 800;
        }

        .nav-link-soft {
            color: var(--owner-muted);
            font-size: 14px;
            font-weight: 700;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .nav-link-soft:hover,
        .nav-link-soft.active {
            color: var(--owner-green);
            background: var(--owner-green-soft);
        }

        .page-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: end;
            margin-bottom: 22px;
        }

        .page-hero h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: 0;
        }

        .page-hero p {
            color: var(--owner-muted);
            margin: 0;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-owner,
        .btn-owner-outline {
            min-height: 40px;
            border-radius: 8px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .btn-owner {
            background: var(--owner-green);
            color: #fff;
        }

        .btn-owner-outline {
            background: #fff;
            color: var(--owner-text);
            border-color: var(--owner-border);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: var(--owner-card);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            padding: 18px;
            min-height: 112px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .stat-card .label {
            color: var(--owner-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 800;
            color: var(--owner-text);
        }

        .stat-card .hint {
            color: var(--owner-muted);
            font-size: 12px;
        }

        .filter-panel,
        .calendar-card,
        .agenda-card {
            background: var(--owner-card);
            border: 1px solid var(--owner-border);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .filter-panel {
            padding: 16px;
            margin-bottom: 18px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1.2fr 1.2fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .filter-panel label {
            color: var(--owner-muted);
            display: block;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .filter-panel .form-select,
        .filter-panel .form-control {
            border-color: var(--owner-border);
            border-radius: 8px;
            font-size: 14px;
            min-height: 42px;
        }

        .filter-panel .form-select:focus,
        .filter-panel .form-control:focus {
            border-color: var(--owner-green);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
        }

        .legend-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
            color: var(--owner-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            background: var(--owner-soft);
            border-radius: 999px;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .schedule-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 18px;
            align-items: start;
        }

        .calendar-card {
            min-height: 760px;
            padding: 16px;
            overflow: hidden;
        }

        .agenda-card {
            position: sticky;
            top: 90px;
            max-height: calc(100vh - 110px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .agenda-header {
            padding: 18px;
            border-bottom: 1px solid var(--owner-border);
        }

        .agenda-header h2 {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .agenda-header p {
            color: var(--owner-muted);
            font-size: 13px;
            margin: 0;
        }

        .agenda-list {
            overflow-y: auto;
            padding: 12px;
        }

        .agenda-item {
            border: 1px solid var(--owner-border);
            border-left-width: 4px;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease;
        }

        .agenda-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .agenda-item .time {
            color: var(--owner-text);
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .agenda-item .title {
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .agenda-item .meta {
            color: var(--owner-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .agenda-empty {
            color: var(--owner-muted);
            text-align: center;
            padding: 36px 18px;
            font-size: 14px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            border-radius: 999px;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 800;
        }

        .status-pending { background: #fff7ed; color: var(--owner-amber); }
        .status-confirmed { background: #ecfdf5; color: var(--owner-green); }
        .status-completed { background: #eff6ff; color: var(--owner-blue); }
        .status-cancelled { background: #f1f5f9; color: #475569; }
        .status-rejected { background: #fef2f2; color: var(--owner-red); }

        .fc {
            --fc-border-color: #e5e7eb;
            --fc-today-bg-color: #ecfdf5;
            color: var(--owner-text);
        }

        .fc .fc-toolbar {
            align-items: center;
            gap: 12px;
        }

        .fc .fc-toolbar-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .fc .fc-button-primary {
            background: #fff;
            border-color: var(--owner-border);
            border-radius: 8px;
            color: var(--owner-text);
            font-size: 13px;
            font-weight: 700;
            box-shadow: none;
            min-height: 34px;
            text-transform: capitalize;
        }

        .fc .fc-button-primary:hover,
        .fc .fc-button-primary:focus {
            background: var(--owner-soft);
            border-color: #cbd5e1;
            color: var(--owner-text);
            box-shadow: none;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: var(--owner-green);
            border-color: var(--owner-green);
            color: #fff;
        }

        .fc .fc-col-header-cell-cushion,
        .fc .fc-timegrid-slot-label-cushion {
            color: var(--owner-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .fc-event {
            border-radius: 6px;
            border: 0 !important;
            cursor: pointer;
            max-width: 100%;
            overflow: hidden;
            padding: 2px 4px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
            pointer-events: auto !important; /* FIX LỖI KHÔNG CLICK ĐƯỢC */
            z-index: 5 !important; /* FIX LỖI KHÔNG CLICK ĐƯỢC */
        }

        .fc-daygrid-event {
            min-height: 24px;
        }

        .fc-timegrid-event,
        .fc-daygrid-event {
            overflow: hidden;
        }

        .fc-event-main {
            font-weight: 700;
            line-height: 1.25;
            max-width: 100%;
            overflow: hidden;
        }

        .booking-event {
            display: grid;
            gap: 1px;
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
        }

        .booking-event-title,
        .booking-event-subtitle {
            display: block;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .booking-event-title {
            font-size: 12px;
            font-weight: 800;
        }

        .booking-event-subtitle {
            font-size: 11px;
            font-weight: 600;
            opacity: 0.92;
        }

        .fc-daygrid-event .booking-event {
            display: block;
        }

        .fc-daygrid-event .booking-event-subtitle {
            display: none;
        }

        .fc-list-event-dot {
            border-width: 5px;
        }

        .modal-content {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.2);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 130px minmax(0, 1fr);
            gap: 12px 16px;
            margin: 0;
        }

        .detail-grid dt {
            color: var(--owner-muted);
            font-weight: 600;
        }

        .detail-grid dd {
            margin: 0;
            font-weight: 700;
        }

        @media (max-width: 1200px) {
            .schedule-layout {
                grid-template-columns: 1fr;
            }

            .agenda-card {
                position: static;
                max-height: none;
            }

            .agenda-list {
                max-height: 420px;
            }
        }

        @media (max-width: 992px) {
            .stats-grid,
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .page-hero {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            .calendar-card {
                min-height: 620px;
                overflow-x: auto;
            }

            #booking-calendar {
                min-width: 780px;
            }

            .fc .fc-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .stats-grid,
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<!-- Top Navigation -->
<nav class="sporthub-nav">
    <div class="sporthub-nav-left">
        <div class="sporthub-logo">
            SportHub
        </div>

        <div class="sporthub-breadcrumb">
            <a href="{{ route('owner.dashboard') }}">
                Dashboard
            </a>
            <span>/</span>
            <span>Lịch đặt sân</span>
        </div>
    </div>

    <div class="sporthub-nav-right">
        @include('owner.partials.notification-bell')

        <a href="{{ route('owner.dashboard') }}">
            Tổng quan
        </a>

        <a href="{{ route('owner.web.venues.index') }}">
            Cơ sở sân
        </a>

        <a href="{{ route('owner.web.calendar.index') }}">
            Lịch đặt sân
        </a>

        <a href="{{ route('owner.web.reschedule.index') }}">
            Yêu cầu đổi lịch
        </a>
        <a href="{{ route('owner.web.packages.index') }}">
            Quản lý gói
        </a>
    </div>
</nav>

<main class="container-fluid page-shell py-4">
    <section class="page-hero">
        <div>
            <h1>Lịch trình đặt sân</h1>
            <p>Xem lịch theo ngày, tuần, tháng và xử lý nhanh các booking đang chờ xác nhận.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn-owner-outline" id="go-today">
                Hôm nay
            </button>
            <a href="{{ route('owner.web.venues.index') }}" class="btn-owner">
                Quản lý sân
            </a>
        </div>
    </section>

    <section class="stats-grid" aria-label="Tổng quan lịch đặt">
        <div class="stat-card">
            <div class="label">Lịch hôm nay</div>
            <div class="value" id="today-booking-count">{{ $todayBookings }}</div>
            <div class="hint">Không tính đơn đã hủy/từ chối</div>
        </div>

        <div class="stat-card">
            <div class="label">Đã xác nhận</div>
            <div class="value text-success">{{ $confirmedBookings }}</div>
            <div class="hint">Lịch sắp phục vụ</div>
        </div>
        <div class="stat-card">
            <div class="label">Tuần này</div>
            <div class="value">{{ $weekBookings }}</div>
            <div class="hint">Booking đang hoạt động</div>
        </div>
        <div class="stat-card">
            <div class="label">Sân con</div>
            <div class="value">{{ $totalCourts }}</div>
            <div class="hint">{{ $venues->count() }} cơ sở đang quản lý</div>
        </div>
    </section>

    <section class="filter-panel">
        <div class="filter-grid">
            <div>
                <label for="venue-filter">Điểm sân</label>
                <select id="venue-filter" class="form-select">
                    <option value="">Tất cả điểm sân</option>
                    @foreach ($venues as $venue)
                        <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="court-filter">Sân con</label>
                <select id="court-filter" class="form-select">
                    <option value="">Tất cả sân con</option>
                    @foreach ($venues as $venue)
                        @foreach ($venue->courts as $court)
                            <option value="{{ $court->id }}" data-venue="{{ $venue->id }}">
                                {{ $venue->name }} - {{ $court->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status-filter">Trạng thái</label>
                <select id="status-filter" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="completed">Đã hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <div>
                <label for="date-jump">Tới ngày</label>
                <input id="date-jump" type="date" class="form-control">
            </div>
        </div>

        <div class="legend-strip" aria-label="Chú thích trạng thái">
            <span class="legend-item"><i class="legend-dot" style="background:#047857"></i> Đã xác nhận</span>
            <span class="legend-item"><i class="legend-dot" style="background:#2563eb"></i> Đã hoàn thành</span>
            <span class="legend-item"><i class="legend-dot" style="background:#64748b"></i> Đã hủy</span>
        </div>
    </section>

    <section class="schedule-layout">
        <div class="calendar-card">
            <div id="booking-calendar"></div>
        </div>

        <aside class="agenda-card">
            <div class="agenda-header">
                <h2>Lịch trong khung đang xem</h2>
                <p id="agenda-range-label">Các booking sẽ hiện ở đây sau khi tải lịch.</p>
            </div>
            <div id="agenda-list" class="agenda-list">
                <div class="agenda-empty">Đang tải lịch...</div>
            </div>
        </aside>
    </section>
</main>

<div class="modal fade" id="booking-detail-modal" tabindex="-1" aria-labelledby="booking-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="small text-secondary" id="booking-code"></div>
                    <h2 class="modal-title fs-5 fw-bold" id="booking-detail-title">Chi tiết lịch đặt</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div id="booking-action-alert" class="alert d-none" role="alert"></div>
                <dl class="detail-grid">
                    <dt>Khách hàng</dt>
                    <dd>
                        <div id="detail-customer" class="fw-bold"></div>
                        <small class="text-secondary fw-normal d-block" id="detail-email"></small>
                        
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span id="detail-phone" class="badge bg-light text-dark border border-secondary-subtle fs-6"></span>
                            <a href="#" id="btn-call-customer" class="btn btn-sm btn-success p-1 px-2" title="Gọi điện">
                                <svg class="w-4 h-4" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </a>
                            <a href="#" target="_blank" id="btn-zalo-customer" class="btn btn-sm btn-primary p-1 px-2" title="Chat Zalo">
                                <span style="font-size: 12px; font-weight: bold;">Zalo</span>
                            </a>
                        </div>
                        </dd>
                    <dt>Điểm sân</dt>
                    <dd id="detail-venue"></dd>
                    <dt>Sân con</dt>
                    <dd id="detail-court"></dd>
                    <dt>Thời gian</dt>
                    <dd id="detail-time"></dd>

                    <dt>Trạng thái</dt>
                    <dd><span class="status-pill" id="detail-status"></span></dd>
                    <dt>Tổng tiền</dt>
                    <dd id="detail-price"></dd>
               
                    <dt class="d-none" id="detail-cancel-label">Lý do hủy</dt>
                    <dd class="d-none text-danger" id="detail-cancel-reason"></dd>
                </dl>
            </div>
            <div id="booking-actions" class="modal-footer d-none">
                <button type="button" id="reject-booking" class="btn btn-outline-danger">Từ chối</button>
                <button type="button" id="confirm-booking" class="btn btn-success">Xác nhận</button>
            </div>
            <div id="booking-cancel" class="modal-footer d-none flex-column align-items-stretch gap-2">
                <div class="w-100 text-start">
                    <label for="cancel-reason" class="form-label small fw-semibold text-danger mb-1">Lý do hủy gửi cho khách</label>
                    <textarea id="cancel-reason" class="form-control form-control-sm" rows="2" maxlength="1000" placeholder="Ví dụ: Sân bảo trì đột xuất, xin lỗi quý khách..."></textarea>
                </div>
                <button type="button" id="cancel-booking" class="btn btn-danger w-100">Hủy đơn đã xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const venueFilter = document.getElementById('venue-filter');
        const courtFilter = document.getElementById('court-filter');
        const statusFilter = document.getElementById('status-filter');
        const dateJump = document.getElementById('date-jump');
        const goToday = document.getElementById('go-today');
        const agendaList = document.getElementById('agenda-list');
        const agendaRangeLabel = document.getElementById('agenda-range-label');
        const courtOptions = Array.from(courtFilter.options).slice(1);
        const detailModal = new bootstrap.Modal(document.getElementById('booking-detail-modal'));
        const bookingActions = document.getElementById('booking-actions');
        const actionAlert = document.getElementById('booking-action-alert');
        const confirmButton = document.getElementById('confirm-booking');
        const rejectButton = document.getElementById('reject-booking');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const statusUrlTemplate = @js(route('owner.web.calendar.bookings.status', ['booking' => '__BOOKING__']));
        const cancelUrlTemplate = @js(route('owner.web.calendar.bookings.cancel', ['booking' => '__BOOKING__']));
        const bookingCancel = document.getElementById('booking-cancel');
        const cancelReason = document.getElementById('cancel-reason');
        const cancelButton = document.getElementById('cancel-booking');
        const detailCancelLabel = document.getElementById('detail-cancel-label');
        const detailCancelReason = document.getElementById('detail-cancel-reason');
        let selectedBookingId = null;

        const statusClasses = {
            pending: 'status-pending',
            confirmed: 'status-confirmed',
            completed: 'status-completed',
            cancelled: 'status-cancelled',
            rejected: 'status-rejected',
        };

        const calendar = new FullCalendar.Calendar(document.getElementById('booking-calendar'), {
            locale: 'vi',
            initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
            firstDay: 1,
            nowIndicator: true,
            height: 'auto',
            slotMinTime: '05:00:00',
            slotMaxTime: '24:00:00',
            allDaySlot: false,
            expandRows: true,
            navLinks: true,
            stickyHeaderDates: true,
            dayMaxEvents: 3,
            eventMaxStack: 4,
            slotEventOverlap: false,
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek',
            },
            buttonText: {
                today: 'Hôm nay',
                day: 'Ngày',
                week: 'Tuần',
                month: 'Tháng',
                list: 'Danh sách',
            },
            events: {
                url: @js(route('owner.web.calendar.events')),
                extraParams: () => ({
                    venue_id: venueFilter.value,
                    court_id: courtFilter.value,
                    status: statusFilter.value,
                }),
                failure: () => {
                    agendaList.innerHTML = '<div class="agenda-empty text-danger">Không tải được dữ liệu lịch đặt. Vui lòng thử lại.</div>';
                },
            },
            eventContent: ({ event, view }) => {
                const booking = event.extendedProps;
                const isMonthView = view.type === 'dayGridMonth';
                const title = isMonthView
                    ? `${booking.time_label} · ${booking.court_name}`
                    : `${booking.time_label} · ${booking.court_name}`;
                const subtitle = booking.customer_name;

                return {
                    html: `
                        <div class="booking-event" title="${escapeHtml(booking.court_name)} - ${escapeHtml(booking.customer_name)}">
                            <span class="booking-event-title">${escapeHtml(title)}</span>
                            <span class="booking-event-subtitle">${escapeHtml(subtitle)}</span>
                        </div>
                    `,
                };
            },
            eventClick: ({ event }) => showBookingDetail(event),
            eventsSet: (events) => renderAgenda(events),
            datesSet: (info) => {
                agendaRangeLabel.textContent = `${formatDate(info.start)} - ${formatDate(addDays(info.end, -1))}`;
            },
        });

        function addDays(date, days) {
            const next = new Date(date);
            next.setDate(next.getDate() + days);
            return next;
        }

        function formatDate(date) {
            return new Intl.DateTimeFormat('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            }).format(date);
        }

        function renderAgenda(events) {
            const sorted = [...events].sort((a, b) => a.start - b.start);

            if (sorted.length === 0) {
                agendaList.innerHTML = '<div class="agenda-empty">Không có booking nào trong khung thời gian này.</div>';
                return;
            }

            agendaList.innerHTML = sorted.map((event) => {
                const booking = event.extendedProps;
                const color = event.backgroundColor || '#64748b';

                return `
                    <article class="agenda-item" data-event-id="${event.id}" style="border-left-color:${color}">
                        <div class="time">${booking.date_label} · ${booking.time_label}</div>
                        <div class="title">${escapeHtml(booking.court_name)} - ${escapeHtml(booking.customer_name)}</div>
                        <div class="meta">${escapeHtml(booking.venue_name)}</div>
                        <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
                            <span class="status-pill ${statusClasses[booking.status] || 'status-cancelled'}">${escapeHtml(booking.status_label)}</span>
                            <strong>${escapeHtml(booking.total_price)}</strong>
                        </div>
                    </article>
                `;
            }).join('');

            agendaList.querySelectorAll('.agenda-item').forEach((item) => {
                item.addEventListener('click', () => {
                    const event = calendar.getEventById(item.dataset.eventId);
                    if (event) {
                        showBookingDetail(event);
                    }
                });
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function showBookingDetail(event) {
            const booking = event.extendedProps;
            
            // FIX QUAN TRỌNG: Gán ID đơn hàng để Lệnh Hủy biết đang thao tác trên đơn nào
            selectedBookingId = booking.booking_id;

            document.getElementById('booking-code').textContent = `Mã booking #${booking.booking_id}`;
            document.getElementById('detail-venue').textContent = booking.venue_name;
            document.getElementById('detail-court').textContent = booking.court_name;
            document.getElementById('detail-time').textContent = `${booking.date_label}, ${booking.time_label}`;
            document.getElementById('detail-customer').textContent = booking.customer_name;
            document.getElementById('detail-email').textContent = booking.customer_email;
            
            const phoneStr = booking.customer_phone || 'Chưa cập nhật SĐT';
            document.getElementById('detail-phone').textContent = phoneStr;
            const btnCall = document.getElementById('btn-call-customer');
            const btnZalo = document.getElementById('btn-zalo-customer');
            if (phoneStr !== 'Chưa cập nhật SĐT' && phoneStr !== '') {
                btnCall.href = `tel:${phoneStr}`;
                btnZalo.href = `https://zalo.me/${phoneStr}`; 
                btnCall.style.display = 'inline-flex';
                btnZalo.style.display = 'inline-flex';
            } else {
                btnCall.style.display = 'none';
                btnZalo.style.display = 'none';
            }

            document.getElementById('detail-price').textContent = booking.total_price;
            document.getElementById('detail-status').className = `status-pill ${statusClasses[booking.status] || 'status-cancelled'}`;
            document.getElementById('detail-status').textContent = booking.status_label;

            // FIX QUAN TRỌNG: Dùng ĐÚNG tên biến HTML của bạn để chống sập JS
            actionAlert.className = 'alert d-none';
            bookingActions.classList.add('d-none');
            bookingCancel.classList.add('d-none');
            
            if (cancelReason) cancelReason.value = '';

            if (booking.status === 'pending') {
                bookingActions.classList.remove('d-none');
            } else if (booking.status === 'confirmed') {
                bookingCancel.classList.remove('d-none');
            }

            const showCancelReason = booking.status === 'cancelled';
            detailCancelLabel.classList.toggle('d-none', !showCancelReason);
            detailCancelReason.classList.toggle('d-none', !showCancelReason);
            
            if (showCancelReason) {
                detailCancelReason.textContent = booking.cancel_reason || 'Khách tự hủy';
            }

            detailModal.show();
        }

        async function updateBookingStatus(status) {
            if (!selectedBookingId) return;

            const button = status === 'confirmed' ? confirmButton : rejectButton;
            const originalText = button.textContent;
            confirmButton.disabled = true;
            rejectButton.disabled = true;
            button.textContent = 'Đang xử lý...';

            try {
                const response = await fetch(
                    statusUrlTemplate.replace('__BOOKING__', selectedBookingId),
                    {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ status }),
                    }
                );
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể cập nhật booking.');
                }

                const pendingBookingCount = document.getElementById('pending-booking-count');
                if (pendingBookingCount) {
                    pendingBookingCount.textContent = data.pending_count;
                }
                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger';
            } finally {
                confirmButton.disabled = false;
                rejectButton.disabled = false;
                button.textContent = originalText;
            }
        }

        // THỰC THI TRƯỜNG HỢP A: CHỦ SÂN CHỦ ĐỘNG HỦY CA
        // Bổ sung tham số (event) để chặn hành vi mặc định của Form
        async function cancelConfirmedBooking(event) {
            // Ngăn chặn trình duyệt tự động validate popup HTML5 (Chống lỗi validate 2 lần)
            if (event) event.preventDefault();

            // Dùng đúng biến selectedBookingId của bạn
            if (!selectedBookingId) return;

            // Dùng đúng biến cancelReason của bạn
            const reason = cancelReason.value.trim();
            if (!reason) {
                actionAlert.textContent = 'Vui lòng nhập lý do hủy sân để thông báo cho khách!';
                actionAlert.className = 'alert alert-danger mt-3';
                actionAlert.classList.remove('d-none');
                cancelReason.focus(); // Tự động trỏ chuột vào ô nhập cho tiện
                return;
            }

            const originalText = cancelButton.innerHTML;
            cancelButton.disabled = true;
            cancelButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang hủy...';
            actionAlert.classList.add('d-none'); // Ẩn cảnh báo lỗi màu đỏ đi

            try {
                // Dùng đúng biến cancelUrlTemplate của bạn
                const response = await fetch(cancelUrlTemplate.replace('__BOOKING__', selectedBookingId), {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ reason: reason })
                });
                
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể hủy booking.');
                }

                alert(data.message);
                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger mt-3';
                actionAlert.classList.remove('d-none');
            } finally {
                cancelButton.disabled = false;
                cancelButton.innerHTML = originalText;
            }
        }

        confirmButton.addEventListener('click', () => updateBookingStatus('confirmed'));
        rejectButton.addEventListener('click', () => updateBookingStatus('rejected'));
        cancelButton.addEventListener('click', cancelConfirmedBooking);

        venueFilter.addEventListener('change', () => {
            courtOptions.forEach((option) => {
                option.hidden = venueFilter.value !== '' && option.dataset.venue !== venueFilter.value;
            });

            const selectedCourt = courtFilter.selectedOptions[0];
            if (selectedCourt && selectedCourt.hidden) {
                courtFilter.value = '';
            }

            calendar.refetchEvents();
        });

        courtFilter.addEventListener('change', () => calendar.refetchEvents());
        statusFilter.addEventListener('change', () => calendar.refetchEvents());
        dateJump.addEventListener('change', () => {
            if (dateJump.value) {
                calendar.gotoDate(dateJump.value);
            }
        });
        goToday.addEventListener('click', () => calendar.today());

        calendar.render();
    });
</script>
@include('owner.partials.notification-script')
</body>
</html>
