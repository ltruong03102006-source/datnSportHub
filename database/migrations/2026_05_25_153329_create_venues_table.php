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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('sport_id')
                ->constrained('sports')
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('address');

            $table->decimal('lat', 10, 7)
                ->nullable();

            $table->decimal('lng', 10, 7)
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->string('banner')
                ->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'inactive',
                'suspended'
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
