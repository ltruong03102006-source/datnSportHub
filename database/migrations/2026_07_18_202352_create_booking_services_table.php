<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->integer('quantity')->default(1); // Số lượng mua/thuê
            $table->decimal('price', 12, 2); // QUAN TRỌNG: Lưu lại giá của dịch vụ TẠI THỜI ĐIỂM ĐẶT để chốt bill, phòng trường hợp sau này chủ sân tăng giá thì bill cũ không bị sai lệch.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};