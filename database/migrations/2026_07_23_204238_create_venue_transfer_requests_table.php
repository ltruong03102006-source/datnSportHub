<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_transfer_requests', function (Blueprint $table) {
            $table->id();
            
            // Sân cơ sở được chuyển nhượng
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            
            // Chủ cũ (người tạo yêu cầu)
            $table->foreignId('from_owner_id')->constrained('users')->cascadeOnDelete();
            
            // Chủ mới (người nhận qua Email)
            $table->foreignId('to_owner_id')->constrained('users')->cascadeOnDelete();
            
            // Trạng thái: pending (Chờ duyệt), approved (Đã duyệt), rejected (Từ chối)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Ghi chú của Admin nếu từ chối
            $table->text('admin_note')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_transfer_requests');
    }
};