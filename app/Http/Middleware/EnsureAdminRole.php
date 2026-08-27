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
            session(['url.intended' => $request->fullUrl()]);
            return redirect('/admin/login')->with('error', 'Vui lòng đăng nhập bằng tài khoản Quản trị viên (Admin).');
        }

        if (strtolower($user->role) !== 'admin') {
            session(['url.intended' => $request->fullUrl()]);
            return redirect('/admin/login')->with('admin_login_error', 'Tài khoản hiện tại của bạn không có quyền Admin. Vui lòng đăng nhập bằng tài khoản Quản trị viên.');
        }

        if (isset($user->status) && $user->status !== 'active') {
            return redirect('/admin/login')->with('admin_login_error', 'Tài khoản Admin chưa được kích hoạt.');
        }

        return $next($request);
    }
}
