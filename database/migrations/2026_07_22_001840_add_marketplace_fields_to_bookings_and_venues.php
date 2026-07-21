<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. CẬP NHẬT BẢNG BOOKINGS
        Schema::table('bookings', function (Blueprint $table) {
            // Snapshot số tiền
            $table->decimal('platform_fee', 15, 0)->default(0)->after('total_price');
            $table->decimal('owner_earnings', 15, 0)->default(0)->after('platform_fee');
            
            // Decimal(5,2) lưu %. VD: 10.50 (10.5%). Giá trị này là Snapshot tĩnh.
            $table->decimal('commission_rate', 5, 2)->default(0)->after('owner_earnings');
            
            // Trạng thái đối soát
            $table->string('settlement_status', 20)->default('pending')->after('payment_status');
            $table->timestamp('settled_at')->nullable()->after('settlement_status');

            $table->index('settlement_status');
        });

        // 2. CẬP NHẬT BẢNG VENUES
        Schema::table('venues', function (Blueprint $table) {
            // Nullable: Nếu null thì lấy phí mặc định của hệ thống
            $table->decimal('commission_rate', 5, 2)->nullable()->after('status')
                  ->comment('Tỷ lệ hoa hồng cấu hình riêng cho cơ sở này');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['platform_fee', 'owner_earnings', 'commission_rate', 'settlement_status', 'settled_at']);
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};