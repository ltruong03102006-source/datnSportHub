<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Hiển thị form đăng nhập cho Admin
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && strtolower(Auth::guard('web')->user()->role) === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('admin_login_error', 'Email hoặc mật khẩu không chính xác.');
        }

        // Kiểm tra role admin
        if (strtolower($user->role) !== 'admin') {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('admin_login_error', 'Bạn không có quyền truy cập trang quản trị này.');
        }

        // Kiểm tra trạng thái nếu có
        if (isset($user->status) && $user->status !== 'active') {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('admin_login_error', 'Tài khoản đang bị khóa.');
        }

        Auth::shouldUse('web');
        Auth::guard('web')->login($user, $request->boolean('remember'));
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Xử lý đăng xuất (Tùy chọn nếu muốn đăng xuất riêng hoặc dùng chung)
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
