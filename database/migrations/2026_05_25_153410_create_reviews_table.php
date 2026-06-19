<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('court_id')
                ->constrained('courts')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // THÊM CỘT BOOKING_ID VÀO ĐÂY
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->tinyInteger('rating');
            $table->text('content')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            // ĐÃ XÓA UNIQUE('court_id', 'user_id') ĐỂ CHO PHÉP ĐÁNH GIÁ NHIỀU LẦN
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};