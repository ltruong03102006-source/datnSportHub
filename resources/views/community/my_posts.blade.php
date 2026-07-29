@extends('layouts.app')
@section('title', 'Quản lý kèo của tôi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-zinc-900">Quản lý Kèo của tôi</h2>
        <a href="{{ route('community.index') }}" class="text-sm font-semibold text-emerald-600 hover:underline">&larr; Về bảng tin</a>
    </div>

    <div class="flex space-x-6 border-b border-stone-200 mb-6">
        <button id="tab-created" onclick="switchTab('created')" class="pb-3 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 transition">
            <i class="fa-solid fa-pen-to-square me-1"></i> Kèo tôi tạo
        </button>
        <button id="tab-joined" onclick="switchTab('joined')" class="pb-3 text-sm font-bold border-b-2 border-transparent text-zinc-500 hover:text-zinc-800 transition">
            <i class="fa-solid fa-handshake-angle me-1"></i> Kèo tôi xin tham gia
        </button>
    </div>

    <div id="content-created" class="grid grid-cols-1 gap-6">
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

                <div>
                    <h4 class="text-sm font-bold text-zinc-800 mb-3 uppercase tracking-wider">Người xin tham gia</h4>
                    
                    @if($post->participants->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($post->participants as $participant)
                                <div class="border rounded-lg p-3 flex items-center justify-between {{ $participant->status == 'approved' ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-stone-200' }}">
                                    <div>
                                        <p class="font-bold text-sm text-zinc-900">{{ $participant->user->name }}</p>
                                        @if($participant->status == 'approved')
                                            <p class="text-xs font-bold text-emerald-600 mt-1">SĐT: {{ $participant->user->phone ?? 'Không có' }}</p>
                                        @else
                                            <p class="text-xs text-zinc-500 mt-1">{{ $participant->created_at->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                    
                                  @if($participant->status == 'pending' && $post->status == 'open' && !$isPast)
                                        <div class="flex gap-2">
                                            <form action="{{ route('community.approve', $participant->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-emerald-700 transition">Duyệt</button>
                                            </form>
                                            
                                            <form action="{{ route('community.reject', $participant->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" onclick="return confirm('Bạn muốn từ chối yêu cầu tham gia của người này?')" class="bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold px-3 py-1.5 rounded hover:bg-rose-200 transition">Từ chối</button>
                                            </form>
                                        </div>
                                    @elseif($participant->status == 'pending' && $isPast)
                                        <span class="text-xs font-medium text-stone-400 italic">Hết hạn duyệt</span>
                                    @elseif($participant->status == 'approved')
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="text-xs font-bold text-emerald-600"><i class="fa-solid fa-check"></i> Đã duyệt</span>
                                            
                                            @if(!$isPast)
                                                <form action="{{ route('community.reject', $participant->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" onclick="return confirm('CẢNH BÁO: Khách này đang trong danh sách chốt. Bạn có chắc chắn muốn MỜI RA (Kick)? Hệ thống sẽ tự động mở lại kèo để bạn tuyển người mới.')" class="bg-rose-50 text-rose-600 border border-rose-200 text-xs font-bold px-3 py-1.5 rounded hover:bg-rose-100 transition">
                                                        Mời ra (Kick)
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif($participant->status == 'rejected')
                                        <span class="text-xs font-bold text-rose-500">
                                            <i class="fa-solid fa-ban"></i> Đã từ chối
                                        </span>
                                    @elseif($participant->status == 'kicked')
                                        <span class="text-xs font-bold text-orange-500">
                                            <i class="fa-solid fa-user-slash"></i> Đã mời ra (Kick)
                                        </span>
                                        
                                    @elseif($participant->status == 'withdrawn')
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="text-xs font-bold text-amber-600">
                                                <i class="fa-solid fa-person-walking-arrow-right"></i> Khách tự rút lui
                                            </span>
                                            <span class="text-[10px] text-zinc-500">Kèo đã tự mở lại</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-zinc-500 italic">Chưa có ai xin tham gia kèo này.</p>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-stone-100 flex justify-end">
                    @php
                        $hasApproved = $post->approvedParticipants()->count() > 0;
                    @endphp

                    @if($post->status === 'cancelled')
                        <span class="text-sm font-bold text-rose-500">Đã hủy</span>
                    @elseif($isPast || $post->status === 'expired')
                        <span class="text-sm font-bold text-stone-500">Kèo đã kết thúc</span>
                    @else
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
                <p class="text-zinc-500 mb-2">Bạn chưa đăng kèo nào.</p>
                <button onclick="window.location.href='{{ route('community.index') }}'" class="text-emerald-600 font-bold hover:underline">Đăng kèo ngay tại Bảng tin</button>
            </div>
        @endforelse
        <div class="mt-4">{{ $posts->links() }}</div>
    </div>

    <div id="content-joined" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($joinedPosts as $post)
            @php 
                $myParticipant = $post->participants->where('user_id', Auth::id())->first();
                $myStatus = $myParticipant ? $myParticipant->status : null;
                $isPast = \Carbon\Carbon::parse($post->play_date->format('Y-m-d') . ' ' . $post->play_time)->isPast();
            @endphp
            
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-5 relative flex flex-col h-full">
                <div class="flex items-center gap-3 mb-4 border-b pb-3">
                    <div class="w-10 h-10 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center font-bold">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-zinc-900">Chủ kèo: {{ $post->user->name }}</p>
                        <p class="text-xs text-zinc-500 font-semibold">{{ $post->sport->name }}</p>
                    </div>
                </div>

                <h3 class="text-base font-bold text-zinc-800 mb-3 line-clamp-2">{{ $post->title }}</h3>

                <div class="space-y-2 mb-4 text-sm text-zinc-600 flex-grow">
                    <p class="flex items-center gap-2"><i class="fa-regular fa-calendar w-4 text-emerald-600"></i> <span class="font-semibold text-zinc-900">{{ $post->play_date->format('d/m/Y') }}</span> lúc <span class="font-bold text-rose-600">{{ \Carbon\Carbon::parse($post->play_time)->format('H:i') }}</span></p>
                    <p class="flex items-center gap-2"><i class="fa-solid fa-location-dot w-4 text-emerald-600"></i> {{ $post->location }}</p>
                    
                    @if($myStatus === 'approved')
                        <p class="flex items-center gap-2 text-emerald-700 font-bold mt-2"><i class="fa-solid fa-phone w-4"></i> Liên hệ: {{ $post->contact_info }}</p>
                    @endif
                </div>

                <div class="mt-auto pt-4 border-t border-stone-100">
                    @if($post->status === 'cancelled')
                        <div class="w-full bg-stone-100 text-stone-500 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-stone-200">
                            <i class="fa-solid fa-ban"></i> Chủ bài đã hủy kèo này
                        </div>
                    @elseif($isPast)
                        <div class="w-full bg-stone-100 text-stone-500 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-stone-200">
                            <i class="fa-solid fa-clock-rotate-left"></i> Kèo đã kết thúc
                        </div>
                    @else
                        @if($myStatus === 'pending')
                            <div class="flex flex-col gap-2">
                                <div class="w-full bg-amber-50 text-amber-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-amber-200">
                                    <i class="fa-solid fa-hourglass-half"></i> Đang chờ chủ kèo duyệt
                                </div>
                                <button onclick="cancelJoinMatch({{ $post->id }})" class="w-full bg-rose-50 text-rose-600 font-bold py-2 rounded-lg border border-rose-200 hover:bg-rose-100 transition text-sm flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-xmark"></i> Hủy yêu cầu
                                </button>
                            </div>
                        @elseif($myStatus === 'approved')
                            <div class="flex flex-col gap-2">
                                <div class="w-full bg-emerald-50 text-emerald-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-emerald-200">
                                    <i class="fa-solid fa-check"></i> Bạn đã được duyệt
                                </div>
                                <button onclick="cancelJoinMatch({{ $post->id }})" class="w-full bg-rose-50 text-rose-600 font-bold py-2 rounded-lg border border-rose-200 hover:bg-rose-100 transition text-sm flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-xmark"></i> Rút lui (Có việc đột xuất)
                                </button>
                            </div>
                        @elseif($myStatus === 'rejected')
                            <div class="w-full bg-stone-50 text-stone-600 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-stone-200 cursor-not-allowed">
                                <i class="fa-solid fa-circle-info"></i> Yêu cầu của bạn không được chủ kèo duyệt
                            </div>
                            @elseif($myStatus === 'withdrawn')
                            <div class="w-full bg-stone-100 text-stone-500 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-stone-200 cursor-not-allowed" title="Bạn đã hủy tham gia kèo này">
                                <i class="fa-solid fa-person-walking-arrow-right"></i> Bạn đã rút lui khỏi kèo này
                            </div>
                        @elseif($myStatus === 'kicked')
                            <div class="w-full bg-rose-50 text-rose-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-rose-200 cursor-not-allowed">
                                <i class="fa-solid fa-user-slash"></i> Bạn đã bị chủ sân mời ra khỏi kèo
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-xl border border-stone-200">
                <p class="text-zinc-500">Bạn chưa xin tham gia kèo nào.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Logic chuyển đổi qua lại giữa 2 Tab
    function switchTab(tab) {
        const activeClass = 'pb-3 text-sm font-bold border-b-2 border-emerald-600 text-emerald-600 transition';
        const inactiveClass = 'pb-3 text-sm font-bold border-b-2 border-transparent text-zinc-500 hover:text-zinc-800 transition';

        if (tab === 'created') {
            document.getElementById('content-created').classList.remove('hidden');
            document.getElementById('content-joined').classList.add('hidden');
            document.getElementById('tab-created').className = activeClass;
            document.getElementById('tab-joined').className = inactiveClass;
        } else {
            document.getElementById('content-joined').classList.remove('hidden');
            document.getElementById('content-created').classList.add('hidden');
            document.getElementById('tab-joined').className = activeClass;
            document.getElementById('tab-created').className = inactiveClass;
        }
    }

    // Logic rút lui bằng AJAX
    async function cancelJoinMatch(postId) {
        if(!confirm('Bạn có chắc chắn muốn rút lui / hủy tham gia kèo này không?')) return;
        
        try {
            const res = await fetch(`/community/${postId}/cancel-join`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Accept': 'application/json' 
                }
            });
            const data = await res.json();
            
            if (res.ok) {
                Swal.fire('Thành công', data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ.', 'error');
        }
    }
</script>
@endsection