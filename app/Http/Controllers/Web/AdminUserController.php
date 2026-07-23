<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng toàn hệ thống
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Lọc theo từ khóa (tên hoặc email) nếu có
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Sắp xếp mới nhất
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $users->appends($request->all());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Form thêm người dùng mới
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Lưu người dùng mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,owner,admin',
            'status' => 'required|in:active,inactive,banned',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now(); // Mặc định admin tạo thì xác thực luôn

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng mới thành công!');
    }

    /**
     * Hiển thị chi tiết một người dùng
     */
    public function show($id): View
    {
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

    /**
     * Form sửa thông tin người dùng
     */
    public function edit($id): View
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,owner,admin',
            'status' => 'required|in:active,inactive,banned',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật thông tin người dùng thành công!');
    }
}
