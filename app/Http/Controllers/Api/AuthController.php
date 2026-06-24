<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthController extends Controller
{
    /**
     * API Đăng ký tài khoản (User Story #03)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Cố tình Hash thủ công dù Model đã cast để đảm bảo an toàn kép
                'role' => 'user',
                'status' => 'active',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            if ($request->hasSession()) {
                Auth::guard('web')->login($user);
                $request->session()->regenerate();
            }

            return response()->json([
                'message' => 'Register successful',
                'token' => $token,
                'user' => $user
            ], 201); // 201 Created

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi hệ thống khi đăng ký tài khoản.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Đăng nhập (User Story #04)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // 1. Tìm user theo email
            $user = User::where('email', $request->email)->first();

            // 2. Kiểm tra user có tồn tại và password có khớp không (Dùng Hash thay vì Auth::attempt)
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Email hoặc mật khẩu không chính xác.'
                ], 401);
            }

            // 3. Kiểm tra status của tài khoản
            if ($user->status !== 'active') {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa hoặc chưa kích hoạt.'
                ], 403);
            }

            // Record login history for the user's security page
            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now(),
            ]);

            // 4. Tạo token mới
            $token = $user->createToken('auth_token')->plainTextToken;

            if ($request->hasSession()) {
                Auth::guard('web')->login($user);
                $request->session()->regenerate();
            }

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi hệ thống khi đăng nhập.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Đăng xuất (User Story #04)
     */
    public function logout(): JsonResponse
    {
        try {
            // Xóa token hiện tại đang được sử dụng cho request này
            $user = request()->user();
            $accessToken = $user?->currentAccessToken();

            if ($accessToken) {
                $accessToken->delete();
            }

            if (request()->hasSession()) {
                Auth::guard('web')->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }

            return response()->json([
                'message' => 'Logout successful'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi hệ thống khi đăng xuất.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
