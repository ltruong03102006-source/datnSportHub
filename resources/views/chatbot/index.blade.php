@extends('layouts.app')

@section('title', 'Lịch sử chatbot | SportHub')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">SportHub Bot</p>
        <h1 class="mt-2 text-3xl font-extrabold text-zinc-900">Lịch sử chatbot</h1>
        <p class="mt-2 text-sm text-zinc-500">Xem lại các cuộc trò chuyện hỗ trợ gần đây của bạn.</p>
    </div>

    <div class="space-y-4">
        @forelse($conversations as $conversation)
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold text-zinc-900">Cuộc trò chuyện #{{ $conversation->id }}</p>
                        <p class="mt-1 text-sm text-zinc-500">
                            {{ $conversation->last_message_at?->format('d/m/Y H:i') ?? $conversation->created_at->format('d/m/Y H:i') }}
                            · {{ $conversation->messages()->count() }} tin nhắn
                        </p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        {{ $conversation->status }}
                    </span>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center">
                <p class="font-bold text-zinc-700">Bạn chưa có cuộc trò chuyện chatbot nào.</p>
                <p class="mt-1 text-sm text-zinc-500">Mở nút chatbot ở góc màn hình để bắt đầu hỏi đáp.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $conversations->links() }}
    </div>
</div>
@endsection
