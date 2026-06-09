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
        Schema::create('revenue_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venue_id')
                ->constrained('venues')
                ->cascadeOnDelete();

            $table->enum('period_type', [
                'daily',
                'weekly',
                'monthly'
            ]);

            $table->date('period_date');

            $table->integer('total_bookings')
                ->default(0);

            $table->decimal('total_revenue', 12, 2)
                ->default(0);

            $table->timestamp('created_at')
                ->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_summaries');
    }
};
