@extends('layouts.app')

@section('title', 'Thông báo')

@section('content')
<div class="mx-auto max-w-4xl py-12 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6">Thông báo</h1>

    <div class="bg-white rounded-xl border border-stone-100 shadow-sm">
        @foreach($notifications as $note)
            <div class="px-4 py-4 border-b last:border-b-0 {{ $note->is_read ? 'bg-stone-50' : 'bg-white' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900">{{ $note->title }}</div>
                        <div class="mt-1 text-sm text-zinc-600">{{ $note->content }}</div>
                        <div class="mt-2 text-xs text-zinc-400">{{ $note->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="ml-4">
                        @unless($note->is_read)
                            <form action="{{ route('notifications.read', $note) }}" method="POST">
                                @csrf
                                <button class="text-sm text-emerald-700">Đánh dấu đã đọc</button>
                            </form>
                        @endunless
                    </div>
                </div>
            </div>
        @endforeach

        <div class="p-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
