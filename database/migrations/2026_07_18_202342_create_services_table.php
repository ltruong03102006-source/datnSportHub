<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->string('name'); // VD: Nước suối Lavie, Thuê vợt cầu lông
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2); // Giá tiền
            $table->string('unit', 50)->default('cái'); // Đơn vị tính: chai, cái, bộ, giờ...
            $table->string('image')->nullable(); // Ảnh minh họa dịch vụ
            $table->boolean('is_active')->default(true); // Chủ sân có thể tắt/ẩn dịch vụ nếu hết hàng
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};