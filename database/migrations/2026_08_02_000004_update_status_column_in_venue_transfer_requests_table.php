<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Chuyển cột status sang varchar(50) để hỗ trợ các trạng thái mới (draft, sent, filled, signed, approved, rejected)
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_transfer_requests', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
        });
    }
};
