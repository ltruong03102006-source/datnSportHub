<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking_package_session_slots')) {
            Schema::create('booking_package_session_slots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_package_session_id')
                    ->constrained('booking_package_sessions')
                    ->cascadeOnDelete();
                $table->foreignId('time_slot_id')
                    ->constrained('time_slots')
                    ->cascadeOnDelete();
                $table->unsignedInteger('slot_order')->default(1);
                $table->decimal('price', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(
                    ['booking_package_session_id', 'time_slot_id'],
                    'bp_session_slot_unique'
                );
                $table->index(
                    ['booking_package_session_id', 'slot_order'],
                    'bp_session_slot_order_index'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_package_session_slots');
    }
};
