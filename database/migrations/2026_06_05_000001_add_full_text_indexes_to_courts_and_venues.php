<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->supportsFullTextIndexes()) {
            return;
        }

        Schema::table('courts', function (Blueprint $table) {
            $table->fullText('name', 'courts_name_fulltext');
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->fullText(['name', 'address'], 'venues_name_address_fulltext');
        });
    }

    public function down(): void
    {
        if (! $this->supportsFullTextIndexes()) {
            return;
        }

        Schema::table('courts', function (Blueprint $table) {
            $table->dropFullText('courts_name_fulltext');
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropFullText('venues_name_address_fulltext');
        });
    }

    private function supportsFullTextIndexes(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
