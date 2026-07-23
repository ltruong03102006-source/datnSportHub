<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category', 50)->default('drink')->after('name'); // Phân loại
            $table->string('pricing_type', 50)->default('retail')->after('category'); // Hình thức bán
            $table->integer('stock')->nullable()->after('price'); // Tồn kho (null = Vô hạn)
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['category', 'pricing_type', 'stock']);
        });
    }
};