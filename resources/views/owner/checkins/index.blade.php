<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in khách đến sân - SportHub</title>
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
        .owner-topbar span {
            color: #fff !important;
        }

        .owner-page {
            position: relative;
            z-index: 1;
        }

        .hero-shell,
        .filter-shell,
        .table-shell {
            background: var(--owner-panel);
            backdrop-filter: blur(18px) saturate(145%);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            border: 1px solid var(--owner-line);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.13);
        }

        .hero-shell {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 253, 250, 0.78)),
                linear-gradient(120deg, rgba(16, 185, 129, 0.18), rgba(59, 130, 246, 0.12), rgba(244, 114, 182, 0.12));
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.1);
        }

        .checkin-field {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.38);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
        }

        .checkin-table thead {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.12), rgba(59, 130, 246, 0.12), rgba(244, 114, 182, 0.1));
        }

        .checkin-table tbody tr:hover {
            background: rgba(240, 253, 250, 0.74);
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <nav class="owner-topbar sticky top-0 z-40 px-6 py-4">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('owner.dashboard') }}" class="text-2xl font-extrabold">SportHub</a>
                <span class="hidden h-6 w-px bg-white/20 md:block"></span>
                <span class="hidden text-sm font-semibold md:block">Check-in khách đến sân</span>
            </div>
            <div class="flex items-center gap-3">
                @include('owner.partials.navigation-menu')
            </div>
        </div>
    </nav>

    <main class="owner-page mx-auto max-w-7xl px-6 py-8 lg:py-10">
        <section class="hero-shell mb-6 rounded-3xl p-6 md:p-8">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">Quản lý vận hành trong ngày</p>
                    <h1 class="mt-2 text-3xl font-extrabold text-slate-950 md:text-4xl">Check-in khách đến sân</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Theo dõi các booking hôm nay, biết khách nào đang chờ, đã đến sân hoặc được đánh dấu không đến.
                    </p>
                </div>
                <div class="rounded-2xl bg-white/75 px-4 py-3 text-sm font-bold text-slate-700 ring-1 ring-white/80">
                    Ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}
                </div>
            </div>
        </section>

        <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="metric-card rounded-3xl p-5">
                <p class="text-sm font-semibold text-slate-500">Tổng booking</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="metric-card rounded-3xl p-5">
                <p class="text-sm font-semibold text-amber-600">Đang chờ</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</p>
            </div>
            <div class="metric-card rounded-3xl p-5">
                <p class="text-sm font-semibold text-emerald-600">Đã check-in</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-700">{{ $stats['checked_in'] }}</p>
            </div>
            <div class="metric-card rounded-3xl p-5">
                <p class="text-sm font-semibold text-rose-600">Không đến</p>
                <p class="mt-2 text-3xl font-extrabold text-rose-700">{{ $stats['no_show'] }}</p>
            </div>
        </section>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" action="{{ route('owner.web.checkins.index') }}" class="filter-shell mb-6 rounded-3xl p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Cơ sở</label>
                    <select name="venue_id" class="checkin-field w-full rounded-2xl px-3 py-2 text-sm outline-none focus:border-emerald-500">
                        <option value="">Tất cả cơ sở</option>
                        @foreach($venues as $venue)
                            <option value="{{ $venue->id }}" @selected(($filters['venue_id'] ?? '') == $venue->id)>{{ $venue->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Sân</label>
                    <select name="court_id" class="checkin-field w-full rounded-2xl px-3 py-2 text-sm outline-none focus:border-emerald-500">
                        <option value="">Tất cả sân</option>
                        @foreach($venues as $venue)
                            @foreach($venue->courts as $court)
                                <option value="{{ $court->id }}" @selected(($filters['court_id'] ?? '') == $court->id)>
                                    {{ $venue->name }} - {{ $court->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                    <select name="checkin_status" class="checkin-field w-full rounded-2xl px-3 py-2 text-sm outline-none focus:border-emerald-500">
                        <option value="">Tất cả trạng thái</option>
                        <option value="waiting" @selected(($filters['checkin_status'] ?? '') === 'waiting')>Đang chờ</option>
                        <option value="checked_in" @selected(($filters['checkin_status'] ?? '') === 'checked_in')>Đã check-in</option>
                        <option value="no_show" @selected(($filters['checkin_status'] ?? '') === 'no_show')>Không đến</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Tìm khách</label>
                    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên, email, SĐT" class="checkin-field w-full rounded-2xl px-3 py-2 text-sm outline-none focus:border-emerald-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">Lọc</button>
                    <a href="{{ route('owner.web.checkins.index') }}" class="rounded-full border border-slate-300 bg-white/80 px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Xóa</a>
                </div>
            </div>
        </form>

        <section class="table-shell overflow-hidden rounded-3xl">
            <div class="border-b border-white/70 bg-white/45 px-5 py-4">
                <h2 class="text-lg font-extrabold text-slate-900">Booking hôm nay</h2>
                <p class="mt-1 text-sm text-slate-500">Bấm check-in khi khách đã đến sân hoặc đánh dấu không đến nếu khách bỏ lịch.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="checkin-table min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Giờ</th>
                            <th class="px-5 py-3">Khách hàng</th>
                            <th class="px-5 py-3">Sân</th>
                            <th class="px-5 py-3">Thanh toán</th>
                            <th class="px-5 py-3">Trạng thái check-in</th>
                            <th class="px-5 py-3 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/70">
                        @forelse($bookings as $booking)
                            @php
                                $checkinStatus = 'Đang chờ';
                                $statusClass = 'bg-amber-50 text-amber-700 ring-amber-100';

                                if ($booking->checked_in_at) {
                                    $checkinStatus = 'Đã check-in lúc '.$booking->checked_in_at->format('H:i');
                                    $statusClass = 'bg-emerald-50 text-emerald-700 ring-emerald-100';
                                } elseif ($booking->no_show_at) {
                                    $checkinStatus = 'Khách không đến';
                                    $statusClass = 'bg-rose-50 text-rose-700 ring-rose-100';
                                }
                            @endphp
                            <tr class="transition">
                                <td class="whitespace-nowrap px-5 py-4 font-bold text-slate-900">
                                    {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">{{ $booking->user->name ?? 'Không rõ khách' }}</div>
                                    <div class="text-xs text-slate-500">{{ $booking->user->email ?? 'Chưa có email' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-800">{{ $booking->court->name ?? 'Không rõ sân' }}</div>
                                    <div class="text-xs text-slate-500">{{ $booking->court?->venue?->name ?? 'Không rõ cơ sở' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-bold text-slate-900">{{ number_format((float) $booking->total_price, 0, ',', '.') }} đ</div>
                                    <div class="text-xs text-slate-500">{{ strtoupper((string) ($booking->payment_method ?? 'N/A')) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                        {{ $checkinStatus }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if(!$booking->checked_in_at && !$booking->no_show_at)
                                        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:justify-end">
                                            <form method="POST" action="{{ route('owner.web.checkins.check-in', $booking) }}">
                                                @csrf
                                                <button type="submit" class="w-full rounded-full bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                                    Check-in
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('owner.web.checkins.no-show', $booking) }}" onsubmit="return confirm('Đánh dấu khách không đến cho booking này?')">
                                                @csrf
                                                <button type="submit" class="w-full rounded-full bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 ring-1 ring-rose-200 transition hover:bg-rose-100">
                                                    Không đến
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="text-right text-xs font-semibold text-slate-400">Đã xử lý</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <p class="text-base font-bold text-slate-700">Hôm nay chưa có booking cần check-in.</p>
                                        <p class="mt-1 text-sm text-slate-500">Khi có lịch đã xác nhận, danh sách sẽ hiển thị tại đây.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
