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

        if (! $this->indexExists('courts', 'courts_name_fulltext')) {
            Schema::table('courts', function (Blueprint $table) {
                $table->fullText('name', 'courts_name_fulltext');
            });
        }

        if (! $this->indexExists('venues', 'venues_name_address_fulltext')) {
            Schema::table('venues', function (Blueprint $table) {
                $table->fullText(['name', 'address'], 'venues_name_address_fulltext');
            });
        }
    }

    public function down(): void
    {
        if (! $this->supportsFullTextIndexes()) {
            return;
        }

        if ($this->indexExists('courts', 'courts_name_fulltext')) {
            Schema::table('courts', function (Blueprint $table) {
                $table->dropFullText('courts_name_fulltext');
            });
        }

        if ($this->indexExists('venues', 'venues_name_address_fulltext')) {
            Schema::table('venues', function (Blueprint $table) {
                $table->dropFullText('venues_name_address_fulltext');
            });
        }
    }

    private function supportsFullTextIndexes(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function indexExists(string $table, string $index): bool
    {
        $result = DB::selectOne(
            'select count(*) as aggregate
             from information_schema.statistics
             where table_schema = ? and table_name = ? and index_name = ?',
            [DB::connection()->getDatabaseName(), $table, $index]
        );

        return (int) $result->aggregate > 0;
    }
};
