<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_packages', function (Blueprint $table) {
            // Migration an toàn cho database đã có dữ liệu:
            // chỉ thêm cột nếu database hiện tại chưa có.
            if (! Schema::hasColumn('venue_packages', 'max_sessions_per_week')) {
                $table->unsignedTinyInteger('max_sessions_per_week')
                    ->default(7)
                    ->after('duration');
            }

            if (! Schema::hasColumn('venue_packages', 'max_subscribers')) {
                $table->unsignedInteger('max_subscribers')
                    ->nullable()
                    ->after('discount_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('venue_packages', function (Blueprint $table) {
            if (Schema::hasColumn('venue_packages', 'max_sessions_per_week')) {
                $table->dropColumn('max_sessions_per_week');
            }

            if (Schema::hasColumn('venue_packages', 'max_subscribers')) {
                $table->dropColumn('max_subscribers');
            }
        });
    }
};
