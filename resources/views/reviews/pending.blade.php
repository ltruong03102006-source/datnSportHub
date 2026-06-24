@extends('layouts.app')

@section('title', 'Đánh giá của tôi | SportHub')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Tài khoản</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900">Đánh giá của tôi</h1>
            <p class="mt-2 text-sm text-zinc-500">Các booking hoàn thành sẽ xuất hiện tại đây để bạn chia sẻ trải nghiệm.</p>
        </div>
        <a href="{{ route('account.bookings.index') }}" class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 transition hover:bg-stone-50">
            Lịch sử đặt sân
        </a>
    </div>

    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
        <strong>{{ $pendingReviews->total() }}</strong> booking đã hoàn thành đang chờ đánh giá.
    </div>

    @if($pendingReviews->isEmpty())
        <div class="rounded-lg border border-stone-200 bg-white px-6 py-16 text-center shadow-sm">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            </div>
            <h2 class="text-lg font-extrabold text-zinc-900">Bạn đã đánh giá tất cả booking</h2>
            <p class="mt-2 text-sm text-zinc-500">Các booking hoàn thành mới sẽ tự xuất hiện tại đây.</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($pendingReviews as $booking)
                <article id="review-card-{{ $booking->id }}" class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Booking #{{ $booking->id }}</p>
                            <h2 class="mt-1 text-lg font-extrabold text-zinc-900">{{ $booking->court?->name ?? 'Sân chưa cập nhật' }}</h2>
                            <p class="mt-1 text-sm text-zinc-500">{{ $booking->court?->venue?->name ?? 'Cơ sở chưa cập nhật' }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Hoàn thành</span>
                    </div>
                    <div class="space-y-2 border-y border-stone-100 py-4 text-sm text-zinc-600">
                        <p><span class="font-bold text-zinc-800">Ngày:</span> {{ $booking->slot_date?->format('d/m/Y') }}</p>
                        <p><span class="font-bold text-zinc-800">Khung giờ:</span> {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</p>
                        <p><span class="font-bold text-zinc-800">Tổng tiền:</span> {{ number_format((float) $booking->total_price, 0, ',', '.') }}đ</p>
                    </div>
                    <button type="button" data-booking-id="{{ $booking->id }}" data-court-id="{{ $booking->court_id }}" data-court-name="{{ $booking->court?->name ?? 'Sân' }}" class="open-review mt-4 w-full rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-bold text-zinc-900 transition hover:bg-amber-500">
                        Viết đánh giá
                    </button>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $pendingReviews->links() }}</div>
    @endif
</div>

<div id="review-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-stone-100 bg-stone-50 p-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Đánh giá sân</p>
                <h2 id="modal-court-name" class="mt-1 text-lg font-extrabold text-zinc-900"></h2>
            </div>
            <button type="button" id="close-review" class="rounded-lg p-2 text-stone-400 transition hover:bg-stone-100 hover:text-zinc-700">Đóng</button>
        </div>
        <div class="p-6">
            <div class="mb-5 text-center">
                <p class="mb-3 text-sm font-medium text-stone-500">Trải nghiệm của bạn thế nào?</p>
                <div id="rating-stars" class="flex justify-center gap-2">
                    @for($star = 1; $star <= 5; $star++)
                        <button type="button" data-rating="{{ $star }}" class="rating-star text-3xl text-amber-400">★</button>
                    @endfor
                </div>
            </div>
            <label for="review-content" class="mb-2 block text-xs font-bold uppercase tracking-wider text-stone-500">Nhận xét</label>
            <textarea id="review-content" rows="4" maxlength="1000" class="w-full rounded-lg border border-stone-300 bg-stone-50 p-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white" placeholder="Chia sẻ về mặt sân, ánh sáng, dịch vụ..."></textarea>
            <p id="review-error" class="mt-3 hidden rounded-lg bg-rose-50 p-3 text-sm font-semibold text-rose-600"></p>
        </div>
        <div class="flex justify-end gap-3 border-t border-stone-100 bg-stone-50 p-4">
            <button type="button" id="cancel-review" class="rounded-lg px-4 py-2.5 text-sm font-bold text-stone-600">Hủy</button>
            <button type="button" id="submit-review" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white">Gửi đánh giá</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const reviewModal = document.getElementById('review-modal');
    const reviewError = document.getElementById('review-error');
    let activeReview = { bookingId: null, courtId: null, rating: 5 };

    function closeReviewModal() {
        reviewModal.classList.add('hidden');
        reviewModal.classList.remove('flex');
    }

    function renderRating() {
        document.querySelectorAll('.rating-star').forEach((star) => {
            star.classList.toggle('text-amber-400', Number(star.dataset.rating) <= activeReview.rating);
            star.classList.toggle('text-stone-300', Number(star.dataset.rating) > activeReview.rating);
        });
    }

    document.querySelectorAll('.open-review').forEach((button) => {
        button.addEventListener('click', () => {
            activeReview = { bookingId: button.dataset.bookingId, courtId: button.dataset.courtId, rating: 5 };
            document.getElementById('modal-court-name').textContent = button.dataset.courtName;
            document.getElementById('review-content').value = '';
            reviewError.classList.add('hidden');
            renderRating();
            reviewModal.classList.remove('hidden');
            reviewModal.classList.add('flex');
        });
    });

    document.querySelectorAll('.rating-star').forEach((star) => {
        star.addEventListener('click', () => {
            activeReview.rating = Number(star.dataset.rating);
            renderRating();
        });
    });

    document.getElementById('close-review').addEventListener('click', closeReviewModal);
    document.getElementById('cancel-review').addEventListener('click', closeReviewModal);

    document.getElementById('submit-review').addEventListener('click', async () => {
        const submitButton = document.getElementById('submit-review');
        submitButton.disabled = true;
        reviewError.classList.add('hidden');

        try {
            const response = await fetch(`/api/courts/${activeReview.courtId}/reviews`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    booking_id: activeReview.bookingId,
                    rating: activeReview.rating,
                    content: document.getElementById('review-content').value,
                }),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Không thể gửi đánh giá.');
            }

            document.getElementById(`review-card-${activeReview.bookingId}`)?.remove();
            closeReviewModal();
        } catch (error) {
            reviewError.textContent = error.message;
            reviewError.classList.remove('hidden');
        } finally {
            submitButton.disabled = false;
        }
    });
</script>
@endsection
