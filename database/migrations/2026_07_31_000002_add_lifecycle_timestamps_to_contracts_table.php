<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('signed_at');
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            $table->timestamp('expired_at')->nullable()->after('rejected_at');
            $table->timestamp('terminated_at')->nullable()->after('expired_at');
            $table->index(['status', 'start_date']);
            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex(['status', 'start_date']);
            $table->dropIndex(['status', 'end_date']);
            $table->dropColumn(['sent_at', 'rejected_at', 'expired_at', 'terminated_at']);
        });
    }
};
