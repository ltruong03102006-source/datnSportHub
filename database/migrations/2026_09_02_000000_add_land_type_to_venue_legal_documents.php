<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('venue_legal_documents', 'land_type')) {
            Schema::table('venue_legal_documents', function (Blueprint $table) {
                $table->string('land_type')->nullable()->after('business_license_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('venue_legal_documents', 'land_type')) {
            Schema::table('venue_legal_documents', function (Blueprint $table) {
                $table->dropColumn('land_type');
            });
        }
    }
};
