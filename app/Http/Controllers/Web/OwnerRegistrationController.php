<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerRegistrationRequest;
use App\Models\OwnerRegistration;
use App\Models\OwnerPasswordSetupToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerRegistrationController extends Controller
{
    public function create(): View
    {
        $registration = Auth::check()
            ? OwnerRegistration::where('user_id', Auth::id())->latest()->first()
            : null;

        return view('owner-registrations.create', [
            'registration' => $registration,
        ]);
    }

    public function store(OwnerRegistrationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $plainToken = Str::random(64);

        DB::transaction(function () use ($request, $user, $plainToken) {
            $owner = $user ?: User::where('email', $request->email)->first();

            if (! $owner) {
                $owner = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Str::random(40),
                    'role' => 'owner',
                    'status' => 'inactive',
                ]);
            } else {
                $owner->update([
                    'name' => $request->name,
                    'role' => 'owner',
                    'status' => 'inactive',
                ]);
            }

            OwnerRegistration::updateOrCreate(
                ['email' => $request->email],
                [
                    'user_id' => $owner->id,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'status' => 'active',
                    'rejection_reason' => null,
                ]
            );

            OwnerPasswordSetupToken::updateOrCreate(
                ['user_id' => $owner->id],
                ['token' => hash('sha256', $plainToken), 'expires_at' => now()->addDay()]
            );
        });

        return redirect()->route('owner.password.setup.create', ['token' => $plainToken])
            ->with('success', 'Đăng ký thành công. Hãy tạo mật khẩu để kích hoạt tài khoản chủ sân.');
    }
}
