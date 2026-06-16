<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectOwnerRegistrationRequest;
use App\Models\OwnerRegistration;
use Illuminate\Http\Request;
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
        $registration->save();

        if ($registration->user) {
            $registration->user->update([
                'role' => 'owner',
                'status' => 'active',
            ]);
        }

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
