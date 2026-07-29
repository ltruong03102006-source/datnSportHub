<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm 'kicked' vào danh sách ENUM của cột status
        DB::statement("ALTER TABLE match_participants MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'kicked') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Khôi phục lại như cũ nếu rollback
        DB::statement("ALTER TABLE match_participants MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};