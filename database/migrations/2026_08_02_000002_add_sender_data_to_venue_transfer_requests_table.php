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
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('venue_transfer_requests', 'sender_data')) {
                $table->json('sender_data')->nullable()->after('receiver_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            if (Schema::hasColumn('venue_transfer_requests', 'sender_data')) {
                $table->dropColumn('sender_data');
            }
        });
    }
};
