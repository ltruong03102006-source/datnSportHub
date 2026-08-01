<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('venue_transfer_requests', 'price')) {
                $table->decimal('price', 15, 2)->nullable()->after('to_owner_id');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'contract_date')) {
                $table->date('contract_date')->nullable()->after('price');
            }
            if (!Schema::hasColumn('venue_transfer_requests', 'contract_location')) {
                $table->string('contract_location')->nullable()->after('contract_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            $table->dropColumn(['price', 'contract_date', 'contract_location']);
        });
    }
};
