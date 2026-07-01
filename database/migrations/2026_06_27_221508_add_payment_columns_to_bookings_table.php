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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'payment_method')) {
                $table->string('payment_method')->default('cash'); // 'cash', 'vnpay', 'transfer'
            }
            if (!Schema::hasColumn('bookings', 'payment_status')) {
                $table->string('payment_status')->default('unpaid'); // 'unpaid', 'paid', 'failed'
            }
            if (!Schema::hasColumn('bookings', 'vnpay_tran_id')) {
                $table->string('vnpay_tran_id')->nullable(); // Lưu mã giao dịch VNPay
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'vnpay_tran_id']);
        });
    }
};
