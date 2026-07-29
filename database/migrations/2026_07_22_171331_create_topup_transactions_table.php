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
        Schema::create('topup_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            $table->string('code')->unique();
            $table->decimal('amount', 12, 2);

            $table->string('payment_method')->default('vnpay');

            $table->string('vnpay_txn_ref')->nullable()->index();
            $table->string('vnpay_transaction_no')->nullable();
            $table->string('vnpay_response_code')->nullable();

            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'expired',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['wallet_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_transactions');
    }
};
