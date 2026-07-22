<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']); 
            $table->dropColumn('user_id');
            $table->foreignId('wallet_id')->after('id')->constrained('wallets')->cascadeOnDelete();
            $table->decimal('amount', 15, 0)->change();
            $table->timestamp('processed_at')->nullable()->after('admin_note');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('wallet_id')->after('id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->after('wallet_id')->constrained('bookings')->nullOnDelete();
            $table->foreignId('withdrawal_request_id')->nullable()->after('booking_id')->constrained('withdrawal_requests')->nullOnDelete();
            $table->string('reference')->unique()->after('withdrawal_request_id');
            
            // ---> FIX LỖI Ở ĐÂY: Mở rộng cột type thành chuỗi 50 ký tự <---
            $table->string('type', 50)->change();
            
            $table->decimal('amount', 15, 0)->change();
            $table->decimal('balance_before', 15, 0)->after('amount')->default(0);
            $table->decimal('balance_after', 15, 0)->change();
            $table->json('metadata')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        // ...
    }
};