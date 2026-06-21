<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Venue;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerReviewController extends Controller
{
    public function index(Request $request)
    {
        // 1. Lấy tất cả Venue của Chủ sân này (Đã sửa user_id thành owner_id)
        $venues = Venue::where('owner_id', Auth::id())->get();
        $venueIds = $venues->pluck('id')->toArray();
        
        // 2. Logic Lọc: Nếu URL có truyền venue_id (Bấm từ Chi tiết cơ sở sang, hoặc dùng Dropdown)
        if ($request->has('venue_id') && $request->venue_id != '') {
            // Chỉ lấy danh sách sân con của đúng cơ sở được chọn
            $venueIds = [$request->venue_id];
        }

        // 3. Lấy tất cả Sân con thuộc các Venue hợp lệ ở trên
        $courtIds = Court::whereIn('venue_id', $venueIds)->pluck('id');

        // 4. Lấy Review (Kèm phân trang)
        $reviews = Review::with(['user', 'court.venue'])
            ->whereIn('court_id', $courtIds)
            ->latest()
            ->paginate(15);

        // Truyền thêm danh sách $venues ra View để làm thanh Dropdown chọn cơ sở
        return view('owner.reviews.index', compact('reviews', 'venues'));
    }

    public function reply(Request $request, Review $review)
    {
        $request->validate([
            'owner_reply' => 'required|string|max:1000'
        ]);

        // Bảo mật: Sửa user_id thành owner_id để check đúng chủ sân
        if ($review->court->venue->owner_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền phản hồi đánh giá này.');
        }

        if ($review->owner_reply) {
            return back()->with('error', 'Đánh giá này đã được phản hồi rồi.');
        }

        $review->update(['owner_reply' => $request->owner_reply]);

        return back()->with('success', 'Đã gửi phản hồi thành công!');
    }
}