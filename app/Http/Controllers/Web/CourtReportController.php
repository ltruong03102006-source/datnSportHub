<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourtReportController extends Controller
{
    public function store(Request $request, Court $court): JsonResponse
    {
        // 1. Validate dữ liệu
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ], [
            'reason.required' => 'Vui lòng nhập lý do báo cáo.',
            'reason.min' => 'Lý do hơi ngắn, vui lòng miêu tả chi tiết hơn (ít nhất 10 ký tự).'
        ]);

        // 2. Chống Spam: Kiểm tra xem user đã có báo cáo pending cho sân này chưa
        $existingReport = CourtReport::where('court_id', $court->id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã báo cáo sân này rồi và đang chờ quản trị viên xử lý. Vui lòng không gửi trùng lặp!'
            ], 429);
        }

        // 3. Tạo báo cáo mới
        CourtReport::create([
            'court_id' => $court->id,
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'status' => 'pending'
        ]);

        // 4. Trả về kết quả JSON (Đoạn này bị thiếu khiến VS Code báo đỏ)
        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn. Quản trị viên sẽ ghi nhận và kiểm tra cơ sở này.'
        ]);
    }
}