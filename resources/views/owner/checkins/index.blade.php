<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in khách đến sân - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 px-6 py-4 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('owner.dashboard') }}" class="text-2xl font-extrabold text-emerald-600">SportHub</a>
                <span class="hidden h-6 w-px bg-slate-200 md:block"></span>
                <span class="hidden text-sm font-semibold text-slate-500 md:block">Check-in khách đến sân</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('owner.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Tổng quan</a>
                <a href="{{ route('owner.web.calendar.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Lịch sân</a>
                <a href="{{ route('owner.web.venues.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cơ sở</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-8">
        <section class="mb-6 rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white shadow-sm">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-50">Quản lý vận hành trong ngày</p>
                    <h1 class="mt-2 text-3xl font-extrabold">Check-in khách đến sân</h1>
                    <p class="mt-2 max-w-2xl text-sm text-emerald-50">
                        Theo dõi các booking hôm nay, biết khách nào đang chờ, đã đến sân hoặc được đánh dấu không đến.
                    </p>
                </div>
                <div class="rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold ring-1 ring-white/20">
                    Ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}
                </div>
            </div>
        </section>

        <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Tổng booking</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-amber-600">Đang chờ</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-emerald-600">Đã check-in</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-700">{{ $stats['checked_in'] }}</p>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-rose-600">Không đến</p>
                <p class="mt-2 text-3xl font-extrabold text-rose-700">{{ $stats['no_show'] }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-extrabold text-slate-900">Booking hôm nay</h2>
                <p class="mt-1 text-sm text-slate-500">Giai đoạn này chỉ hiển thị dữ liệu, thao tác check-in sẽ được thêm ở các giai đoạn sau.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Giờ</th>
                            <th class="px-5 py-3">Khách hàng</th>
                            <th class="px-5 py-3">Sân</th>
                            <th class="px-5 py-3">Thanh toán</th>
                            <th class="px-5 py-3">Trạng thái check-in</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
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
                            <tr class="hover:bg-slate-50">
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
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
