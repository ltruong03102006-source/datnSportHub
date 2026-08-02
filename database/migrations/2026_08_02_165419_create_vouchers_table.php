<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('discount_type'); // percent, fixed
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_booking_value', 12, 2)->nullable();
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            
            $table->unsignedBigInteger('sport_field_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            
            $table->boolean('applies_to_all_fields')->default(false);
            $table->json('time_slots')->nullable();
            $table->json('apply_days')->nullable();
            
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->string('status')->default('active');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
