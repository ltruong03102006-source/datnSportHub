<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('province_code', 10)->nullable()->after('address')->index();
            $table->string('ward_code', 10)->nullable()->after('province_code')->index();
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['province_code', 'ward_code']);
        });
    }
};
