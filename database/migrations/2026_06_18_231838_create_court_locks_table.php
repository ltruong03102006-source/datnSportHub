<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('court_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->date('lock_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('reason');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('court_locks');
    }
};