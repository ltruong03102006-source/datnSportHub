<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('match_posts', 'ward_code')) {
                $table->string('ward_code', 50)->nullable()->after('province_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_posts', function (Blueprint $table) {
            if (Schema::hasColumn('match_posts', 'ward_code')) {
                $table->dropColumn('ward_code');
            }
        });
    }
};
