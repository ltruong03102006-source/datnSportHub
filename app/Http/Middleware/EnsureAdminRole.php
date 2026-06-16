<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Middleware kiểm tra user có role admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            // For web routes, it's better to redirect to login if not authenticated
            return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập.');
        }

        if (strtolower($user->role) !== 'admin') {
            abort(403, 'Forbidden - Bạn không có quyền truy cập trang quản trị Admin.');
        }

        // Optional: Check status if admins have a status field
        if (isset($user->status) && $user->status !== 'active') {
             abort(403, 'Forbidden - Tài khoản Admin chưa được kích hoạt.');
        }

        return $next($request);
    }
}
