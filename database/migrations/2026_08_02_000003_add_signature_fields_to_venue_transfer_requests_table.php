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
            if (!Schema::hasColumn('venue_transfer_requests', 'receiver_signed_at')) {
                $table->timestamp('receiver_signed_at')->nullable()->after('sender_data');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'receiver_signed_ip')) {
                $table->string('receiver_signed_ip', 45)->nullable()->after('receiver_signed_at');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'receiver_signed_account')) {
                $table->string('receiver_signed_account')->nullable()->after('receiver_signed_ip');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'sender_signed_at')) {
                $table->timestamp('sender_signed_at')->nullable()->after('receiver_signed_account');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'sender_signed_ip')) {
                $table->string('sender_signed_ip', 45)->nullable()->after('sender_signed_at');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'sender_signed_account')) {
                $table->string('sender_signed_account')->nullable()->after('sender_signed_ip');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            $columns = [
                'receiver_signed_at',
                'receiver_signed_ip',
                'receiver_signed_account',
                'sender_signed_at',
                'sender_signed_ip',
                'sender_signed_account',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('venue_transfer_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
