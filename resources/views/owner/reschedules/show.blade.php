<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đổi lịch - Chủ Sân</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05),
                        0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-radius: 18px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
        }

        .reschedule-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: all .2s ease;
        }

        .reschedule-approve {
            background: #059669;
        }

        .reschedule-approve:hover {
            background: #047857;
        }

        .reschedule-reject {
            background: #dc2626;
        }

        .reschedule-reject:hover {
            background: #b91c1c;
        }

        .reschedule-note {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #d6d3d1;
            border-radius: 10px;
            padding: 11px 12px;
            font: inherit;
            outline: none;
            resize: vertical;
            min-height: 90px;
        }

        .reschedule-note:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px #d1fae5;
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
                <a href="{{ route('owner.web.reschedule.index') }}" class="hover:text-emerald-600 transition-colors">
                    Yêu cầu đổi lịch
                </a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Chi tiết</span>
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
            <a href="{{ route('owner.web.packages.index') }}"
               class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">
                Quản lý gói
            </a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <div class="flex-1 p-6 lg:p-10 max-w-5xl mx-auto w-full">

        @php($slots = $rescheduleRequest->slots->sortBy(fn($slot) => $slot->old_start_time ?? $slot->booking?->start_time))

        @php($oldTimes = $slots->map(fn($slot) =>
            substr($slot->old_start_time ?? $slot->booking?->start_time, 0, 5)
            . '–' .
            substr($slot->old_end_time ?? $slot->booking?->end_time, 0, 5)
        )->implode(', '))

        @php($newTimes = $rescheduleRequest->slots
            ->sortBy(fn($slot) => $slot->newTimeSlot->start_time)
            ->map(fn($slot) =>
                substr($slot->newTimeSlot->start_time, 0, 5)
                . '–' .
                substr($slot->newTimeSlot->end_time, 0, 5)
            )->implode(', '))

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">
                    Chi tiết đổi lịch #{{ $rescheduleRequest->id }}
                </h2>

                <p class="text-slate-500">
                    Xem thông tin chi tiết và xử lý yêu cầu đổi lịch của khách hàng.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('owner.web.reschedule.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Danh sách yêu cầu
                </a>
            </div>
        </div>

        <!-- Error Alert -->
        @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>

                <div>
                    <h3 class="text-sm font-medium text-red-800">
                        Đã xảy ra lỗi
                    </h3>

                    <p class="text-sm text-red-700 mt-1">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Success Alert -->
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

        <!-- Detail Card -->
        <div class="detail-card p-6 lg:p-8">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">
                        Yêu cầu đổi lịch #{{ $rescheduleRequest->id }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Khách:
                        <b class="text-slate-800">{{ $rescheduleRequest->user->name }}</b>
                        · Booking #{{ $rescheduleRequest->booking_id }}
                    </p>
                </div>

                <span class="badge bg-{{ $rescheduleRequest->status === 'pending' ? 'warning' : ($rescheduleRequest->status === 'approved' ? 'success' : 'danger') }}">
                    {{ $rescheduleRequest->status }}
                </span>
            </div>

            <div class="info-box space-y-4 text-sm">
                <div>
                    <p class="text-slate-500 font-medium mb-1">Lịch cũ</p>
                    <p class="text-slate-900 font-semibold">
                        {{ $rescheduleRequest->old_slot_date->format('d/m/Y') }} {{ $oldTimes }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 font-medium mb-1">Lịch mới</p>
                    <p class="text-slate-900 font-semibold">
                        {{ $rescheduleRequest->new_slot_date->format('d/m/Y') }} {{ $newTimes }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 font-medium mb-1">Lý do đổi lịch</p>
                    <p class="text-slate-900">
                        {{ $rescheduleRequest->reason ?: '—' }}
                    </p>
                </div>
            </div>

            @if($rescheduleRequest->status === 'pending')
                <div class="mt-6 border-t border-slate-100 pt-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">
                        Xử lý yêu cầu
                    </h3>

                    <div class="flex flex-col gap-4">
                        <form method="POST" action="{{ route('owner.web.reschedule.approve', $rescheduleRequest) }}">
                            @csrf

                            <button class="reschedule-action reschedule-approve">
                                Duyệt yêu cầu
                            </button>
                        </form>

                        <form method="POST" action="{{ route('owner.web.reschedule.reject', $rescheduleRequest) }}">
                            @csrf

                            <textarea
                                class="reschedule-note"
                                name="owner_note"
                                placeholder="Nhập lý do từ chối"
                                required></textarea>

                            <button class="reschedule-action reschedule-reject">
                                Từ chối yêu cầu
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-600">
                    Yêu cầu này đã được xử lý, không thể thao tác thêm.
                </div>
            @endif

        </div>
    </div>

    @include('owner.partials.notification-script')
</body>
</html>
