@extends('layouts.app')

@section('title', 'Sân Yêu Thích | SportHub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 border-b border-stone-200 pb-5">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">Sân Yêu Thích Của Tôi</h2>
        <p class="mt-2 text-sm text-stone-500">Danh sách các điểm sân mà bạn đã lưu lại để đặt lịch nhanh chóng.</p>
    </div>

    @if($favorites->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-stone-300 bg-white py-20 text-center shadow-sm">
            <div class="grid h-20 w-20 place-items-center rounded-full bg-rose-50 text-rose-500 mb-4">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900">Chưa có sân nào được lưu</h3>
            <p class="mt-2 text-sm text-stone-500 max-w-sm mb-6">Bạn chưa thả tim cho sân thể thao nào. Hãy khám phá và lưu lại những sân ưng ý nhé!</p>
            <a href="{{ route('home') }}" class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Khám phá sân ngay</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($favorites as $venue)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition-all hover:-translate-y-1 hover:shadow-md">
                    <div class="relative h-48 w-full bg-stone-100 overflow-hidden">
                        <img src="{{ $venue->banner ? asset('storage/'.$venue->banner) : 'https://placehold.co/600x400?text=SportHub' }}" alt="{{ $venue->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        
                        <button onclick="removeFavorite(event, {{ $venue->id }})" class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-sm transition hover:scale-110">
                            <svg class="h-4 w-4 text-rose-500 fill-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        </button>
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="text-base font-bold text-zinc-900 line-clamp-1"><a href="{{ route('venues.show', $venue->id) }}" class="focus:outline-none"><span class="absolute inset-0" aria-hidden="true"></span>{{ $venue->name }}</a></h3>
                        <p class="mt-1 text-xs text-stone-500 line-clamp-1">{{ $venue->address }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Đến đặt lịch</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $favorites->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    async function removeFavorite(event, venueId) {
        event.preventDefault(); // Ngăn việc click bị chuyển sang trang chi tiết
        if (!confirm('Bạn muốn bỏ sân này khỏi danh sách yêu thích?')) return;

        try {
            const response = await fetch(`/venues/${venueId}/favorite`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            if (response.ok) { window.location.reload(); }
        } catch (error) { alert('Lỗi kết nối.'); }
    }
</script>
@endsection