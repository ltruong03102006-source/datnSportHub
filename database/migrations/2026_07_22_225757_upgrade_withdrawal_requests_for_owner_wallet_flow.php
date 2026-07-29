<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('withdrawal_requests')) {
            Schema::create('withdrawal_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
                $table->string('code')->unique();
                $table->decimal('amount', 12, 2);
                $table->string('bank_name');
                $table->string('bank_account_number', 50);
                $table->string('bank_account_holder');
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
                $table->text('owner_note')->nullable();
                $table->text('admin_note')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['owner_id', 'status']);
                $table->index(['wallet_id', 'status']);
                $table->index(['status', 'created_at']);
            });

            return;
        }

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('withdrawal_requests', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('withdrawal_requests', 'wallet_id')) {
                $table->foreignId('wallet_id')
                    ->nullable()
                    ->after('owner_id')
                    ->constrained('wallets')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('withdrawal_requests', 'code')) {
                $table->string('code')->nullable()->unique()->after('wallet_id');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'bank_account_number')) {
                $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'bank_account_holder')) {
                $table->string('bank_account_holder')->nullable()->after('bank_account_number');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'owner_note')) {
                $table->text('owner_note')->nullable()->after('status');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->after('admin_note')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('withdrawal_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('withdrawal_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            }
        });

        DB::statement("ALTER TABLE `withdrawal_requests` MODIFY `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");

        if (
            Schema::hasColumn('withdrawal_requests', 'bank_account_no')
            && Schema::hasColumn('withdrawal_requests', 'bank_account_number')
        ) {
            DB::statement("UPDATE `withdrawal_requests` SET `bank_account_number` = `bank_account_no` WHERE `bank_account_number` IS NULL");
        }

        if (
            Schema::hasColumn('withdrawal_requests', 'bank_account_name')
            && Schema::hasColumn('withdrawal_requests', 'bank_account_holder')
        ) {
            DB::statement("UPDATE `withdrawal_requests` SET `bank_account_holder` = `bank_account_name` WHERE `bank_account_holder` IS NULL");
        }

        if (Schema::hasColumn('withdrawal_requests', 'wallet_id')) {
            DB::statement("
                UPDATE `withdrawal_requests` wr
                INNER JOIN `wallets` w ON w.`id` = wr.`wallet_id`
                SET wr.`owner_id` = w.`owner_id`
                WHERE wr.`owner_id` IS NULL
            ");
        }

        DB::statement("
            UPDATE `withdrawal_requests`
            SET `code` = CONCAT('WD-', DATE_FORMAT(COALESCE(`created_at`, NOW()), '%Y%m%d%H%i%s'), '-', `id`)
            WHERE `code` IS NULL
        ");

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->index(['owner_id', 'status'], 'withdrawal_requests_owner_status_index');
            $table->index(['wallet_id', 'status'], 'withdrawal_requests_wallet_status_index');
            $table->index(['status', 'created_at'], 'withdrawal_requests_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
