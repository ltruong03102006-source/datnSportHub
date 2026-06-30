<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
	public function up(): void
	{
		// Dự án dùng bảng notifications riêng (user_id, title, content, ...)
		// do migration 2026_06_22_000000_create_notifications_table quản lý.
		// Migration này được giữ lại để không làm sai lịch sử migration đã có.
	}

	public function down(): void
	{
		// Không xóa bảng notifications vì nó thuộc migration 000000 ở trên.
	}
};
