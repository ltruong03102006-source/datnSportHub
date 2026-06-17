<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venue_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venue_id')
                ->constrained('venues')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('action', ['activated', 'deactivated'])
                ->comment('Hành động thực hiện');

            $table->string('old_status')
                ->comment('Trạng thái trước đó');

            $table->string('new_status')
                ->comment('Trạng thái mới');

            $table->text('reason')
                ->nullable()
                ->comment('Lý do thay đổi');

            $table->text('notes')
                ->nullable()
                ->comment('Ghi chú thêm');

            $table->timestamps();

            // Index để tìm kiếm nhanh
            $table->index('venue_id');
            $table->index('admin_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_logs');
    }
};
