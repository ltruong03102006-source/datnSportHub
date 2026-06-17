<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectOwnerRegistrationRequest;
use App\Models\OwnerRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $registration->status = 'active';
        $registration->rejection_reason = null;

        $user = $registration->user;

        if (!$user && !empty($registration->email)) {
            $user = User::where('email', $registration->email)->first();
        }

        if (!$user) {
            $user = User::create([
                'name' => $registration->name,
                'email' => $registration->email,
                'password' => Hash::make('12345678'),
                'role' => 'owner',
                'status' => 'active',
            ]);
        } else {
            $user->update([
                'role' => 'owner',
                'status' => 'active',
            ]);
        }

        if ($user) {
            $registration->user_id = $user->id;
        }

        $registration->save();

        return redirect()->back()->with('success', 'Duyệt tài khoản chủ sân thành công.');
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
