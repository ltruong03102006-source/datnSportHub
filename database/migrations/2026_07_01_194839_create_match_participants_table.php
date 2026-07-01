<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cập nhật bảng match_posts an toàn
        Schema::table('match_posts', function (Blueprint $table) {
            // Kiểm tra nếu CHƯA CÓ cột total_players thì mới tạo
            if (!Schema::hasColumn('match_posts', 'total_players')) {
                $table->integer('total_players')->default(2)->after('skill_level');
            }
            
            // Kiểm tra nếu CHƯA CÓ cột needed_players thì mới tạo
            if (!Schema::hasColumn('match_posts', 'needed_players')) {
                $table->integer('needed_players')->default(1)->after('total_players');
            }
            
            // Cập nhật Enum
            DB::statement("ALTER TABLE match_posts MODIFY COLUMN status ENUM('open', 'closed', 'full', 'cancelled', 'expired') DEFAULT 'open'");
        });

        // 2. Tạo bảng match_participants an toàn
        if (!Schema::hasTable('match_participants')) {
            Schema::create('match_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_post_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();

                $table->unique(['match_post_id', 'user_id']); 
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('match_participants');
        
        Schema::table('match_posts', function (Blueprint $table) {
            if (Schema::hasColumn('match_posts', 'total_players')) {
                $table->dropColumn('total_players');
            }
            if (Schema::hasColumn('match_posts', 'needed_players')) {
                $table->dropColumn('needed_players');
            }
            DB::statement("ALTER TABLE match_posts MODIFY COLUMN status ENUM('open', 'closed') DEFAULT 'open'");
        });
    }
};