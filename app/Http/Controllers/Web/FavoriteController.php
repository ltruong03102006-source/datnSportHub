<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // Trang hiển thị danh sách sân yêu thích của User
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Lấy danh sách sân kèm theo thông tin môn thể thao
        $favorites = $user->favoriteVenues()->with('sport')->paginate(12);
        
        return view('account.favorites', compact('favorites'));
    }

    // API xử lý việc click thả tim (Thêm / Xóa)
    public function toggle(Venue $venue): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $isFavorited = $venue->isFavoritedBy($user);

        if ($isFavorited) {
            $user->favoriteVenues()->detach($venue->id);
            $message = 'Đã bỏ yêu thích sân này.';
            $status = 'removed';
        } else {
            $user->favoriteVenues()->attach($venue->id);
            $message = 'Đã thêm vào danh sách yêu thích.';
            $status = 'added';
        }

        return response()->json([
            'success' => true, 
            'status' => $status, 
            'message' => $message
        ]);
    }
}