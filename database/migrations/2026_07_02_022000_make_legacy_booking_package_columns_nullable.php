<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Luồng gói mới lưu nhiều buổi trong booking_package_sessions.
        // Các cột cũ trên booking_packages chỉ còn để tương thích dữ liệu cũ,
        // nên phải nullable để tạo gói mới không cần truyền court/time_slot/weekday.
        if (Schema::hasColumn('booking_packages', 'court_id')) {
            DB::statement('ALTER TABLE booking_packages MODIFY court_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('booking_packages', 'time_slot_id')) {
            DB::statement('ALTER TABLE booking_packages MODIFY time_slot_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('booking_packages', 'weekday')) {
            DB::statement('ALTER TABLE booking_packages MODIFY weekday TINYINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Không ép NOT NULL trở lại vì có thể đã có gói mới dùng nhiều buổi
        // và không còn dữ liệu court_id/time_slot_id/weekday trên bảng cha.
    }
};
