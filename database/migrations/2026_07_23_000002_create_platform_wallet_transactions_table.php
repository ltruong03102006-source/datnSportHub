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
        Schema::create('platform_wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('platform_wallet_id')
                ->constrained('platform_wallets')
                ->cascadeOnDelete();

            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            $table->string('reference')->nullable()->index();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['platform_wallet_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['type', 'created_at']);
            $table->unique(
                ['reference_type', 'reference_id', 'type'],
                'platform_wallet_tx_reference_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_wallet_transactions');
    }
};
