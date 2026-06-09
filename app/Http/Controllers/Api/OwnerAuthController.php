<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OwnerRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class OwnerAuthController extends Controller
{
    /**
     * Đăng ký tài khoản chủ sân
     * POST /api/owner/register
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|unique:owner_registrations',
                'password' => 'required|string|min:8',
                'phone' => 'required|string|max:20',
                'confirm_password' => 'required|same:password',
            ], [
                'email.unique' => 'Email này đã được đăng ký',
                'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
                'confirm_password.same' => 'Xác nhận mật khẩu không khớp',
            ]);

            // Kiểm tra email đã có yêu cầu chủ sân chưa
            $existingRequest = OwnerRegistration::whereIn('status', ['pending', 'approved'])
                ->where('email', $validated['email'])
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => $existingRequest->status === 'pending'
                        ? 'Email này đang có yêu cầu chủ sân chờ duyệt'
                        : 'Email này đã được phê duyệt làm chủ sân'
                ], 400);
            }

            // Tạo user mới
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user', // Lúc đầu là user, chỉ thành owner khi được duyệt
                'status' => 'active',
            ]);

            // Tạo OwnerRegistration request
            OwnerRegistration::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'pending',
            ]);

            // Tạo token tạm thời
            $token = $user->createToken('owner_auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Đăng ký chủ sân thành công. Vui lòng chờ duyệt từ admin',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi hệ thống khi đăng ký chủ sân',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đăng nhập chủ sân
     * POST /api/owner/login
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Email không hợp lệ.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.string' => 'Mật khẩu không hợp lệ.',
            ]);

            // Tìm user theo email
            $user = User::where('email', $validated['email'])->first();

            // Kiểm tra user tồn tại, password khớp
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Email hoặc mật khẩu không chính xác'
                ], 401);
            }

            // Kiểm tra user có phải chủ sân không
            if ($user->role !== 'owner') {
                return response()->json([
                    'message' => 'Tài khoản này không phải chủ sân hoặc chưa được duyệt'
                ], 403);
            }

            // Kiểm tra status
            if ($user->status !== 'active') {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa'
                ], 403);
            }

            // Xóa token cũ
            $user->tokens()->delete();

            // Tạo token mới
            $token = $user->createToken('owner_auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Đăng nhập thành công',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi hệ thống khi đăng nhập',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đăng xuất chủ sân
     * POST /api/owner/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Đăng xuất thành công'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi đăng xuất',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông tin chủ sân hiện tại
     * GET /api/owner/me
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy thông tin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đổi mật khẩu
     * POST /api/owner/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|same:new_password',
            ]);

            $user = $request->user();

            // Kiểm tra mật khẩu cũ
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Mật khẩu hiện tại không chính xác'
                ], 401);
            }

            // Cập nhật mật khẩu mới
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            return response()->json([
                'message' => 'Đổi mật khẩu thành công'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi đổi mật khẩu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
