<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectOwnerRegistrationRequest;
use App\Models\OwnerRegistration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminOwnerRegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OwnerRegistration::query()->orderBy('created_at', 'desc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $registrations = $query->paginate(15);

        return response()->json($registrations, 200);
    }

    public function show(int $id): JsonResponse
    {
        $registration = OwnerRegistration::with('user')->find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Owner registration not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $registration->id,
                'name' => $registration->name,
                'email' => $registration->email,
                'phone' => $registration->phone,
                'status' => $registration->status,
                'rejection_reason' => $registration->rejection_reason,
                'created_at' => $registration->created_at?->toDateTimeString(),
                'updated_at' => $registration->updated_at?->toDateTimeString(),
                'user' => $registration->user ? [
                    'id' => $registration->user->id,
                    'name' => $registration->user->name,
                    'email' => $registration->user->email,
                    'role' => $registration->user->role,
                    'status' => $registration->user->status,
                ] : null,
            ],
        ], 200);
    }

    public function approve(int $id): JsonResponse
    {
        $registration = OwnerRegistration::with('user')->find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Owner registration not found',
            ], 404);
        }

        if ($registration->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending registrations can be approved',
            ], 400);
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

        return response()->json([
            'message' => 'Owner account approved successfully',
        ], 200);
    }

    public function reject(RejectOwnerRegistrationRequest $request, int $id): JsonResponse
    {
        $registration = OwnerRegistration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Owner registration not found',
            ], 404);
        }

        if ($registration->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending registrations can be rejected',
            ], 400);
        }

        $registration->status = 'rejected';
        $registration->rejection_reason = $request->input('reason');
        $registration->save();

        return response()->json([
            'message' => 'Owner account rejected successfully',
        ], 200);
    }
}
