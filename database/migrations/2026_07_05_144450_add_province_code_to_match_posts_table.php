<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_posts', function (Blueprint $table) {
            // Thêm cột province_code (cho phép null để không lỗi data cũ), đặt ngay sau sport_id
            $table->string('province_code', 50)->nullable()->after('sport_id');
        });
    }

    public function down(): void
    {
        Schema::table('match_posts', function (Blueprint $table) {
            // Xóa cột nếu bạn muốn rollback
            $table->dropColumn('province_code');
        });
    }
};