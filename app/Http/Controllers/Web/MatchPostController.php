<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MatchPost;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MatchParticipant;

class MatchPostController extends Controller
{
    // 1. Hiển thị Bảng tin (Kèo đang mở)
    public function index(Request $request)
    {
        $sports = Sport::orderBy('name')->get();
        $provinces = \App\Models\Province::orderedByName()->get();
        $query = MatchPost::with(['user', 'sport', 'province'])
                    ->where('status', 'open')
                    // CHẶN TRIỆT ĐỂ NGÀY VÀ GIỜ TRONG QUÁ KHỨ
                    ->where(function ($q) {
                        // Kèo của ngày mai, ngày kia... (Luôn hiện)
                        $q->where('play_date', '>', now()->toDateString())
                          // HOẶC Kèo của ngày hôm nay, nhưng GIỜ ĐÁ PHẢI CHƯA XẢY RA
                          ->orWhere(function ($sq) {
                              $sq->where('play_date', now()->toDateString())
                                 ->where('play_time', '>', now()->format('H:i'));
                          });
                    });

        // Lọc theo môn thể thao nếu có
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }
        // THÊM MỚI: LỌC THEO TỈNH THÀNH
        if ($request->filled('province_code')) {
            $query->where('province_code', $request->province_code);
        }
        // Ưu tiên kèo sắp đá lên đầu
        $posts = $query->orderBy('play_date', 'asc')
                       ->orderBy('play_time', 'asc')
                       ->paginate(12);

        return view('community.index', compact('posts', 'sports', 'provinces'));
    }

    // 2. Lưu kèo mới (AJAX)
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sport_id' => 'required|exists:sports,id',
                'title' => 'required|string|max:255',
                'play_date' => 'required|date|after_or_equal:today',
                'play_time' => 'required',
                'location' => 'required|string|max:255',
                'skill_level' => 'required|string',
                'total_players' => 'required|integer|min:2', // TỔNG SÂN
                'needed_players' => 'required|integer|min:1|lt:total_players', // CẦN TUYỂN (Phải nhỏ hơn Tổng sân)
                'contact_info' => 'required|string|max:255',
                'description' => 'nullable|string'
            ], [
                'play_date.after_or_equal' => 'Ngày thi đấu không được nằm trong quá khứ.',
                'needed_players.lt' => 'Số người cần tuyển phải nhỏ hơn Tổng số người của sân.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ép trả về JSON nếu Validate xịt (Bắt buộc cho AJAX)
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        }

        // Backup chặn giờ quá khứ ở Backend
        if ($request->play_date == now()->toDateString() && $request->play_time < now()->format('H:i')) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => ['play_time' => ['Giờ thi đấu hôm nay đã trôi qua.']]
            ], 422);
        }

        $post = MatchPost::create([
            'user_id' => Auth::id(),
            'sport_id' => $request->sport_id,
            'province_code' => $request->province_code,
            'title' => $request->title,
            'play_date' => $request->play_date,
            'play_time' => $request->play_time,
            'location' => $request->location,
            'skill_level' => $request->skill_level,
            'total_players' => $request->total_players,
            'needed_players' => $request->needed_players, 
            'contact_info' => $request->contact_info, // THÊM LẠI DÒNG NÀY (Vì bạn đang dùng SĐT)
            'description' => $request->description,
            'status' => 'open'
        ]);

        return response()->json(['message' => 'Tạo bài đăng thành công!']);
    }

    // 3. Quản lý kèo của tôi
    // 3. Quản lý kèo của tôi (Gồm Kèo tôi tạo & Kèo tôi tham gia)
    public function myPosts()
    {
        $userId = Auth::id();

        // TAB 1: Kèo do mình tạo
        $posts = MatchPost::with('sport')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        // TAB 2: Kèo mình xin tham gia (Lấy thông tin kèo và trạng thái tham gia)
        $joinedPosts = MatchPost::with(['sport', 'user']) // Lấy thêm 'user' để biết ai là chủ kèo
                    ->whereHas('participants', function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->orderBy('play_date', 'desc')
                    ->orderBy('play_time', 'desc')
                    ->get();

        return view('community.my_posts', compact('posts', 'joinedPosts'));
    }

    // 4. Đóng kèo (Chỉ chính chủ)
    public function closePost(MatchPost $matchPost)
    {
        if ($matchPost->user_id !== Auth::id()) abort(403);
        
        $matchPost->update(['status' => 'closed']);
        return back()->with('success', 'Đã đóng kèo thành công!');
    }

    // 5. Xóa kèo (Chỉ chính chủ)
    public function destroy(MatchPost $matchPost)
    {
        // 1. Kiểm tra chính chủ
        if ($matchPost->user_id !== Auth::id()) {
            abort(403);
        }

        // 2. THAY ĐỔI CỐT LÕI: Đổi trạng thái thành 'cancelled' (KHÔNG DÙNG delete() NỮA)
        $matchPost->update(['status' => 'cancelled']);
        
        // 3. Tự động từ chối tất cả những người đang xin tham gia kèo này
        $matchPost->participants()->update(['status' => 'rejected']);

        return back()->with('success', 'Đã hủy kèo thành công! Kèo đã được lưu vào lịch sử.');
    }
    // Người dùng bấm nút "Tham gia"
    // Người dùng bấm nút "Tham gia"
    public function join(Request $request, \App\Models\MatchPost $matchPost)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();

        // 1. Kiểm tra: Chủ bài tự tham gia -> Không cho
        if ($matchPost->user_id === $userId) {
            return response()->json(['message' => 'Bạn là chủ kèo này rồi!'], 400);
        }

        // 2. Kiểm tra: Bài FULL hoặc đã Hủy/Kết thúc -> Không cho join
        if ($matchPost->status !== 'open') {
            return response()->json(['message' => 'Kèo này đã chốt hoặc không còn nhận người.'], 400);
        }

        // 3. KIỂM TRA LỊCH SỬ THAM GIA CỦA NGƯỜI NÀY (Đoạn code bạn yêu cầu)
        $existingParticipant = \App\Models\MatchParticipant::where('match_post_id', $matchPost->id)
                                               ->where('user_id', $userId)
                                               ->first();

        if ($existingParticipant) {
            if ($existingParticipant->status === 'pending') {
                return response()->json(['message' => 'Bạn đã gửi yêu cầu rồi, đang chờ duyệt.'], 400);
            } elseif ($existingParticipant->status === 'approved') {
                return response()->json(['message' => 'Bạn đã được duyệt vào kèo này rồi mà!'], 400);
            } elseif ($existingParticipant->status === 'kicked') {
                return response()->json(['message' => 'Bạn đã bị chủ kèo mời ra, không thể tham gia lại.'], 400);
            // THÊM NHÁNH NÀY:
            } elseif ($existingParticipant->status === 'withdrawn') {
                return response()->json(['message' => 'Bạn đã rút lui khỏi kèo này rồi, không thể xin tham gia lại.'], 400);
            } else {
                return response()->json(['message' => 'Yêu cầu của bạn trước đó đã không được duyệt.'], 400);
            }
        }

        // 4. Vượt qua mọi bài test -> Tạo yêu cầu tham gia (Pending)
        \App\Models\MatchParticipant::create([
            'match_post_id' => $matchPost->id,
            'user_id' => $userId,
            'status' => 'pending'
        ]);

        // Thông báo trả về giao diện
        return response()->json(['message' => 'Gửi yêu cầu thành công! Chờ chủ kèo duyệt nhé.']);
    }

    // Chủ bài bấm "Duyệt" (Approve) một người
    public function approveParticipant(MatchParticipant $participant)
    {
        $post = $participant->matchPost;
        
        // Chỉ chủ bài mới có quyền duyệt
        if ($post->user_id !== Auth::id()) abort(403);

        // Case 2: Nếu đã duyệt đủ người thì không cho duyệt thêm
        $approvedCount = $post->approvedParticipants()->count();
        if ($approvedCount >= $post->needed_players) {
            return back()->with('error', 'Kèo này đã đủ số lượng người!');
        }

        // Duyệt người này
        $participant->update(['status' => 'approved']);

        // Case 2 (Tiếp): Kiểm tra xem sau khi duyệt xong đã đủ người chưa?
        // Nếu đủ -> Tự động chuyển status bài đăng thành FULL
        if ($approvedCount + 1 >= $post->needed_players) {
            $post->update(['status' => 'full']);
            // Tùy chọn: Tự động Reject toàn bộ những ông còn đang Pending
            $post->participants()->where('status', 'pending')->update(['status' => 'rejected']);
        }

        return back()->with('success', 'Đã duyệt người chơi!');
    }
    // CHỦ KÈO BẤM "TỪ CHỐI"
    // CHỦ KÈO BẤM "TỪ CHỐI" (HOẶC "KICK" NGƯỜI ĐÃ DUYỆT)
    // CHỦ KÈO BẤM "TỪ CHỐI" (HOẶC "KICK" NGƯỜI ĐÃ DUYỆT)
    public function rejectParticipant(\App\Models\MatchParticipant $participant)
    {
        $post = $participant->matchPost;
        
        // Chỉ chủ bài mới có quyền từ chối/kick
        if ($post->user_id !== \Illuminate\Support\Facades\Auth::id()) abort(403);

        // Kiểm tra xem người này trước đó đã được duyệt chưa
        $wasApproved = ($participant->status === 'approved');

        // LOGIC PHÂN BIỆT RÕ RÀNG:
        // Nếu đã từng duyệt -> Trạng thái mới là 'kicked'
        // Nếu chưa từng duyệt (đang pending) -> Trạng thái mới là 'rejected'
        $participant->update([
            'status' => $wasApproved ? 'kicked' : 'rejected'
        ]);

        // Nếu kick người đã duyệt và kèo đang FULL -> Mở lại kèo
        if ($wasApproved && $post->status === 'full') {
            $post->update(['status' => 'open']);
        }

        $message = $wasApproved ? 'Đã mời người chơi này ra khỏi kèo.' : 'Đã từ chối yêu cầu tham gia.';
        return back()->with('success', $message);
    }
    // NGƯỜI XIN THAM GIA TỰ HỦY YÊU CẦU / RÚT LUI
    // NGƯỜI XIN THAM GIA TỰ HỦY YÊU CẦU / RÚT LUI
    // NGƯỜI XIN THAM GIA TỰ HỦY YÊU CẦU / RÚT LUI
    // NGƯỜI XIN THAM GIA TỰ HỦY YÊU CẦU / RÚT LUI
    public function cancelJoin(\App\Models\MatchPost $matchPost)
    {
        $participant = \App\Models\MatchParticipant::where('match_post_id', $matchPost->id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->first();

        if ($participant) {
            $wasApproved = ($participant->status === 'approved');
            
            if ($wasApproved) {
                // A. Nếu ĐÃ DUYỆT mà hủy -> Đổi trạng thái thành RÚT LUI (để chủ sân còn thấy)
                $participant->update(['status' => 'withdrawn']);

                // B. Mở lại kèo nếu kèo đang bị khóa ở trạng thái FULL
                if ($matchPost->status === 'full') {
                    $matchPost->update(['status' => 'open']);
                }
            } else {
                // C. Nếu mới Pending mà hủy -> Xóa luôn cho sạch Database
                $participant->delete(); 
            }
            
            return response()->json(['message' => 'Bạn đã hủy tham gia kèo này thành công.']);
        }
        
        return response()->json(['message' => 'Không tìm thấy yêu cầu.'], 404);
    }
}