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
        Schema::create('venue_update_requests', function (Blueprint $table) {
            $table->id();
            
            // Sân nào đang yêu cầu sửa
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            
            // Lưu TOÀN BỘ dữ liệu mới dưới dạng JSON
            $table->json('requested_data')->comment('Dữ liệu mới chủ sân muốn cập nhật');
            
            // Trạng thái yêu cầu
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Lời nhắn của Admin nếu từ chối
            $table->text('admin_note')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_update_requests');
    }
};
