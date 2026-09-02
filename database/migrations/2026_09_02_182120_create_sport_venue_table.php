<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates pivot table sport_venue and migrates existing sport_id values.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sport_venue')) {
            Schema::create('sport_venue', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sport_id');
                $table->unsignedBigInteger('venue_id');
                $table->timestamps();

                $table->unique(['sport_id', 'venue_id']);
                $table->index('venue_id');
                $table->index('sport_id');

                $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
                $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');
            });

            // Migrate existing sport_id values into pivot table (best-effort)
            if (Schema::hasTable('venues')) {
                $rows = DB::table('venues')->whereNotNull('sport_id')->select('id', 'sport_id')->get();
                $insert = [];
                foreach ($rows as $r) {
                    $insert[] = [
                        'sport_id' => $r->sport_id,
                        'venue_id' => $r->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (!empty($insert)) {
                    DB::table('sport_venue')->insertOrIgnore($insert);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sport_venue');
    }
};
