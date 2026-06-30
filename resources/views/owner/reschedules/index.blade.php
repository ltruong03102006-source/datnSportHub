<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu đổi lịch - Chủ Sân</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .table-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05),
                        0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-radius: 18px;
            overflow-x: auto;
        }

        .reschedule-table {
            width: 100%;
            min-width: 860px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .reschedule-table th {
            background: #f8fafc;
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
            background: #f8fafc;
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
            background: #ecfdf5;
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
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                SportHub
            </h1>

            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500 ml-4 border-l border-slate-200 pl-4">
                <a href="{{ route('owner.dashboard') }}" class="hover:text-emerald-600 transition-colors">
                    Dashboard
                </a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Yêu cầu đổi lịch</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}"
               class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">
                Tổng quan
            </a>

            <a href="{{ route('owner.web.calendar.index') }}"
               class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">
                Lịch đặt sân
            </a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">
                    Yêu cầu đổi lịch
                </h2>

                <p class="text-slate-500">
                    Xem và xử lý các yêu cầu thay đổi lịch đặt sân từ khách hàng.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('owner.web.calendar.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
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
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 flex items-start">
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
