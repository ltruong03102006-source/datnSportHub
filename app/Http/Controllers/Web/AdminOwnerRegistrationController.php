<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectOwnerRegistrationRequest;
use App\Mail\OwnerPasswordSetupMail;
use App\Models\OwnerRegistration;
use App\Models\OwnerPasswordSetupToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminOwnerRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $query = OwnerRegistration::query()->orderBy('created_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $registrations = $query->paginate(15)->withQueryString();

        return view('admin.owner_registrations.index', compact('registrations'));
    }

    public function approve(int $id)
    {
        $registration = OwnerRegistration::with('user')->find($id);

        if (!$registration) {
            return redirect()->back()->with('error', 'Yêu cầu không tồn tại.');
        }

        if ($registration->status !== 'pending') {
            return redirect()->back()->with('error', 'Chỉ có yêu cầu đang chờ mới có thể duyệt.');
        }

        $plainToken = Str::random(64);

        $user = DB::transaction(function () use ($registration, $plainToken) {
            $registration->status = 'active';
            $registration->rejection_reason = null;

            $user = $registration->user ?: User::where('email', $registration->email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $registration->name,
                    'email' => $registration->email,
                    'password' => Str::random(40),
                    'role' => 'owner',
                    'status' => 'inactive',
                ]);
            } else {
                $user->update(['role' => 'owner', 'status' => 'inactive']);
            }

            $registration->user_id = $user->id;
            $registration->save();

            OwnerPasswordSetupToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => hash('sha256', $plainToken), 'expires_at' => now()->addDay()]
            );

            return $user;
        });

        $setupUrl = route('owner.password.setup.create', ['token' => $plainToken]);

        try {
            Mail::to($registration->email)->send(new OwnerPasswordSetupMail($registration, $setupUrl));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->back()
            ->with('success', 'Duyệt tài khoản chủ sân thành công. Liên kết thiết lập mật khẩu đã được tạo.')
            ->with('owner_password_setup_url', $setupUrl);
    }

    public function reject(RejectOwnerRegistrationRequest $request, int $id)
    {
        $registration = OwnerRegistration::find($id);

        if (!$registration) {
            return redirect()->back()->with('error', 'Yêu cầu không tồn tại.');
        }

        if ($registration->status !== 'pending') {
            return redirect()->back()->with('error', 'Chỉ có yêu cầu đang chờ mới có thể từ chối.');
        }

        $registration->status = 'rejected';
        $registration->rejection_reason = $request->input('reason');
        $registration->save();

        return redirect()->back()->with('success', 'Từ chối tài khoản chủ sân thành công.');
    }
}
