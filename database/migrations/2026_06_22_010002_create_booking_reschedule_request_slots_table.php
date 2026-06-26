<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('booking_reschedule_request_slots', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('booking_reschedule_request_id'); $table->foreign('booking_reschedule_request_id', 'brs_request_fk')->references('id')->on('booking_reschedule_requests')->cascadeOnDelete(); $table->foreignId('booking_id')->constrained()->cascadeOnDelete(); $table->date('new_slot_date'); $table->foreignId('new_time_slot_id')->constrained('time_slots')->cascadeOnDelete(); $table->timestamps(); $table->unique(['booking_reschedule_request_id','booking_id'], 'brs_request_booking_unique'); }); } public function down(): void { Schema::dropIfExists('booking_reschedule_request_slots'); } };
