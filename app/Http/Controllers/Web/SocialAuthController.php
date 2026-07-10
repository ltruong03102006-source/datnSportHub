<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'facebook'];

    /** Chuyển hướng sang trang đăng nhập của provider. */
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    /** Provider gọi lại sau khi người dùng đồng ý. */
    public function callback(string $provider, SocialAuthService $socialAuth): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Đăng nhập bằng ' . ucfirst($provider) . ' thất bại, vui lòng thử lại.');
        }

        $user = $socialAuth->resolveUser($provider, $socialUser);

        if ($user->status === 'banned') {
            return redirect()->route('login')->with('error', 'Tài khoản đã bị khoá.');
        }

        Auth::guard('web')->login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
