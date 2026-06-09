<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerRegistrationRequest;
use App\Models\OwnerRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

        $existing = OwnerRegistration::query()
            ->whereIn('status', ['pending', 'active'])
            ->where(function ($query) use ($request, $user) {
                $query->where('email', $request->email);

                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->with('owner_registration_status', $existing->status)
                ->with('owner_registration_message', $existing->status === 'pending'
                    ? 'Đơn đăng ký chủ sân của bạn đang chờ duyệt.'
                    : 'Thông tin này đã được kích hoạt chủ sân.');
        }

        OwnerRegistration::create([
            'user_id' => $user?->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('owner.register')
            ->with('owner_registration_status', 'pending')
            ->with('owner_registration_message', 'Đã gửi đơn đăng ký chủ sân. Trạng thái hiện tại: pending.');
    }
}
