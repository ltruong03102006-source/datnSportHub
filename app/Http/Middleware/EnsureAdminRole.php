<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Middleware kiểm tra user có role admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('web');

        $user = Auth::guard('web')->user();

        if (!$user && Auth::check()) {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect('/admin/login')->with('error', 'Vui lòng đăng nhập.');
        }

        if (strtolower($user->role) !== 'admin') {
            abort(403, 'Forbidden - Bạn không có quyền truy cập trang quản trị Admin.');
        }

        if (isset($user->status) && $user->status !== 'active') {
            abort(403, 'Forbidden - Tài khoản Admin chưa được kích hoạt.');
        }

        return $next($request);
    }
}
