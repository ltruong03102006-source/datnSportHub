<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('cancellation_fee', 12, 2)->default(0)->after('total_price');
            $table->decimal('refund_amount', 12, 2)->default(0)->after('cancellation_fee');
            $table->enum('refund_status', ['none', 'pending', 'refunded'])->default('none')->after('refund_amount');
        });
    }

    public function down(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancellation_fee', 'refund_amount', 'refund_status']);
        });
    }
};