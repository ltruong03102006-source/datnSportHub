<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('booking_reschedule_request_slots', function (Blueprint $table) { $table->date('old_slot_date')->nullable()->after('booking_id'); $table->time('old_start_time')->nullable()->after('old_slot_date'); $table->time('old_end_time')->nullable()->after('old_start_time'); }); } public function down(): void { Schema::table('booking_reschedule_request_slots', fn (Blueprint $table) => $table->dropColumn(['old_slot_date','old_start_time','old_end_time'])); } };
