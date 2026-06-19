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
                            
                            // TRUY VẤN LẤY CHI TIẾT CÁC CA ĐỂ GỘP
                            $actualSlots = \App\Models\Booking::where('user_id', \Illuminate\Support\Facades\Auth::id())
                                ->where('court_id', $booking->court_id)
                                ->where('slot_date', $booking->slot_date)
                                ->where('created_at', $booking->created_at)
                                ->orderBy('start_time')
                                ->get();
                            
                            $mergedTimeStrings = [];
                            if ($actualSlots->count() > 0) {
                                $currentStart = substr((string) $actualSlots[0]->start_time, 0, 5);
                                $currentEnd = substr((string) $actualSlots[0]->end_time, 0, 5);
                                $currentCourt = $booking->court?->name ?? 'Sân';
                                
                                for ($i = 1; $i < $actualSlots->count(); $i++) {
                                    $nextStart = substr((string) $actualSlots[$i]->start_time, 0, 5);
                                    $nextEnd = substr((string) $actualSlots[$i]->end_time, 0, 5);
                                    
                                    if ($currentEnd === $nextStart) {
                                        $currentEnd = $nextEnd; 
                                    } else {
                                        $mergedTimeStrings[] = "- Sân $currentCourt: $currentStart - $currentEnd";
                                        $currentStart = $nextStart;
                                        $currentEnd = $nextEnd;
                                    }
                                }
                                $mergedTimeStrings[] = "- Sân $currentCourt: $currentStart - $currentEnd";
                            }
                        @endphp
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-zinc-900">#{{ $booking->id }}</td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-bold text-zinc-900">{{ $booking->court?->name ?? 'Chưa cập nhật' }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $booking->court?->venue?->name ?? 'Chưa cập nhật cơ sở' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-zinc-700">
                                <p class="font-bold text-zinc-900 mb-1">{{ $slotDate }}</p>
                                <div class="text-zinc-600 font-normal space-y-1">
                                    @foreach($mergedTimeStrings as $timeStr)
                                        <p>{{ $timeStr }}</p>
                                    @endforeach
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                                @if($booking->status === 'cancelled' && $booking->cancel_reason)
                                    <p class="mt-1.5 max-w-[200px] text-xs text-red-600"><span class="font-semibold">Lý do hủy:</span> {{ $booking->cancel_reason }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
    <div class="flex items-center justify-end gap-2">
        <a href="{{ route('web.bookings.success', $booking->id) }}" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-stone-50">Chi tiết</a>
        
        @if($booking->status === 'completed')
            @if(in_array($booking->id, $reviewedBookingIds))
                <button disabled class="cursor-not-allowed rounded-lg border border-stone-200 bg-stone-100 px-3 py-2 text-xs font-bold text-stone-400">Đã đánh giá</button>
            @else
                <button onclick="openReviewModal({{ $booking->id }}, {{ $booking->court_id }}, '{{ addslashes($booking->court?->name) }}')" class="rounded-lg bg-amber-400 px-3 py-2 text-xs font-bold text-zinc-900 transition hover:bg-amber-500 shadow-sm">Đánh giá</button>
            @endif
        @endif

        @if($canCancel)
            <form method="POST" action="{{ route('account.bookings.cancel', $booking) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch đặt sân này?');">
                @csrf
                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">Hủy sân</button>
            </form>
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
                    
                    $actualSlots = \App\Models\Booking::where('user_id', \Illuminate\Support\Facades\Auth::id())
                        ->where('court_id', $booking->court_id)
                        ->where('slot_date', $booking->slot_date)
                        ->where('created_at', $booking->created_at)
                        ->orderBy('start_time')
                        ->get();
                    
                    $mergedTimeStrings = [];
                    if ($actualSlots->count() > 0) {
                        $currentStart = substr((string) $actualSlots[0]->start_time, 0, 5);
                        $currentEnd = substr((string) $actualSlots[0]->end_time, 0, 5);
                        $currentCourt = $booking->court?->name ?? 'Sân';
                        for ($i = 1; $i < $actualSlots->count(); $i++) {
                            $nextStart = substr((string) $actualSlots[$i]->start_time, 0, 5);
                            $nextEnd = substr((string) $actualSlots[$i]->end_time, 0, 5);
                            if ($currentEnd === $nextStart) {
                                $currentEnd = $nextEnd;
                            } else {
                                $mergedTimeStrings[] = "- Sân $currentCourt: $currentStart - $currentEnd";
                                $currentStart = $nextStart;
                                $currentEnd = $nextEnd;
                            }
                        }
                        $mergedTimeStrings[] = "- Sân $currentCourt: $currentStart - $currentEnd";
                    }
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
                            <p class="mt-1 font-bold text-zinc-800 mb-1">{{ $slotDate }}</p>
                            <div class="text-zinc-600 font-normal space-y-1">
                                @foreach($mergedTimeStrings as $timeStr)
                                    <p>{{ $timeStr }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Tổng tiền</p>
                            <p class="mt-1 font-black text-emerald-700">{{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    @if($booking->status === 'cancelled' && $booking->cancel_reason)
                        <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
                            <span class="font-semibold">Lý do hủy:</span> {{ $booking->cancel_reason }}
                        </div>
                    @endif

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
<div id="reviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl overflow-hidden transform transition-all">
        <div class="border-b border-stone-100 bg-stone-50/50 p-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-zinc-900">Đánh giá trải nghiệm</h3>
                <p class="text-xs text-stone-500 mt-1" id="modalCourtName">Tên sân</p>
            </div>
            <button onclick="closeReviewModal()" class="text-stone-400 hover:text-stone-600 transition bg-white rounded-full p-1 shadow-sm">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div class="p-6">
            <input type="hidden" id="revBookingId">
            <input type="hidden" id="revCourtId">
            <input type="hidden" id="revRating" value="5">
            
            <div class="mb-6 flex flex-col items-center">
                <p class="text-sm font-bold text-zinc-800 mb-3">Chất lượng sân như thế nào?</p>
                <div class="flex items-center gap-2" id="starContainer">
                    @for($i=1; $i<=5; $i++)
                        <button type="button" onmouseover="hoverStar({{ $i }})" onmouseout="resetStar()" onclick="setRating({{ $i }})" class="star-btn transition-transform hover:scale-110">
                            <svg class="w-9 h-9 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                    @endfor
                </div>
                <p id="starLabel" class="text-xs font-bold text-amber-600 mt-2 uppercase tracking-wide">Tuyệt vời</p>
            </div>
            
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Nhận xét thêm (Tùy chọn)</label>
                <textarea id="revContent" rows="3" class="w-full rounded-xl border border-stone-300 bg-stone-50 p-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" placeholder="Mặt sân có tốt không? Thái độ phục vụ thế nào?"></textarea>
            </div>
            <div id="revError" class="mt-3 hidden rounded-lg bg-rose-50 p-2 text-center text-xs font-semibold text-rose-600"></div>
        </div>
        <div class="border-t border-stone-100 bg-stone-50 p-4 flex justify-end gap-3">
            <button onclick="closeReviewModal()" class="rounded-xl px-5 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-200 transition">Bỏ qua</button>
            <button id="btnSubmitReview" onclick="submitReview()" class="rounded-xl bg-zinc-900 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-emerald-600 active:scale-95">Gửi đánh giá</button>
        </div>
    </div>
</div>

<script>
    const labels = ["Tệ", "Không hài lòng", "Bình thường", "Tốt", "Tuyệt vời"];
    let currentRating = 5;

    function openReviewModal(bookingId, courtId, courtName) {
        document.getElementById('revBookingId').value = bookingId;
        document.getElementById('revCourtId').value = courtId;
        document.getElementById('modalCourtName').innerText = courtName;
        document.getElementById('revContent').value = '';
        document.getElementById('revError').classList.add('hidden');
        setRating(5);
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function hoverStar(rating) { renderStars(rating, true); }
    function resetStar() { renderStars(currentRating, false); }
    
    function setRating(rating) {
        currentRating = rating;
        document.getElementById('revRating').value = rating;
        renderStars(rating, false);
    }

    function renderStars(rating, isHover) {
        const stars = document.querySelectorAll('.star-btn svg');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-stone-300');
                star.classList.add('text-amber-400');
            } else {
                star.classList.remove('text-amber-400');
                star.classList.add('text-stone-300');
            }
        });
        document.getElementById('starLabel').innerText = labels[rating - 1];
    }

    async function submitReview() {
        const btn = document.getElementById('btnSubmitReview');
        const bookingId = document.getElementById('revBookingId').value;
        const courtId = document.getElementById('revCourtId').value;
        const rating = document.getElementById('revRating').value;
        const content = document.getElementById('revContent').value;
        const errorDiv = document.getElementById('revError');
        const token = localStorage.getItem('sporthub_token');

        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
        errorDiv.classList.add('hidden');

        try {
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
            if (token) headers.Authorization = `Bearer ${token}`;

            const response = await fetch(`/api/courts/${courtId}/reviews`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ booking_id: bookingId, rating, content })
            });

            const data = await response.json();

            if (!response.ok) {
                errorDiv.textContent = data.message || 'Có lỗi xảy ra.';
                errorDiv.classList.remove('hidden');
            } else {
                alert('Đánh giá thành công! Cảm ơn bạn.');
                window.location.reload(); // Tải lại trang để cập nhật nút thành "Đã đánh giá"
            }
        } catch (error) {
            errorDiv.textContent = 'Lỗi kết nối máy chủ.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Gửi đánh giá';
        }
    }
</script>
@endsection
