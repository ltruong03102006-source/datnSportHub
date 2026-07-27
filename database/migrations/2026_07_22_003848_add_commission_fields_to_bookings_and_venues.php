<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('platform_fee', 15, 0)->default(0)->after('total_price');
            $table->decimal('owner_earnings', 15, 0)->default(0)->after('platform_fee');
            $table->decimal('commission_rate', 5, 2)->default(0)->after('owner_earnings');
            $table->string('settlement_status', 20)->default('pending')->after('payment_status');
            $table->timestamp('settled_at')->nullable()->after('settlement_status');
            $table->index('settlement_status');
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        // ...
    }
};