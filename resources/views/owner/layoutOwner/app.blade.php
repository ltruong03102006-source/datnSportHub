<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lịch đặt sân | SportHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

    [x-cloak] {
        display: none !important;
    }
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
        <a href="{{ route('owner.contracts.index') }}">
            Hợp đồng
        </a>
    </div>
</nav>
<div class="d-flex">
    <main class="flex-grow-1 p-4">
        @yield('content')
    </main>
</div>
<script src="bootstrap.bundle.min.js"></script>

@include('owner.partials.notification-script')

@stack('scripts')
</body>
</html>
