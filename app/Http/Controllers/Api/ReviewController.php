<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Review;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, int $courtId): JsonResponse
    {
        $bookingId = $request->input('booking_id');

        // 1. Kiểm tra đơn đặt sân có tồn tại, thuộc về user này và đã Hoàn thành chưa
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Đơn đặt sân không hợp lệ hoặc chưa hoàn thành.'], 403);
        }

        // 2. Kiểm tra xem Đơn này đã đánh giá chưa (Khóa chức năng spam)
        if (Review::where('booking_id', $bookingId)->exists()) {
            return response()->json(['message' => 'Bạn đã đánh giá đơn đặt sân này rồi.'], 422);
        }

        // 3. Tạo đánh giá mới (Bỏ updateOrCreate, dùng create vì đã gắn vào booking_id)
        $review = Review::create([
            'court_id' => $courtId,
            'user_id' => Auth::id(),
            'booking_id' => $bookingId, // Thêm dữ liệu này
            'rating' => $request->integer('rating'),
            'content' => $request->input('content')
        ]);

        return response()->json([
            'message' => 'Cảm ơn bạn đã chia sẻ trải nghiệm!',
            'data' => $this->formatReview($review->load('user:id,name', 'court:id,name')),
        ], 201);
    }

    public function venueReviews(int $venueId): JsonResponse
    {
        if (! Venue::whereKey($venueId)->exists()) {
            return response()->json(['message' => 'Không tìm thấy cơ sở.'], 404);
        }

        $courtIds = Court::where('venue_id', $venueId)->pluck('id');

        $reviews = Review::visible()
            ->whereIn('court_id', $courtIds)
            ->with('user:id,name', 'court:id,name')
            ->latest()
            ->get();

        $distribution = [];
        foreach (range(5, 1) as $star) {
            $distribution[$star] = $reviews->where('rating', $star)->count();
        }

        return response()->json([
            'data' => [
                'average' => round((float) $reviews->avg('rating'), 1),
                'count' => $reviews->count(),
                'distribution' => $distribution,
                'reviews' => $reviews->map(fn (Review $review) => $this->formatReview($review))->values(),
            ],
        ]);
    }

    private function formatReview(Review $review): array
    {
        return [
            'id' => $review->id,
            'user_name' => $review->user?->name ?? 'Người dùng',
            'court_name' => $review->court?->name,
            'rating' => $review->rating,
            'content' => $review->content,
            'created_at' => $review->created_at?->format('d/m/Y'),
            'owner_reply' => $review->owner_reply, // THÊM DÒNG NÀY ĐỂ API TRẢ VỀ DỮ LIỆU
        ];
    }
}
