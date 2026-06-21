<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OwnerPasswordSetupToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerPasswordSetupController extends Controller
{
    public function create(string $token): View
    {
        $setupToken = $this->findValidToken($token);

        abort_unless($setupToken, 404, 'Liên kết đặt mật khẩu không hợp lệ hoặc đã hết hạn.');

        return view('owner.password-setup', compact('token'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $setupToken = $this->findValidToken($validated['token']);

        if (! $setupToken) {
            return back()->withErrors(['token' => 'Liên kết đặt mật khẩu không hợp lệ hoặc đã hết hạn.']);
        }

        $user = DB::transaction(function () use ($setupToken, $validated) {
            $user = $setupToken->user;
            $user->update(['password' => $validated['password'], 'role' => 'owner', 'status' => 'active']);
            $setupToken->delete();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('owner.dashboard')->with('success', 'Đặt mật khẩu thành công. Chào mừng bạn trở thành chủ sân!');
    }

    private function findValidToken(string $token): ?OwnerPasswordSetupToken
    {
        return OwnerPasswordSetupToken::with('user')
            ->where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();
    }
}
