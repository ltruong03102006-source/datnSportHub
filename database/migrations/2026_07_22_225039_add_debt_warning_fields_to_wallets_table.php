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
        Schema::table('wallets', function (Blueprint $table) {
            if (! Schema::hasColumn('wallets', 'debt_warning_sent_at')) {
                $table->timestamp('debt_warning_sent_at')->nullable()->after('credit_limit');
            }

            if (! Schema::hasColumn('wallets', 'debt_warning_level')) {
                $table->decimal('debt_warning_level', 5, 2)->nullable()->after('debt_warning_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            if (Schema::hasColumn('wallets', 'debt_warning_level')) {
                $table->dropColumn('debt_warning_level');
            }

            if (Schema::hasColumn('wallets', 'debt_warning_sent_at')) {
                $table->dropColumn('debt_warning_sent_at');
            }
        });
    }
};
