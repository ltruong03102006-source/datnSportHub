<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Lọc theo từ khóa
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $users->appends($request->all());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị chi tiết một người dùng
     */
    public function show($id): View
    {
        // Lấy thông tin người dùng với các trường cơ bản
        $user = User::select(
            'id',
            'name',
            'email',
            'phone',
            'role',
            'status',
            'created_at',
            'updated_at',
            'avatar',
            'email_verified_at'
        )->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }
}