<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_packages', function (Blueprint $table) {
            // Các cột phục vụ luồng đặt gói mới. Chỉ thêm nếu thiếu để không ảnh hưởng dữ liệu cũ.
            if (! Schema::hasColumn('booking_packages', 'weekly_sessions')) {
                $table->unsignedTinyInteger('weekly_sessions')->default(1)->after('end_date');
            }

            if (! Schema::hasColumn('booking_packages', 'total_sessions')) {
                $table->unsignedSmallInteger('total_sessions')->default(0)->after('weekly_sessions');
            }

            if (! Schema::hasColumn('booking_packages', 'used_sessions')) {
                $table->unsignedSmallInteger('used_sessions')->default(0)->after('total_sessions');
            }

            if (! Schema::hasColumn('booking_packages', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('used_sessions');
            }

            if (! Schema::hasColumn('booking_packages', 'final_amount')) {
                $table->decimal('final_amount', 12, 2)->default(0)->after('discount_amount');
            }

            if (! Schema::hasColumn('booking_packages', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('booking_packages', 'paused_at')) {
                $table->timestamp('paused_at')->nullable()->after('paid_at');
            }

            if (! Schema::hasColumn('booking_packages', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('paused_at');
            }

            if (! Schema::hasColumn('booking_packages', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('cancelled_at');
            }
        });

        Schema::table('booking_package_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_package_sessions', 'session_order')) {
                $table->unsignedTinyInteger('session_order')->default(1)->after('weekday');
            }

            if (! Schema::hasColumn('booking_package_sessions', 'price_per_session')) {
                $table->decimal('price_per_session', 12, 2)->default(0)->after('session_order');
            }
        });

        // Mở rộng enum status để nhận pending_payment/paused/expired.
        // Các giá trị cũ active/cancelled/completed vẫn được giữ nguyên.
        if (Schema::hasColumn('booking_packages', 'status')) {
            DB::statement("ALTER TABLE booking_packages MODIFY status ENUM('pending_payment','active','paused','completed','cancelled','expired') NOT NULL DEFAULT 'pending_payment'");
        }

        // Backfill an toàn cho dữ liệu cũ: nếu trước đây chỉ có total_price thì copy sang cột tiền mới.
        if (Schema::hasColumn('booking_packages', 'total_price')) {
            DB::table('booking_packages')
                ->where('total_amount', 0)
                ->update([
                    'total_amount' => DB::raw('COALESCE(total_price, 0)'),
                    'final_amount' => DB::raw('GREATEST(COALESCE(total_price, 0) - COALESCE(discount_amount, 0), 0)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('booking_package_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('booking_package_sessions', 'price_per_session')) {
                $table->dropColumn('price_per_session');
            }

            if (Schema::hasColumn('booking_package_sessions', 'session_order')) {
                $table->dropColumn('session_order');
            }
        });

        Schema::table('booking_packages', function (Blueprint $table) {
            foreach ([
                'completed_at',
                'cancelled_at',
                'paused_at',
                'paid_at',
                'final_amount',
                'total_amount',
                'used_sessions',
                'total_sessions',
                'weekly_sessions',
            ] as $column) {
                if (Schema::hasColumn('booking_packages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
