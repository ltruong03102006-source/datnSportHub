<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'booking_id')) {
                $table->foreignId('booking_id')
                    ->nullable()
                    ->after('wallet_id')
                    ->constrained('bookings')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('wallet_transactions', 'withdrawal_request_id')) {
                $table->foreignId('withdrawal_request_id')
                    ->nullable()
                    ->after('booking_id')
                    ->constrained('withdrawal_requests')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('wallet_transactions', 'reference')) {
                $table->string('reference')->nullable()->unique()->after('withdrawal_request_id');
            }

            if (! Schema::hasColumn('wallet_transactions', 'balance_before')) {
                $table->decimal('balance_before', 15, 0)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('wallet_transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('description');
            }
        });

        DB::statement("ALTER TABLE `wallet_transactions` MODIFY `type` VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE `wallet_transactions` MODIFY `amount` DECIMAL(15, 0) NOT NULL");
        DB::statement("ALTER TABLE `wallet_transactions` MODIFY `balance_after` DECIMAL(15, 0) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'metadata')) {
                $table->dropColumn('metadata');
            }

            if (Schema::hasColumn('wallet_transactions', 'balance_before')) {
                $table->dropColumn('balance_before');
            }

            if (Schema::hasColumn('wallet_transactions', 'reference')) {
                $table->dropUnique(['reference']);
                $table->dropColumn('reference');
            }

            if (Schema::hasColumn('wallet_transactions', 'withdrawal_request_id')) {
                $table->dropConstrainedForeignId('withdrawal_request_id');
            }

            if (Schema::hasColumn('wallet_transactions', 'booking_id')) {
                $table->dropConstrainedForeignId('booking_id');
            }
        });
    }
};
