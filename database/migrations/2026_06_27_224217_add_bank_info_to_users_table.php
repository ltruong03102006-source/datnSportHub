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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable();
                $table->string('bank_account_no')->nullable();
                $table->string('bank_account_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_no', 'bank_account_name']);
        });
    }
};
