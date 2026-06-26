<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    public function latest(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([]);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id','title','content','link','is_read','created_at']);

        return response()->json($notifications);
    }
}
