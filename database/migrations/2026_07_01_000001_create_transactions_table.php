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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_code')->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method')->default('COD');
            $table->string('payment_gateway')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamp('transaction_time')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['payment_status', 'payment_method']);
            $table->index('transaction_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
