<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm 'withdrawn' vào danh sách ENUM hiện tại
        DB::statement("ALTER TABLE match_participants MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'kicked', 'withdrawn') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Xóa 'withdrawn' nếu bạn muốn rollback (trở về mảng 4 trạng thái cũ)
        DB::statement("ALTER TABLE match_participants MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'kicked') DEFAULT 'pending'");
    }
};