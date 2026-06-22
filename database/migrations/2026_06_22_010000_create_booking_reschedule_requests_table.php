<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('time_slot_id')->nullable()->after('court_id')->constrained('time_slots')->nullOnDelete();
        });

        Schema::create('booking_reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('old_slot_date');
            $table->foreignId('old_time_slot_id')->nullable()->constrained('time_slots')->nullOnDelete();
            $table->date('new_slot_date');
            $table->foreignId('new_time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('owner_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_reschedule_requests');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropConstrainedForeignId('time_slot_id'));
    }
};
