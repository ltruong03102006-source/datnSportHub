<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('province_code', 10)->index();
            $table->string('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
        Schema::dropIfExists('provinces');
    }
};
