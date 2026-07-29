<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking_reschedule_requests')) {
            return;
        }

        Schema::table('booking_reschedule_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_reschedule_requests', 'request_code')) {
                $table->string('request_code')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('booking_reschedule_requests', 'booking_item_id')) {
                $table->foreignId('booking_item_id')->nullable()->after('user_id')->constrained('booking_items')->nullOnDelete();
            }

            if (! Schema::hasColumn('booking_reschedule_requests', 'new_start_time')) {
                $table->time('new_start_time')->nullable()->after('new_time_slot_id');
            }

            if (! Schema::hasColumn('booking_reschedule_requests', 'new_end_time')) {
                $table->time('new_end_time')->nullable()->after('new_start_time');
            }

            if (! Schema::hasColumn('booking_reschedule_requests', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('owner_note')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('booking_reschedule_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('booking_reschedule_requests', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('booking_reschedule_requests')) {
            return;
        }

        Schema::table('booking_reschedule_requests', function (Blueprint $table) {
            foreach (['booking_item_id', 'approved_by'] as $column) {
                if (Schema::hasColumn('booking_reschedule_requests', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['request_code', 'new_start_time', 'new_end_time', 'approved_at', 'rejected_reason'] as $column) {
                if (Schema::hasColumn('booking_reschedule_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
