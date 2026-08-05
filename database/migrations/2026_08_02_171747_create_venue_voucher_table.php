<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_voucher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venue_id');
            $table->unsignedBigInteger('voucher_id');
            $table->timestamps();

            $table->unique(['venue_id', 'voucher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_voucher');
    }
};
