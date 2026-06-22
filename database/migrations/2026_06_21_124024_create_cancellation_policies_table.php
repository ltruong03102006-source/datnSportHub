<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->integer('hours_before')->comment('Số giờ tối thiểu trước khi ca bắt đầu');
            $table->integer('fee_percent')->comment('Phần trăm phí phạt');
            $table->timestamps();

            // Đảm bảo không tạo trùng mốc thời gian trong cùng 1 cơ sở
            $table->unique(['venue_id', 'hours_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_policies');
    }
};
