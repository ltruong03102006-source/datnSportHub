<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('withdrawal_requests')) {
            return;
        }

        if (Schema::hasColumn('withdrawal_requests', 'amount')) {
            DB::statement('ALTER TABLE `withdrawal_requests` MODIFY `amount` DECIMAL(12, 2) NOT NULL');
        }

        if (Schema::hasColumn('withdrawal_requests', 'code')) {
            DB::statement('ALTER TABLE `withdrawal_requests` MODIFY `code` VARCHAR(255) NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('withdrawal_requests')) {
            return;
        }

        if (Schema::hasColumn('withdrawal_requests', 'amount')) {
            DB::statement('ALTER TABLE `withdrawal_requests` MODIFY `amount` DECIMAL(15, 0) NOT NULL');
        }

        if (Schema::hasColumn('withdrawal_requests', 'code')) {
            DB::statement('ALTER TABLE `withdrawal_requests` MODIFY `code` VARCHAR(255) NULL');
        }
    }
};
