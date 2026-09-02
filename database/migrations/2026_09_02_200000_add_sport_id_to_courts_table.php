<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable sport_id to courts and attempts to populate from venues.sport_id for compatibility.
     */
    public function up(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->unsignedBigInteger('sport_id')->nullable()->after('venue_id');
            $table->index('sport_id');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('set null');
        });

        // Try to populate courts.sport_id from venues.sport_id if present (legacy data)
        try {
            DB::statement('UPDATE courts c JOIN venues v ON c.venue_id = v.id SET c.sport_id = v.sport_id WHERE v.sport_id IS NOT NULL');
        } catch (\Throwable $e) {
            // Ignore if DB doesn't support JOIN in UPDATE or other edge cases; it's a best-effort migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->dropForeign(['sport_id']);
            $table->dropIndex(['sport_id']);
            $table->dropColumn('sport_id');
        });
    }
};
