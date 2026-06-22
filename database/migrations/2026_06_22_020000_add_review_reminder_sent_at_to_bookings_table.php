<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'review_reminder_sent_at')) {
                $table->timestamp('review_reminder_sent_at')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'review_reminder_sent_at')) {
                $table->dropColumn('review_reminder_sent_at');
            }
        });
    }
};
