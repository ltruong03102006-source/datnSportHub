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
    Schema::table('contracts', function (Blueprint $table) {
        $table->string('signed_ip', 45)->nullable()->after('signed_at');
        $table->text('signed_user_agent')->nullable()->after('signed_ip');
        // Lưu ý: pdf_path đã có trong model nhưng nếu CSDL chưa có, hãy thêm luôn dòng dưới:
        // $table->string('pdf_path')->nullable()->after('status');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
};
