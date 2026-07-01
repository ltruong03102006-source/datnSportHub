@extends('layouts.app')
@section('title', 'Quản lý kèo của tôi')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-zinc-900">Quản lý Kèo của tôi</h2>
        <a href="{{ route('community.index') }}" class="text-sm font-semibold text-emerald-600 hover:underline">&larr; Về bảng tin</a>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($posts as $post)
            <div class="bg-white rounded-xl shadow-sm border {{ $post->status == 'open' ? 'border-emerald-200' : 'border-stone-200' }} p-6">
                <div class="flex flex-col md:flex-row justify-between gap-4 mb-6 border-b pb-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2 py-1 text-xs font-bold rounded-md bg-stone-100 text-stone-600">{{ $post->sport->name }}</span>
                            
                            @php $isPast = \Carbon\Carbon::parse($post->play_date->format('Y-m-d') . ' ' . $post->play_time)->isPast(); @endphp
                            
                            @if($post->status === 'cancelled') 
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-rose-100 text-rose-700">ĐÃ HỦY</span>
                            @elseif($isPast || $post->status === 'expired') 
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-stone-200 text-stone-600">ĐÃ KẾT THÚC</span>
                            @elseif($post->status === 'open') 
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-emerald-100 text-emerald-700">ĐANG TUYỂN</span>
                            @elseif($post->status === 'full') 
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-indigo-100 text-indigo-700">ĐÃ FULL</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900">{{ $post->title }}</h3>
                        <p class="text-sm text-zinc-600 mt-1">
                            <i class="fa-regular fa-calendar me-1"></i> {{ $post->play_date->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($post->play_time)->format('H:i') }} | 
                            <i class="fa-solid fa-location-dot ms-2 me-1"></i> {{ $post->location }}
                        </p>
                    </div>
                    
                    <div class="flex flex-col items-end justify-center">
                        <div class="text-center bg-stone-50 px-4 py-2 rounded-lg border border-stone-200">
                            <p class="text-xs text-zinc-500 font-semibold uppercase">Đã duyệt</p>
                            <p class="text-xl font-black text-emerald-600">{{ $post->approvedParticipants()->count() }} <span class="text-base text-zinc-400">/ {{ $post->needed_players }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Danh sách người xin tham gia -->
                <div>
                    <h4 class="text-sm font-bold text-zinc-800 mb-3 uppercase tracking-wider">Người xin tham gia</h4>
                    
                    @if($post->participants->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($post->participants as $participant)
                                <div class="border rounded-lg p-3 flex items-center justify-between {{ $participant->status == 'approved' ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-stone-200' }}">
                                    <div>
                                        <p class="font-bold text-sm text-zinc-900">{{ $participant->user->name }}</p>
                                        @if($participant->status == 'approved')
                                            <!-- Nếu đã duyệt thì hiện SĐT -->
                                            <p class="text-xs font-bold text-emerald-600 mt-1">SĐT: {{ $participant->user->phone ?? 'Không có' }}</p>
                                        @else
                                            <p class="text-xs text-zinc-500 mt-1">{{ $participant->created_at->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                    
                                   @if($participant->status == 'pending' && $post->status == 'open' && !$isPast)
                                        <div class="flex gap-2">
                                            <!-- Nút Duyệt -->
                                            <form action="{{ route('community.approve', $participant->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-emerald-700 transition">Duyệt</button>
                                            </form>
                                            
                                            <!-- Nút Từ chối -->
                                            <form action="{{ route('community.reject', $participant->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" onclick="return confirm('Từ chối người này?')" class="bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold px-3 py-1.5 rounded hover:bg-rose-200 transition">Từ chối</button>
                                            </form>
                                        </div>
                                    @elseif($participant->status == 'pending' && $isPast)
                                        <span class="text-xs font-medium text-stone-400 italic">Hết hạn duyệt</span>
                                    @elseif($participant->status == 'approved')
                                        <span class="text-xs font-bold text-emerald-600"><i class="fa-solid fa-check"></i> Đã duyệt</span>
                                    @elseif($participant->status == 'rejected')
                                        <span class="text-xs font-bold text-rose-500">Đã từ chối</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-zinc-500 italic">Chưa có ai xin tham gia kèo này.</p>
                    @endif
                </div>

                <!-- Thao tác xóa kèo -->
                <!-- Thao tác xóa kèo -->
                <div class="mt-6 pt-4 border-t border-stone-100 flex justify-end">
                    @php
                        // Kiểm tra xem giờ đá đã qua chưa
                        $isPast = \Carbon\Carbon::parse($post->play_date->format('Y-m-d') . ' ' . $post->play_time)->isPast();
                        // Kiểm tra xem đã có người nào được duyệt chưa
                        $hasApproved = $post->approvedParticipants()->count() > 0;
                    @endphp

                    @if($post->status === 'cancelled')
                        <span class="text-sm font-bold text-rose-500">Đã hủy</span>
                    @elseif($isPast || $post->status === 'expired')
                        <span class="text-sm font-bold text-stone-500">Kèo đã kết thúc</span>
                    @else
                        <!-- Chỉ hiện nút Hủy khi kèo chưa bắt đầu và chưa bị Hủy -->
                        <form action="{{ route('community.destroy', $post->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" 
                                onclick="return confirm('{{ $hasApproved ? 'CẢNH BÁO: Đã có người tham gia! Hủy kèo sẽ làm ảnh hưởng đến họ. Bạn chắc chắn muốn HỦY?' : 'Bạn muốn hủy kèo này?' }}')" 
                                class="text-sm text-rose-600 font-semibold hover:underline">
                                Hủy kèo
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-xl border border-stone-200">
                <p class="text-zinc-500">Bạn chưa đăng kèo nào.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection