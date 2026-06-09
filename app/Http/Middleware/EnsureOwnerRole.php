<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerRole
{
    /**
     * Middleware kiểm tra user có role owner và đã được kích hoạt không
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized - Vui lòng đăng nhập'
            ], 401);
        }

        if ($user->role !== 'owner') {
            return response()->json([
                'message' => 'Forbidden - Bạn không phải chủ sân'
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Forbidden - Tài khoản chủ sân chưa được kích hoạt'
            ], 403);
        }

        return $next($request);
    }
}
