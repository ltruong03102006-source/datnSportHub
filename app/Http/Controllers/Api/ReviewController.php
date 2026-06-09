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
        $court = Court::find($courtId);

        if (! $court) {
            return response()->json(['message' => 'Không tìm thấy sân.'], 404);
        }

        $hasBooked = Booking::query()
            ->where('court_id', $courtId)
            ->where('user_id', Auth::id())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->exists();

        if (! $hasBooked) {
            return response()->json([
                'message' => 'Bạn cần đặt sân này trước khi đánh giá.',
            ], 403);
        }

        $review = Review::updateOrCreate(
            ['court_id' => $courtId, 'user_id' => Auth::id()],
            ['rating' => $request->integer('rating'), 'content' => $request->input('content')],
        );

        return response()->json([
            'message' => 'Cảm ơn bạn đã đánh giá!',
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
        ];
    }
}
