@extends('layouts.app')

@section('title', 'Chi tiết đổi lịch | Chủ sân')

@section('content')
@php($code = $rescheduleRequest->request_code ?: (string) $rescheduleRequest->id)

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('owner.web.reschedule.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-800">
        ← Danh sách yêu cầu
    </a>

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-black uppercase tracking-wider text-emerald-700">Yêu cầu #{{ $code }}</p>
            <h1 class="mt-2 text-3xl font-black text-zinc-900">Chi tiết đổi lịch</h1>
            <p class="mt-1 font-semibold text-slate-500">
                {{ $rescheduleRequest->booking?->court?->venue?->name }} · {{ $rescheduleRequest->booking?->court?->name }}
            </p>
        </div>

        <span class="rounded-full px-4 py-2 text-sm font-black {{ $rescheduleRequest->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($rescheduleRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800') }}">
            {{ $rescheduleRequest->status }}
        </span>
    </div>

    @if(session('error'))
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 font-bold text-red-700">{{ session('error') }}</div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-zinc-900">Thông tin</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="font-bold text-slate-500">Khách hàng</dt>
                    <dd class="mt-1 font-black text-zinc-900">{{ $rescheduleRequest->user?->name }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-500">Booking</dt>
                    <dd class="mt-1 font-black text-zinc-900">#{{ $rescheduleRequest->booking_id }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-500">Lý do</dt>
                    <dd class="mt-1 font-semibold text-slate-700">{{ $rescheduleRequest->reason ?: 'Không nhập lý do' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-black text-zinc-900">Các ca đổi lịch</h2>
            <div class="mt-4 space-y-3">
                @foreach($requests as $item)
                    <div class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-500">Từ lịch cũ</p>
                            <p class="mt-1 font-black text-zinc-900">
                                {{ $item->old_slot_date?->format('d/m/Y') }}
                            </p>
                            <p class="mt-1 font-bold text-slate-700">
                                {{ substr($item->old_start_time, 0, 5) }} - {{ substr($item->old_end_time, 0, 5) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-emerald-700">Sang lịch mới</p>
                            <p class="mt-1 font-black text-zinc-900">
                                {{ $item->new_slot_date?->format('d/m/Y') }}
                            </p>
                            <p class="mt-1 font-bold text-emerald-700">
                                {{ substr($item->new_start_time ?? $item->newTimeSlot?->start_time, 0, 5) }}
                                -
                                {{ substr($item->new_end_time ?? $item->newTimeSlot?->end_time, 0, 5) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    @if($rescheduleRequest->status === 'pending')
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-zinc-900">Xử lý yêu cầu</h2>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
                <form method="POST" action="{{ route('owner.web.reschedule.approve', $code) }}">
                    @csrf
                    <button class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                        Duyệt yêu cầu
                    </button>
                </form>

                <form method="POST" action="{{ route('owner.web.reschedule.reject', $code) }}" class="flex-1">
                    @csrf
                    <textarea name="owner_note" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="Lý do từ chối nếu có"></textarea>
                    <button class="mt-3 rounded-xl bg-red-600 px-5 py-3 text-sm font-black text-white hover:bg-red-700">
                        Từ chối yêu cầu
                    </button>
                </form>
            </div>
        </section>
    @endif
</div>
@endsection
