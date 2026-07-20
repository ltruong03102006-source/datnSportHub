@extends('layouts.app')

@section('title', 'Yêu cầu đổi lịch | Chủ sân')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-black uppercase tracking-wider text-emerald-700">Chủ sân</p>
            <h1 class="mt-2 text-3xl font-black text-zinc-900">Yêu cầu đổi lịch</h1>
            <p class="mt-1 font-semibold text-slate-500">Duyệt hoặc từ chối yêu cầu đổi lịch của khách.</p>
        </div>

        <a href="{{ route('owner.web.calendar.index') }}" class="inline-flex justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
            Quay lại lịch sân
        </a>
    </div>

    @if(session('success'))
        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Mã yêu cầu</th>
                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Khách hàng</th>
                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Sân</th>
                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Số ca</th>
                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Trạng thái</th>
                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Ngày gửi</th>
                    <th class="px-5 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($requests as $item)
                    @php($group = $item->getRelation('groupedRequests') ?? collect([$item]))
                    @php($code = $item->request_code ?: (string) $item->id)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4 font-black text-zinc-900">{{ $code }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-700">{{ $item->user?->name }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-700">{{ $item->booking?->court?->name }}</td>
                        <td class="px-5 py-4 font-bold text-slate-700">{{ $group->count() }} ca</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $item->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($item->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800') }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-500">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('owner.web.reschedule.show', $code) }}" class="rounded-xl bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700 hover:bg-emerald-100">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center font-bold text-slate-500">Chưa có yêu cầu đổi lịch.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
