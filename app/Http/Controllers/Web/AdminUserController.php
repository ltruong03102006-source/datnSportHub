<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng toàn hệ thống
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Lấy cả user đã xóa
        if ($request->input('trashed') == '1') {
            $query->withTrashed();
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Lọc theo từ khóa (tên hoặc email) nếu có
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sắp xếp mới nhất
        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Xóa người dùng (API / Web Delete)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Không cho phép admin tự xóa chính mình
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Bạn không thể tự xóa tài khoản của chính mình!');
        }

        // Check ràng buộc trước khi soft delete
        if ($user->role === 'owner' && $user->venues()->count() > 0) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể xóa chủ sân đang có cơ sở sân!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng thành công!');
    }

    /**
     * Khôi phục người dùng đã xóa
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')->with('success', 'Đã khôi phục người dùng thành công!');
    }
}
