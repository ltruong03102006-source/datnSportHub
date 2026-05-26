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
        Schema::create('slot_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('time_slot_id')
                ->constrained('time_slots')
                ->cascadeOnDelete();

            $table->decimal('price', 12, 2);

            $table->enum('price_type', [
                'normal',
                'peak'
            ])->default('normal');

            $table->tinyInteger('day_of_week');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_prices');
    }
};
