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
        Schema::table('venues', function (Blueprint $table) {
            if (! Schema::hasColumn('venues', 'debt_suspended_at')) {
                $table->timestamp('debt_suspended_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('venues', 'suspended_reason')) {
                $table->string('suspended_reason')->nullable()->after('debt_suspended_at');
            }

            if (! Schema::hasColumn('venues', 'auto_suspend_enabled')) {
                $table->boolean('auto_suspend_enabled')->default(true)->after('suspended_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            if (Schema::hasColumn('venues', 'auto_suspend_enabled')) {
                $table->dropColumn('auto_suspend_enabled');
            }

            if (Schema::hasColumn('venues', 'suspended_reason')) {
                $table->dropColumn('suspended_reason');
            }

            if (Schema::hasColumn('venues', 'debt_suspended_at')) {
                $table->dropColumn('debt_suspended_at');
            }
        });
    }
};
