<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_reschedule_requests', function (Blueprint $table) {
            $table->decimal('old_price', 12, 2)->default(0)->after('new_end_time');
            $table->decimal('new_price', 12, 2)->default(0)->after('old_price');
            $table->decimal('price_difference', 12, 2)->default(0)->after('new_price');
            $table->string('payment_status')->default('none')->after('price_difference');
        });
    }

    public function down(): void
    {
        Schema::table('booking_reschedule_requests', function (Blueprint $table) {
            $table->dropColumn(['old_price', 'new_price', 'price_difference', 'payment_status']);
        });
    }
};
