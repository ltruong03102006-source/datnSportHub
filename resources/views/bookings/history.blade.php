@extends('layouts.app')

@section('title', 'Lịch sử đặt sân | SportHub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Tài khoản</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">Lịch sử đặt sân</h1>
            <p class="mt-2 text-sm text-zinc-500">Theo dõi toàn bộ lịch đặt, trạng thái xác nhận và thao tác hủy khi còn đủ điều kiện.</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 transition hover:bg-stone-50">
            Đặt sân mới
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($bookings->isEmpty())
        <div class="rounded-lg border border-stone-200 bg-white px-6 py-16 text-center shadow-sm">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-stone-100 text-stone-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                </svg>
            </div>
            <h2 class="text-lg font-extrabold text-zinc-900">Bạn chưa có lịch đặt nào</h2>
            <p class="mt-2 text-sm text-zinc-500">Khi đặt sân thành công, các đơn sẽ xuất hiện tại đây.</p>
        </div>
    @else
        <div class="hidden overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm md:block">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Mã đơn</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Sân</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Thời gian</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Tổng tiền</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-stone-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-stone-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @foreach($bookings as $booking)
                        @php
                            $statusMeta = $statusMap[$booking->status] ?? [
                                'label' => ucfirst($booking->status),
                                'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
                            ];
                            $canCancel = in_array($booking->status, ['pending', 'confirmed'], true);
                            $slotDate = $booking->slot_date?->format('d/m/Y') ?? '';
                            $startTime = substr((string) $booking->start_time, 0, 5);
                            $endTime = substr((string) $booking->end_time, 0, 5);
                        @endphp
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-zinc-900">#{{ $booking->id }}</td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-bold text-zinc-900">{{ $booking->court?->name ?? 'Chưa cập nhật' }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-zinc-700">{{ $slotDate }}<br><span class="text-zinc-500">{{ $startTime }} - {{ $endTime }} ({{ $booking->slot_count ?? 1 }} ca)</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
    <div class="flex items-center justify-end gap-2">
        <a href="{{ route('web.bookings.success', $booking->id) }}" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-stone-50">
            Chi tiết
        </a>
        @if($canCancel)
            <form method="POST" action="{{ route('account.bookings.cancel', $booking) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch đặt sân này?');">
                @csrf
                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">
                    Hủy sân
                </button>
            </form>
        @else
            <span class="text-xs font-semibold text-stone-400 ml-2">Không thể hủy</span>
        @endif
    </div>
</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 md:hidden">
            @foreach($bookings as $booking)
                @php
                    $statusMeta = $statusMap[$booking->status] ?? [
                        'label' => ucfirst($booking->status),
                        'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-600/20',
                    ];
                    $canCancel = in_array($booking->status, ['pending', 'confirmed'], true);
                    $slotDate = $booking->slot_date?->format('d/m/Y') ?? '';
                    $startTime = substr((string) $booking->start_time, 0, 5);
                    $endTime = substr((string) $booking->end_time, 0, 5);
                @endphp
                <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Mã đơn #{{ $booking->id }}</p>
                            <h2 class="mt-1 text-base font-extrabold text-zinc-900">{{ $booking->court?->name ?? 'Chưa cập nhật' }}</h2>
                            <p class="mt-1 text-sm text-zinc-500">{{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusMeta['class'] }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 border-t border-stone-100 pt-3 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Thời gian</p>
                            <p class="mt-1 font-bold text-zinc-800">{{ $slotDate }}</p>
                            <p class="text-zinc-500">{{ $startTime }} - {{ $endTime }} ({{ $booking->slot_count ?? 1 }} ca)</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Tổng tiền</p>
                            <p class="mt-1 font-black text-emerald-700">{{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    @if($canCancel)
                        <form method="POST" action="{{ route('account.bookings.cancel', $booking) }}" class="mt-4" onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch đặt sân này?');">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-100">
                                Hủy sân
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
