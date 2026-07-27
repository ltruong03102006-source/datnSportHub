<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            
            $table->decimal('balance', 15, 0)->default(0);
            $table->decimal('available_balance', 15, 0)->default(0);
            $table->decimal('pending_balance', 15, 0)->default(0);
            $table->decimal('credit_limit', 15, 0)->default(0);
            
            $table->string('currency', 3)->default('VND');
            $table->string('status', 20)->default('active');
            
            $table->timestamps();
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};