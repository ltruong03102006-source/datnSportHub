<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('venue_transfer_requests', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('contract_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};
