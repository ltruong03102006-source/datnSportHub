<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('review_reminder_sent_at');
            }

            if (! Schema::hasColumn('bookings', 'checked_in_by')) {
                $table->foreignId('checked_in_by')
                    ->nullable()
                    ->after('checked_in_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('bookings', 'checkin_note')) {
                $table->text('checkin_note')->nullable()->after('checked_in_by');
            }

            if (! Schema::hasColumn('bookings', 'no_show_at')) {
                $table->timestamp('no_show_at')->nullable()->after('checkin_note');
            }

            if (! Schema::hasColumn('bookings', 'no_show_by')) {
                $table->foreignId('no_show_by')
                    ->nullable()
                    ->after('no_show_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'no_show_by')) {
                $table->dropConstrainedForeignId('no_show_by');
            }

            if (Schema::hasColumn('bookings', 'checked_in_by')) {
                $table->dropConstrainedForeignId('checked_in_by');
            }

            foreach (['no_show_at', 'checkin_note', 'checked_in_at'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
