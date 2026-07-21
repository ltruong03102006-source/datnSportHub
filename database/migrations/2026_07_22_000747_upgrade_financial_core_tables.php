<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TẠO BẢNG WALLETS MỚI
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            
            $table->decimal('balance', 15, 0)->default(0);
            $table->decimal('available_balance', 15, 0)->default(0);
            $table->decimal('pending_balance', 15, 0)->default(0);
            $table->decimal('credit_limit', 15, 0)->default(0);
            
            $table->string('currency', 3)->default('VND');
            $table->string('status', 20)->default('active');
            
            $table->timestamps();
            $table->index(['owner_id', 'status']);
        });

        // 2. NÂNG CẤP BẢNG WITHDRAWAL_REQUESTS CÓ SẴN
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Cắt đứt quan hệ với users, chuyển sang wallets
            $table->dropForeign(['user_id']); 
            $table->dropColumn('user_id');
            
            $table->foreignId('wallet_id')->after('id')->constrained('wallets')->cascadeOnDelete();
            
            // Đảm bảo amount là Decimal (nếu trước đây bạn dùng integer/float thì nó sẽ change)
            $table->decimal('amount', 15, 0)->change();
            
            $table->timestamp('processed_at')->nullable()->after('admin_note');
            $table->index(['wallet_id', 'status']);
        });

        // 3. NÂNG CẤP BẢNG WALLET_TRANSACTIONS CÓ SẴN
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Cắt đứt quan hệ với users, chuyển sang wallets
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->foreignId('wallet_id')->after('id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->after('wallet_id')->constrained('bookings')->nullOnDelete();
            $table->foreignId('withdrawal_request_id')->nullable()->after('booking_id')->constrained('withdrawal_requests')->nullOnDelete();
            
            $table->string('reference')->unique()->after('withdrawal_request_id')->comment('Mã giao dịch duy nhất (Idempotency Key)');
            
            $table->decimal('amount', 15, 0)->change();
            $table->decimal('balance_before', 15, 0)->after('amount')->default(0);
            $table->decimal('balance_after', 15, 0)->change();
            
            $table->json('metadata')->nullable()->after('description');
            
            $table->index(['wallet_id', 'type']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        // Rollback nếu cần thiết (Hạ cấp về kiến trúc cũ)
        // ... (Trong môi trường dev có thể dùng migrate:fresh để nhanh gọn)
    }
};