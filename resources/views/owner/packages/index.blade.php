<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý gói đặt sân | SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --owner-ink: #172033;
            --owner-muted: #64748b;
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

        .hero-shell,
        .venue-shell,
        .empty-shell {
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

        .venue-shell {
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .venue-shell:hover {
            transform: translateY(-3px);
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.17);
        }

        .metric-mini {
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .package-table thead {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.12), rgba(59, 130, 246, 0.12), rgba(244, 114, 182, 0.1));
        }

        .package-table tbody tr:hover {
            background: rgba(240, 253, 250, 0.74);
        }
    </style>
</head>

<body class="min-h-screen text-slate-800 antialiased">
    <nav class="owner-topbar sticky top-0 z-50 flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-2xl font-extrabold">
                SportHub
            </a>

            <div class="hidden border-l border-white/20 pl-4 text-sm font-semibold md:flex md:gap-2">
                <a href="{{ route('owner.dashboard') }}" class="hover:opacity-90">
                    Dashboard
                </a>
                <span>/</span>
                <span>Quản lý gói</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            @include('owner.partials.navigation-menu')
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <main class="owner-page mx-auto max-w-7xl px-6 py-8 lg:py-10">
        <div class="hero-shell mb-8 rounded-3xl p-6 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">
                    SportHub Owner
                </p>

                <h1 class="mt-2 text-3xl font-extrabold text-slate-950 md:text-4xl">
                    Quản lý gói đặt sân
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                    Chủ sân cấu hình gói tuần/tháng, giảm giá, số buổi/tuần tối đa và bật/tắt gói cho từng cơ sở.
                    Giá cuối cùng sẽ được tính theo sân và khung giờ khách chọn.
                </p>
            </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse($venues as $venue)
                @php
                    $allowPackageBooking = (bool) data_get($venue, 'allow_package_booking', false);
                    $venueCanManagePackages = in_array($venue->status, ['approved', 'active'], true);
                    $activePackageCount = $venue->packages->where('status', 'active')->count();
                    $totalPackageCount = $venue->packages->count();
                @endphp

                <section class="venue-shell rounded-3xl">
                    <div class="flex flex-col gap-4 border-b border-white/60 bg-white/55 px-5 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-xl font-extrabold text-slate-950">
                                    {{ $venue->name }}
                                </h2>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $allowPackageBooking ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $allowPackageBooking ? 'Đang bật đặt gói' : 'Đang tắt đặt gói' }}
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-slate-600">
                                {{ $venue->address }}
                            </p>

                            <p class="mt-2 text-xs font-semibold text-slate-500">
                                {{ $activePackageCount }}/{{ $totalPackageCount }} gói đang bật
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('owner.web.venues.packages.toggle-booking', $venue) }}">
                                @csrf

                                <button type="submit"
                                        @disabled(! $venueCanManagePackages)
                                        class="rounded-full px-4 py-2 text-sm font-bold shadow-sm transition {{ ! $venueCanManagePackages ? 'cursor-not-allowed bg-slate-100 text-slate-400' : ($allowPackageBooking ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-emerald-600 text-white hover:bg-emerald-700') }}">
                                    {{ $allowPackageBooking ? 'Tắt đặt gói' : 'Bật đặt gói' }}
                                </button>
                            </form>

                            @if($venueCanManagePackages)
                                <a href="{{ route('owner.web.venues.packages.create', $venue) }}"
                                   class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                     + Thêm gói
                                </a>
                            @else
                                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">
                                    Chờ Admin duyệt
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(! $venueCanManagePackages)
                        <div class="border-b border-amber-100 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700">
                            Cơ sở này chưa được Admin duyệt nên chưa thể bật đặt gói hoặc tạo gói mới.
                        </div>
                    @endif

                    <div class="grid gap-4 border-b border-white/60 bg-white/35 px-5 py-4 md:grid-cols-3">
                        <div class="metric-mini rounded-2xl p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                                Trạng thái cơ sở
                            </p>

                            <p class="mt-1 text-lg font-extrabold {{ $allowPackageBooking ? 'text-emerald-600' : 'text-slate-500' }}">
                                {{ $allowPackageBooking ? 'Cho phép khách đặt gói' : 'Không cho đặt gói' }}
                            </p>
                        </div>

                        <div class="metric-mini rounded-2xl p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                                Tổng số gói
                            </p>

                            <p class="mt-1 text-lg font-extrabold text-slate-900">
                                {{ $totalPackageCount }}
                            </p>
                        </div>

                        <div class="metric-mini rounded-2xl p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                                Gói đang hoạt động
                            </p>

                            <p class="mt-1 text-lg font-extrabold text-emerald-600">
                                {{ $activePackageCount }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="package-table min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Tên gói</th>
                                    <th class="px-5 py-3">Loại</th>
                                    <th class="px-5 py-3">Thời lượng</th>
                                    <th class="px-5 py-3">Buổi/tuần tối đa</th>
                                    <th class="px-5 py-3">Giảm giá</th>
                                    <th class="px-5 py-3">Số lượng</th>
                                    <th class="px-5 py-3">Trạng thái</th>
                                    <th class="px-5 py-3 text-right">Hành động</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white/70">
                                @forelse($venue->packages as $package)
                                    @php
                                        $activeSubscribers = $package->bookingPackages()
                                            ->whereIn('status', ['active', 'paused'])
                                            ->count();

                                        $maxSubscribers = $package->max_subscribers ?: 'Không giới hạn';

                                        $maxSessionsPerWeek = data_get($package, 'max_sessions_per_week', 7);

                                        $durationText = $package->type === 'week'
                                            ? $package->duration . ' tuần'
                                            : $package->duration . ' tháng';

                                        $discountText = rtrim(rtrim(number_format($package->discount_percent, 2), '0'), '.') . '%';
                                    @endphp

                                    <tr class="transition">
                                        <td class="px-5 py-4">
                                            <div>
                                                <p class="font-extrabold text-slate-900">
                                                    {{ $package->name }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    Gói {{ $package->type === 'week' ? 'theo tuần' : 'theo tháng' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-700">
                                            {{ $package->type === 'week' ? 'Theo tuần' : 'Theo tháng' }}
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-700">
                                            {{ $durationText }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                                {{ $maxSessionsPerWeek }} buổi/tuần
                                            </span>

                                            @if((int) $maxSessionsPerWeek === 7)
                                                <p class="mt-1 text-xs font-semibold text-emerald-600">
                                                    Hỗ trợ chơi mỗi ngày
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 font-extrabold text-emerald-600">
                                            {{ $discountText }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="font-bold text-slate-900">
                                                {{ $activeSubscribers }}
                                            </span>
                                            <span class="text-slate-400">/</span>
                                            <span class="text-slate-600">
                                                {{ $maxSubscribers }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $package->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $package->status === 'active' ? 'Đang bật' : 'Đang tắt' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('owner.web.venues.packages.edit', [$venue, $package]) }}"
                                                   class="rounded-full border border-slate-200 bg-white/80 px-3 py-2 font-bold text-slate-700 transition hover:bg-slate-50">
                                                    Sửa
                                                </a>

                                                <form method="POST" action="{{ route('owner.web.venues.packages.toggle', [$venue, $package]) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="rounded-full border border-amber-200 bg-white/80 px-3 py-2 font-bold text-amber-700 transition hover:bg-amber-50">
                                                        Bật/Tắt
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('owner.web.venues.packages.destroy', [$venue, $package]) }}"
                                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa gói này? Nếu gói đã có khách đăng ký, nên tắt gói thay vì xóa.')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="rounded-full border border-rose-200 bg-white/80 px-3 py-2 font-bold text-rose-700 transition hover:bg-rose-50">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-10 text-center">
                                            <p class="font-bold text-slate-700">
                                                Cơ sở này chưa có gói nào.
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Hãy tạo gói tuần hoặc gói tháng để khách có thể đăng ký sân cố định.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <div class="empty-shell rounded-3xl border border-dashed border-white/70 p-10 text-center">
                    <p class="font-bold text-slate-700">
                        Bạn chưa có cơ sở sân nào để cấu hình gói.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Hãy tạo cơ sở sân trước, sau đó quay lại cấu hình gói đặt sân.
                    </p>
                </div>
            @endforelse
        </div>
    </main>

    @include('owner.partials.notification-script')
</body>
</html>
