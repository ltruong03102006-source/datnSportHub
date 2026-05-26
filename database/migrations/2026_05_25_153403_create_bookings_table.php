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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('court_id')
                ->constrained('courts')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('slot_date');

            $table->time('start_time');

            $table->time('end_time');

            $table->decimal('total_price', 12, 2);

            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled',
                'rejected'
            ])->default('pending');

            $table->text('note')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
