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
        Schema::table('reviews', function (Blueprint $table) {
            // Thêm cột booking_id vào ngay sau cột id
            $table->unsignedBigInteger('booking_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Xóa cột nếu rollback
            $table->dropColumn('booking_id');
        });
    }
};
