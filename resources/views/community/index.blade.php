@extends('layouts.app')

@section('title', 'Cộng đồng Tìm Đối')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-zinc-900">Tìm đối / Bắt kèo</h2>
        <div class="flex gap-3">
            <a href="{{ route('community.my_posts') }}" class="px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">Kèo của tôi</a>
            <button onclick="document.getElementById('createPostModal').classList.remove('hidden')" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 transition">+ Đăng kèo mới</button>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-stone-100 mb-6 flex items-center gap-3">
        <span class="text-sm font-semibold text-zinc-700">Lọc môn:</span>
        <form id="filterForm" action="{{ route('community.index') }}" method="GET" class="flex items-center gap-2">
            <select name="sport_id" onchange="document.getElementById('filterForm').submit()" class="text-sm rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Tất cả</option>
                @foreach($sports as $s)
                    <option value="{{ $s->id }}" {{ request('sport_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            @if(request('sport_id'))
                <a href="{{ route('community.index') }}" class="text-xs text-rose-500 hover:underline">Xóa lọc</a>
            @endif
        </form>
    </div>

    <!-- Grid Danh sách -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $post)
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5 relative overflow-hidden transition hover:shadow-md">
                <div class="absolute top-0 right-0 bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-bl-lg">
                    {{ $post->sport->name }}
                </div>
                
                <div class="flex items-center gap-3 mb-4 mt-2">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-zinc-900">{{ $post->user->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $post->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <h3 class="text-base font-bold text-zinc-800 mb-3 line-clamp-2">{{ $post->title }}</h3>

                <div class="space-y-2 mb-4 text-sm text-zinc-600">
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> 
                        <span class="font-semibold text-zinc-900">{{ $post->play_date->format('d/m/Y') }}</span> lúc <span class="font-bold text-rose-600">{{ \Carbon\Carbon::parse($post->play_time)->format('H:i') }}</span>
                    </p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> {{ $post->location }}</p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Trình độ: <strong>{{ $post->skill_level }}</strong></p>
                    
                    <!-- THÊM: Tiến độ người tham gia -->
                    <!-- THÊM: Tiến độ người tham gia -->
                    <p class="flex items-center gap-2 text-indigo-600 font-medium">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        Đã duyệt: {{ $post->approvedParticipants()->count() }} / {{ $post->needed_players }} người (Tổng sân: {{ $post->total_players }})
                    </p>
                </div>

                @if($post->description)
                    <div class="bg-stone-50 p-3 rounded-lg text-xs text-zinc-600 italic mb-4 border border-stone-100">"{{ $post->description }}"</div>
                @endif

                <!-- THAY ĐỔI: Nút Xin Tham Gia gọi AJAX thay vì lấy SĐT -->
                <!-- KIỂM TRA: Nếu là chủ bài đăng thì hiện nút Quản lý, ngược lại mới cho Tham gia -->
                <!-- KIỂM TRA: Nếu là chủ bài đăng thì hiện nút Quản lý -->
                @if($post->user_id === Auth::id())
                    <a href="{{ route('community.my_posts') }}" class="w-full bg-stone-100 text-stone-600 font-bold py-2 rounded-lg border border-stone-200 hover:bg-stone-200 transition flex items-center justify-center gap-2 text-sm text-decoration-none">
                        Quản lý kèo này
                    </a>
                @else
                    @php
                        // Kiểm tra xem User đang đăng nhập đã xin Join kèo này chưa
                        $myParticipantStatus = $post->participants->where('user_id', Auth::id())->first()->status ?? null;
                    @endphp
                    
                    @if($myParticipantStatus === 'pending')
                        <button onclick="cancelJoinMatch({{ $post->id }})" class="w-full bg-amber-100 text-amber-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-amber-200 hover:bg-rose-100 hover:text-rose-600 hover:border-rose-200 transition group" title="Bấm để hủy yêu cầu">
                            <span class="group-hover:hidden"><i class="fa-solid fa-hourglass-half"></i> Đang chờ duyệt</span>
                            <span class="hidden group-hover:block"><i class="fa-solid fa-xmark"></i> Hủy yêu cầu</span>
                        </button>
                    @elseif($myParticipantStatus === 'approved')
    <!-- Nút Rút lui rõ ràng, luôn hiện, không cần hover -->
    <div class="flex flex-col gap-2">
        <div class="w-full bg-emerald-50 text-emerald-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-emerald-200">
            <i class="fa-solid fa-check"></i> Đã được duyệt
        </div>
        <button onclick="cancelJoinMatch({{ $post->id }})" 
                class="w-full bg-rose-50 text-rose-600 font-bold py-2 rounded-lg border border-rose-200 hover:bg-rose-100 transition text-sm flex items-center justify-center gap-2">
            <i class="fa-solid fa-xmark"></i> Rút lui (Có việc đột xuất)
        </button>
    </div>
                    @elseif($myParticipantStatus === 'rejected')
                        <button disabled class="w-full bg-rose-100 text-rose-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm cursor-not-allowed border border-rose-200">
                            <i class="fa-solid fa-xmark"></i> Bị từ chối
                        </button>
                    @else
                        <!-- Nút xin tham gia ban đầu -->
                        <button id="btn-join-{{ $post->id }}" onclick="joinMatch({{ $post->id }})" class="w-full bg-emerald-600 text-white font-bold py-2 rounded-lg hover:bg-emerald-700 transition flex items-center justify-center gap-2 text-sm">
                            Xin tham gia ngay
                        </button>
                    @endif
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-zinc-500 mb-2">Chưa có kèo nào đang mở.</p>
                <button onclick="document.getElementById('createPostModal').classList.remove('hidden')" class="text-emerald-600 font-bold hover:underline">Hãy là người tạo kèo đầu tiên!</button>
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">{{ $posts->links() }}</div>
</div>

<!-- Modal Tạo Kèo (Tailwind) -->
<div id="createPostModal" class="fixed inset-0 z-50 hidden bg-zinc-900/50 backdrop-blur-sm overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-bold leading-6 text-zinc-900" id="modal-title">Tạo kèo mới</h3>
                    <button type="button" onclick="document.getElementById('createPostModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="formCreatePost">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1">Tiêu đề (Nhu cầu của bạn) <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="VD: Tìm 1 nam đánh cầu lông tối nay" required>
                        <p class="mt-1 text-xs text-rose-500 hidden" id="err-title"></p>
                    </div>
                    <!-- Thêm ô Số lượng người cần tuyển -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 mb-1">Tổng người 1 sân <span class="text-rose-500">*</span></label>
                            <input type="number" name="total_players" min="2" max="30" value="4" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="VD: 4" required>
                            <p class="mt-1 text-xs text-rose-500 hidden" id="err-total_players"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 mb-1">Cần tìm thêm <span class="text-rose-500">*</span></label>
                            <input type="number" name="needed_players" min="1" max="29" value="1" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="VD: 2" required>
                            <p class="mt-1 text-xs text-rose-500 hidden" id="err-needed_players"></p>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Đã sửa lại để chỉ có 1 ô Môn thể thao duy nhất -->
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 mb-1">Môn thể thao <span class="text-rose-500">*</span></label>
                            <select name="sport_id" id="sportSelect" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" required>
                                @foreach($sports as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                            </select>
                            <p class="mt-1 text-xs text-rose-500 hidden" id="err-sport_id"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 mb-1">Trình độ <span class="text-rose-500">*</span></label>
                            <select name="skill_level" id="skillSelect" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" required>
                                <!-- JS sẽ nạp dữ liệu vào đây -->
                            </select>
                            <p class="mt-1 text-xs text-rose-500 hidden" id="err-skill_level"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 mb-1">Ngày đá <span class="text-rose-500">*</span></label>
                            <input type="date" name="play_date" min="{{ now()->toDateString() }}" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" required>
                            <p class="mt-1 text-xs text-rose-500 hidden" id="err-play_date"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 mb-1">Giờ đá <span class="text-rose-500">*</span></label>
                            <input type="time" name="play_time" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" required>
                            <p class="mt-1 text-xs text-rose-500 hidden" id="err-play_time"></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1">Khu vực / Sân <span class="text-rose-500">*</span></label>
                        <input type="text" name="location" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="VD: Sân Chùa Láng, Đống Đa" required>
                        <p class="mt-1 text-xs text-rose-500 hidden" id="err-location"></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1">SĐT / Zalo <span class="text-rose-500">*</span></label>
                        <input type="tel" name="contact_info" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="{{ Auth::user()->phone ?? '' }}" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-bold text-emerald-700" placeholder="VD: 0987654321" required>
                        <p class="mt-1 text-xs text-rose-500 hidden" id="err-contact_info"></p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1">Ghi chú</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Chia sẻ chi phí sân..."></textarea>
                    </div>

                    <button type="submit" id="btnSubmitPost" class="w-full inline-flex justify-center rounded-lg bg-emerald-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none">Đăng kèo ngay</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // 1. TỪ ĐIỂN TRÌNH ĐỘ THEO MÔN
    const skillMap = {
        'Cầu lông': ['Mới chơi (Vung vợt trượt)', 'Trung bình yếu (TB-Y)', 'Trung bình (TB)', 'Trung bình khá (TB-K)', 'Khá/Giỏi'],
        'Bóng đá': ['Đá dưỡng sinh/Đổ mồ hôi', 'Phủi phong trào', 'Bán chuyên/Đá giải'],
        'Pickleball': ['Newbie (Chưa biết luật)', '2.0 - 2.5 (Mới tập)', '3.0 - 3.5 (Đánh ổn)', '4.0+ (Trình giải)'],
        'Tennis': ['Mới tập (NTRP 1.5-2.0)', 'Trung bình (NTRP 2.5-3.0)', 'Khá (NTRP 3.5-4.0)', 'Cao thủ (NTRP 4.5+)'],
        'Bóng bàn': ['Phủi vui vẻ', 'Hạng F, E', 'Hạng D, C', 'Hạng B, A'],
        'default': ['Mới chơi', 'Trung bình', 'Khá/Giỏi']
    };

    const sportSelect = document.getElementById('sportSelect');
    const skillSelect = document.getElementById('skillSelect');

    function updateSkillLevels() {
        const sportName = sportSelect.options[sportSelect.selectedIndex].text.trim();
        const skills = skillMap[sportName] || skillMap['default'];
        
        skillSelect.innerHTML = '';
        skills.forEach(skill => {
            skillSelect.innerHTML += `<option value="${skill}">${skill}</option>`;
        });
    }

    sportSelect.addEventListener('change', updateSkillLevels);
    
    // Nạp trình độ ngay khi load trang
    document.addEventListener('DOMContentLoaded', updateSkillLevels);

    // 2. HIỆN LIÊN HỆ
    function showContact(name, contact) {
        Swal.fire({
            title: `Liên hệ với ${name}`,
            html: `<div class="text-2xl font-black text-emerald-600 my-4">${contact}</div><p class="text-sm text-zinc-500">Gọi điện hoặc nhắn Zalo để chốt kèo nhé!</p>`,
            icon: 'info',
            confirmButtonText: 'Đóng',
            confirmButtonColor: '#059669',
            customClass: { popup: 'rounded-2xl' }
        });
    }
    // CHẶN CHỌN GIỜ QUÁ KHỨ NẾU ĐÁ HÔM NAY
    const playDateInput = document.querySelector('input[name="play_date"]');
    const playTimeInput = document.querySelector('input[name="play_time"]');

    function checkTimeValidity() {
        if (!playDateInput.value || !playTimeInput.value) return;
        
        const selectedDate = new Date(playDateInput.value);
        const today = new Date();
        
        // Nếu người dùng chọn ngày hôm nay
        if (selectedDate.toDateString() === today.toDateString()) {
            const selectedTime = playTimeInput.value;
            // Lấy giờ hiện tại format HH:mm
            const nowTime = today.getHours().toString().padStart(2, '0') + ':' + today.getMinutes().toString().padStart(2, '0');
            
            if (selectedTime < nowTime) {
                Swal.fire('Lỗi chọn giờ', 'Giờ này đã trôi qua! Vui lòng chọn giờ khác.', 'error');
                playTimeInput.value = ''; // Tự động xóa giờ sai
            }
        }
    }

    playDateInput.addEventListener('change', checkTimeValidity);
    playTimeInput.addEventListener('change', checkTimeValidity);
    // 3. XỬ LÝ SUBMIT FORM (ĐÃ XÓA TRẠNG THÁI SÂN)
    document.getElementById('formCreatePost').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPost');
        const form = this;
        
        // Ẩn các thông báo lỗi cũ nếu có
        form.querySelectorAll('[id^="err-"]').forEach(el => {
            el.classList.add('hidden');
            el.previousElementSibling.classList.remove('border-rose-500', 'ring-rose-500');
        });
        
        btn.disabled = true; 
        btn.innerHTML = 'Đang xử lý...';

        try {
            const formData = new FormData(form);
            
            // Gửi thẳng data lên Server, KHÔNG CẦN check trạng thái sân nữa
            const res = await fetch("{{ route('community.store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (res.ok) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else if (res.status === 422) {
                // Xử lý in lỗi Validation từ Laravel ra màn hình
                for (const key in data.errors) {
                    const errEl = document.getElementById(`err-${key}`);
                    if (errEl) {
                        errEl.innerText = data.errors[key][0];
                        errEl.classList.remove('hidden');
                        errEl.previousElementSibling.classList.add('border-rose-500', 'ring-rose-500');
                    }
                }
            } else {
                showToast('Lỗi xử lý.', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
        } finally {
            btn.disabled = false; 
            btn.innerHTML = 'Đăng kèo ngay';
        }
    });
    async function joinMatch(postId) {
        if(!confirm('Bạn chắc chắn muốn xin tham gia kèo này?')) return;
        
        const btn = document.getElementById(`btn-join-${postId}`);
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            btn.classList.replace('bg-emerald-600', 'bg-emerald-400');
        }

        try {
            const res = await fetch(`/community/${postId}/join`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            if (res.ok) {
                Swal.fire('Thành công', data.message, 'success');
                
                // NẾU THÀNH CÔNG: Gắn toàn bộ giao diện và chức năng Hủy vào nút
                if (btn) {
                    btn.disabled = false; // Mở khóa nút để còn bấm Hủy được
                    // Thay toàn bộ class để biến nó thành nút Hủy (Có hover đỏ)
                    btn.className = 'w-full bg-amber-100 text-amber-700 font-bold py-2 rounded-lg flex items-center justify-center gap-2 text-sm border border-amber-200 hover:bg-rose-100 hover:text-rose-600 hover:border-rose-200 transition group';
                    btn.title = 'Bấm để hủy yêu cầu';
                    // Thêm 2 lớp chữ (1 ẩn 1 hiện theo Hover)
                    btn.innerHTML = `
                        <span class="group-hover:hidden"><i class="fa-solid fa-hourglass-half"></i> Đang chờ duyệt</span>
                        <span class="hidden group-hover:block"><i class="fa-solid fa-xmark"></i> Hủy yêu cầu</span>
                    `;
                    // Gắn chức năng Hủy vào Onclick
                    btn.onclick = () => cancelJoinMatch(postId); 
                }
            } else {
                Swal.fire('Thất bại', data.message || 'Lỗi xử lý', 'warning');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Xin tham gia ngay';
                    btn.classList.replace('bg-emerald-400', 'bg-emerald-600');
                }
            }
        } catch (error) {
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ.', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Xin tham gia ngay';
                btn.classList.replace('bg-emerald-400', 'bg-emerald-600');
            }
        }
    }
    async function cancelJoinMatch(postId) {
        if(!confirm('Bạn có chắc chắn muốn rút lui / hủy tham gia kèo này không?')) return;
        
        try {
            const res = await fetch(`/community/${postId}/cancel-join`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            if (res.ok) {
                Swal.fire('Thành công', data.message, 'success');
                setTimeout(() => window.location.reload(), 1000); // Tự động load lại trang để trả về nút màu xanh
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ.', 'error');
        }
    }
</script>
@endsection