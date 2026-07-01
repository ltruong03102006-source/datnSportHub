<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các cột cho hệ thống thông báo nội bộ của SportHub.
     * Không xóa các cột notifiable_* và data cũ để tránh mất dữ liệu hiện có.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
            }

            if (! Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->after('type');
            }

            if (! Schema::hasColumn('notifications', 'content')) {
                $table->text('content')->nullable()->after('title');
            }

            if (! Schema::hasColumn('notifications', 'link')) {
                $table->string('link')->nullable()->after('content');
            }

            if (! Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('link');
            }
        });
    }

    public function down(): void
    {
        // Không rollback các cột để tránh làm mất dữ liệu thông báo đã tạo.
    }
};
