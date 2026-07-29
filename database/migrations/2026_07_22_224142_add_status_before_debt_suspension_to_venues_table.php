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
            if (! Schema::hasColumn('venues', 'status_before_debt_suspension')) {
                $table->string('status_before_debt_suspension')
                    ->nullable()
                    ->after('suspended_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            if (Schema::hasColumn('venues', 'status_before_debt_suspension')) {
                $table->dropColumn('status_before_debt_suspension');
            }
        });
    }
};
