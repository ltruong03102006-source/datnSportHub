<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu đổi lịch - Chủ Sân</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --owner-ink: #172033;
            --owner-panel: rgba(255, 255, 255, 0.84);
            --owner-line: rgba(255, 255, 255, 0.58);
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                linear-gradient(115deg, rgba(16, 185, 129, 0.16) 0%, transparent 28%),
                linear-gradient(245deg, rgba(59, 130, 246, 0.14) 0%, transparent 34%),
                linear-gradient(135deg, #f7fee7 0%, #ecfeff 36%, #fdf2f8 72%, #fff7ed 100%);
            color: var(--owner-ink);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.42) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.36) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.78), transparent 82%);
        }

        .owner-topbar {
            background: linear-gradient(120deg, rgba(15, 23, 42, 0.92), rgba(5, 150, 105, 0.88), rgba(37, 99, 235, 0.86));
            border-bottom: 1px solid rgba(255, 255, 255, 0.24);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.22);
        }

        .owner-topbar a,
        .owner-topbar h1,
        .owner-topbar span {
            color: #fff !important;
        }

        .owner-pill {
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: 0.2s ease;
        }

        .owner-pill:hover {
            background: rgba(255, 255, 255, 0.24);
            transform: translateY(-1px);
        }

        .owner-page {
            position: relative;
            z-index: 1;
        }

        .hero-shell {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 253, 250, 0.78)),
                linear-gradient(120deg, rgba(16, 185, 129, 0.18), rgba(59, 130, 246, 0.12), rgba(244, 114, 182, 0.12));
            backdrop-filter: blur(18px) saturate(145%);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            border: 1px solid var(--owner-line);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.13);
        }

        .table-card {
            background: var(--owner-panel);
            backdrop-filter: blur(18px) saturate(145%);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            border: 1px solid var(--owner-line);
            box-shadow: 0 24px 65px rgba(15, 23, 42, 0.14);
            border-radius: 24px;
            overflow-x: auto;
        }

        .reschedule-table {
            width: 100%;
            min-width: 860px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .reschedule-table th {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.12), rgba(59, 130, 246, 0.12), rgba(244, 114, 182, 0.1));
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 700;
        }

        .reschedule-table th,
        .reschedule-table td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .reschedule-table tr:last-child td {
            border-bottom: 0;
        }

        .reschedule-table tbody tr {
            transition: all .2s ease;
        }

        .reschedule-table tbody tr:hover {
            background: rgba(240, 253, 250, 0.74);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
        }

        .bg-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .bg-success {
            background: #dcfce7;
            color: #166534;
        }

        .bg-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border-radius: 9px;
            background: rgba(236, 253, 245, 0.9);
            color: #047857;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #a7f3d0;
            transition: all .2s ease;
        }

        .detail-btn:hover {
            background: #10b981;
            color: #ffffff;
        }
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="owner-topbar px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold">
                SportHub
            </h1>

            <div class="hidden md:flex items-center gap-2 text-sm font-semibold ml-4 border-l border-white/20 pl-4">
                <a href="{{ route('owner.dashboard') }}" class="hover:opacity-90 transition-colors">
                    Dashboard
                </a>
                <span>/</span>
                <span>Yêu cầu đổi lịch</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}"
               class="owner-pill text-sm font-medium">
                Tổng quan
            </a>

            <a href="{{ route('owner.web.calendar.index') }}"
               class="owner-pill text-sm font-medium">
                Lịch đặt sân
            </a>
            <a href="{{ route('owner.web.packages.index') }}"
               class="owner-pill text-sm font-medium">
                Quản lý gói
            </a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <div class="owner-page flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

        <!-- Header -->
        <div class="hero-shell flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 rounded-3xl p-6 md:p-8">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">
                    SportHub Owner
                </p>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-950 md:text-4xl">
                    Yêu cầu đổi lịch
                </h2>

                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                    Xem và xử lý các yêu cầu thay đổi lịch đặt sân từ khách hàng.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('owner.web.calendar.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-full shadow-sm transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Quay lại quản lý booking
                </a>
            </div>
        </div>

        <!-- Alert -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50/90 border border-emerald-200 flex items-start shadow-sm">
                <svg class="w-5 h-5 text-emerald-500 mt-0.5 mr-3 flex-shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>

                <div>
                    <h3 class="text-sm font-medium text-emerald-800">
                        Thành công
                    </h3>

                    <p class="text-sm text-emerald-700 mt-1">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Table -->
        <div class="table-card">
            <table class="reschedule-table">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Khách</th>
                        <th>Lịch cũ</th>
                        <th>Lịch mới</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $item)
                        @php($slots = $item->slots->sortBy(fn($slot) => $slot->old_start_time ?? $slot->booking?->start_time))

                        @php($oldTimes = $slots->map(fn($slot) =>
                            substr($slot->old_start_time ?? $slot->booking?->start_time, 0, 5)
                            . '–' .
                            substr($slot->old_end_time ?? $slot->booking?->end_time, 0, 5)
                        )->implode(', '))

                        @php($newTimes = $item->slots
                            ->sortBy(fn($slot) => $slot->newTimeSlot->start_time)
                            ->map(fn($slot) =>
                                substr($slot->newTimeSlot->start_time, 0, 5)
                                . '–' .
                                substr($slot->newTimeSlot->end_time, 0, 5)
                            )->implode(', '))

                        <tr>
                            <td class="font-semibold text-slate-800">
                                #{{ $item->booking_id }}
                            </td>

                            <td class="text-slate-700">
                                {{ $item->user->name }}
                            </td>

                            <td class="text-slate-600">
                                {{ $item->old_slot_date->format('d/m/Y') }} {{ $oldTimes }}
                            </td>

                            <td class="text-slate-600">
                                {{ $item->new_slot_date->format('d/m/Y') }} {{ $newTimes }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $item->status === 'pending' ? 'warning' : ($item->status === 'approved' ? 'success' : 'danger') }}">
                                    {{ $item->status }}
                                </span>
                            </td>

                            <td>
                                <a class="detail-btn" href="{{ route('owner.web.reschedule.show', $item) }}">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-500 py-10">
                                Chưa có yêu cầu đổi lịch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    </div>

    @include('owner.partials.notification-script')
</body>
</html>
