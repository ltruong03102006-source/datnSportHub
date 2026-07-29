<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->upgradeWithdrawalRequests();
        $this->upgradeWalletTransactions();
    }

    public function down(): void
    {
        // This migration normalizes existing wallet/debt tables. Keep rollback
        // intentionally empty to avoid dropping live finance data.
    }

    private function upgradeWithdrawalRequests(): void
    {
        if (! Schema::hasTable('withdrawal_requests')) {
            return;
        }

        $userForeign = $this->foreignKeyName('withdrawal_requests', 'user_id');

        Schema::table('withdrawal_requests', function (Blueprint $table) use ($userForeign) {
            if ($userForeign) {
                $table->dropForeign($userForeign);
            }

            if (! Schema::hasColumn('withdrawal_requests', 'wallet_id')) {
                $table->foreignId('wallet_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('wallets')
                    ->cascadeOnDelete();
            }
        });

        if (
            Schema::hasColumn('withdrawal_requests', 'user_id')
            && Schema::hasColumn('withdrawal_requests', 'wallet_id')
            && Schema::hasTable('wallets')
        ) {
            DB::statement("
                UPDATE `withdrawal_requests` wr
                INNER JOIN `wallets` w ON w.`owner_id` = wr.`user_id`
                SET wr.`wallet_id` = w.`id`
                WHERE wr.`wallet_id` IS NULL
            ");
        }

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawal_requests', 'user_id')) {
                $table->dropColumn('user_id');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'processed_at')) {
                $processedAt = $table->timestamp('processed_at')->nullable();

                if (Schema::hasColumn('withdrawal_requests', 'admin_note')) {
                    $processedAt->after('admin_note');
                }
            }
        });

        if (Schema::hasColumn('withdrawal_requests', 'amount')) {
            DB::statement('ALTER TABLE `withdrawal_requests` MODIFY `amount` DECIMAL(15, 0) NOT NULL');
        }
    }

    private function upgradeWalletTransactions(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        $userForeign = $this->foreignKeyName('wallet_transactions', 'user_id');

        Schema::table('wallet_transactions', function (Blueprint $table) use ($userForeign) {
            if ($userForeign) {
                $table->dropForeign($userForeign);
            }

            if (! Schema::hasColumn('wallet_transactions', 'wallet_id')) {
                $table->foreignId('wallet_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('wallets')
                    ->cascadeOnDelete();
            }
        });

        if (
            Schema::hasColumn('wallet_transactions', 'user_id')
            && Schema::hasColumn('wallet_transactions', 'wallet_id')
            && Schema::hasTable('wallets')
        ) {
            DB::statement("
                UPDATE `wallet_transactions` wt
                INNER JOIN `wallets` w ON w.`owner_id` = wt.`user_id`
                SET wt.`wallet_id` = w.`id`
                WHERE wt.`wallet_id` IS NULL
            ");
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'user_id')) {
                $table->dropColumn('user_id');
            }

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

        if (Schema::hasColumn('wallet_transactions', 'type')) {
            DB::statement('ALTER TABLE `wallet_transactions` MODIFY `type` VARCHAR(50) NOT NULL');
        }

        if (Schema::hasColumn('wallet_transactions', 'amount')) {
            DB::statement('ALTER TABLE `wallet_transactions` MODIFY `amount` DECIMAL(15, 0) NOT NULL');
        }

        if (Schema::hasColumn('wallet_transactions', 'balance_after')) {
            DB::statement('ALTER TABLE `wallet_transactions` MODIFY `balance_after` DECIMAL(15, 0) NOT NULL');
        }
    }

    private function foreignKeyName(string $table, string $column): ?string
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');
    }
};
