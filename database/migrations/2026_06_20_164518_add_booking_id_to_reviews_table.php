<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra: Nếu bảng reviews CHƯA CÓ cột booking_id thì mới thêm vào
        if (!Schema::hasColumn('reviews', 'booking_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Xóa cột nếu rollback
            $table->dropColumn('booking_id');
        });
    }
};
