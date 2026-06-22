<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('booking_reschedule_requests', function (Blueprint $table) { $table->time('old_start_time')->nullable()->after('old_slot_date'); $table->time('old_end_time')->nullable()->after('old_start_time'); }); } public function down(): void { Schema::table('booking_reschedule_requests', fn (Blueprint $table) => $table->dropColumn(['old_start_time','old_end_time'])); } };
