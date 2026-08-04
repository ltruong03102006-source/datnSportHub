@extends('owner.layoutOwner.app')

@section('title', 'Quản lý gói đặt sân | SportHub')
@section('breadcrumb', 'Quản lý gói')

@section('content')

{{-- CSS/JS riêng cho trang này, layout hiện tại không có @stack('styles') nên đặt trực tiếp ở đây --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

<main class="mx-auto max-w-7xl px-6 py-8 font-[Inter] text-slate-800">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Quản lý gói đặt sân
            </h1>

            <p class="mt-2 max-w-3xl text-sm text-slate-500">
                Chủ sân cấu hình gói tuần/tháng, giảm giá, số buổi/tuần tối đa và bật/tắt gói cho từng cơ sở.
                Giá cuối cùng sẽ được tính theo sân và khung giờ khách chọn.
            </p>
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

            <div class="-mx-10 sm:mx-0">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-visible">
                <div class="flex flex-col gap-4 border-b border-slate-100 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-extrabold text-slate-900">
                                {{ $venue->name }}
                            </h2>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $allowPackageBooking ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $allowPackageBooking ? 'Đang bật đặt gói' : 'Đang tắt đặt gói' }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
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
                                    class="rounded-lg px-4 py-2 text-sm font-bold {{ ! $venueCanManagePackages ? 'cursor-not-allowed bg-slate-100 text-slate-400' : ($allowPackageBooking ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-emerald-600 text-white hover:bg-emerald-700') }}">
                                {{ $allowPackageBooking ? 'Tắt đặt gói' : 'Bật đặt gói' }}
                            </button>
                        </form>

                        @if($venueCanManagePackages)
                            <a href="{{ route('owner.web.venues.packages.create', $venue) }}"
                               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                                + Thêm gói
                            </a>
                        @else
                            <span class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">
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

                <div class="grid gap-4 border-b border-slate-100 bg-slate-50 px-5 py-4 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                            Trạng thái cơ sở
                        </p>

                        <p class="mt-1 text-lg font-extrabold {{ $allowPackageBooking ? 'text-emerald-600' : 'text-slate-500' }}">
                            {{ $allowPackageBooking ? 'Cho phép khách đặt gói' : 'Không cho đặt gói' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                            Tổng số gói
                        </p>

                        <p class="mt-1 text-lg font-extrabold text-slate-900">
                            {{ $totalPackageCount }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                            Gói đang hoạt động
                        </p>

                        <p class="mt-1 text-lg font-extrabold text-emerald-600">
                            {{ $activePackageCount }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full table-auto divide-y divide-slate-100 text-xs">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Tên gói</th>
                                <th class="px-3 py-2">Loại</th>
                                <th class="px-3 py-2">Thời lượng</th>
                                <th class="px-3 py-2">Buổi/tuần tối đa</th>
                                <th class="px-3 py-2">Giảm giá</th>
                                <th class="px-3 py-2">Số lượng</th>
                                <th class="px-3 py-2">Trạng thái</th>
                                <th class="px-3 py-2 text-right">Hành động</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
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

                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2 align-middle">
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
                                        <span class="inline-flex items-center justify-center whitespace-nowrap
             rounded-full px-3 py-1 text-[11px] font-medium
             {{ $package->status === 'active'
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-slate-100 text-slate-600' }}">
    {{ $package->status === 'active' ? 'Đang bật' : 'Đang tắt' }}
</span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('owner.web.venues.packages.edit', [$venue, $package]) }}"
                                               class="rounded-lg border border-slate-200 px-3 py-2 font-bold text-slate-700 hover:bg-slate-50">
                                                Sửa
                                            </a>

                                            <form method="POST" action="{{ route('owner.web.venues.packages.toggle', [$venue, $package]) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="rounded-lg border border-amber-200 px-3 py-2 font-bold text-amber-700 hover:bg-amber-50">
                                                    Bật/Tắt
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('owner.web.venues.packages.destroy', [$venue, $package]) }}"
                                                  onsubmit="return confirm('Bạn chắc chắn muốn xóa gói này? Nếu gói đã có khách đăng ký, nên tắt gói thay vì xóa.')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="rounded-lg border border-rose-200 px-3 py-2 font-bold text-rose-700 hover:bg-rose-50">
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
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
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

@endsection