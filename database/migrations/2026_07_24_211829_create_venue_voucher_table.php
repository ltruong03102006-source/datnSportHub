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
        Schema::create('venue_voucher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_voucher');
    }
};
