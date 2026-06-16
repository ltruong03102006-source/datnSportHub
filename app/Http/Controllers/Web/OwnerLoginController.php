<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OwnerLoginController extends Controller
{
    public function create(): View
    {
        return view('owner.login');
    }

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
            ->withInput()
            ->with('owner_login_error', 'Email hoặc mật khẩu không chính xác.');
    }

    // Kiểm tra role owner
    if ($user->role !== 'owner') {
        return back()
            ->withInput()
            ->with('owner_login_error', 'Tài khoản này không phải chủ sân.');
    }

    // Kiểm tra trạng thái
    if ($user->status !== 'active') {
        return back()
            ->withInput()
            ->with('owner_login_error', 'Tài khoản đang bị khóa.');
    }

    Auth::login($user);

    $request->session()->regenerate();

    return redirect()->route('owner.dashboard');
}
}
