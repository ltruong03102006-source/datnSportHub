<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking_package_sessions')) {
            Schema::create('booking_package_sessions', function (Blueprint $table) {
                $table->id();

                $table->foreignId('booking_package_id')
                    ->constrained('booking_packages')
                    ->cascadeOnDelete();

                $table->foreignId('court_id')
                    ->constrained('courts')
                    ->cascadeOnDelete();

                $table->foreignId('time_slot_id')
                    ->constrained('time_slots')
                    ->cascadeOnDelete();

                // 0 = Chủ nhật, 1 = Thứ 2, ..., 6 = Thứ 7
                $table->unsignedTinyInteger('weekday');

                // Thứ tự hiển thị: Buổi 1, Buổi 2, Buổi 3...
                $table->unsignedTinyInteger('session_order')
                    ->default(1);

                // Lưu giá tại thời điểm khách mua gói
                // Tránh trường hợp sau này chủ sân đổi giá slot làm sai lịch sử
                $table->decimal('price_per_session', 12, 2)
                    ->default(0);

                $table->timestamps();

                $table->index(['booking_package_id', 'weekday']);
                $table->index(['court_id', 'weekday']);
                $table->index(['time_slot_id']);

                $table->unique(
                    ['booking_package_id', 'weekday', 'court_id', 'time_slot_id'],
                    'booking_package_sessions_unique'
                );
            });
        }

        if (Schema::hasTable('transactions')) {
            if (Schema::hasColumn('transactions', 'booking_id')) {
                try {
                    Schema::table('transactions', function (Blueprint $table) {
                        $table->dropForeign(['booking_id']);
                    });
                } catch (Throwable $exception) {
                    // Có thể foreign key đã bị drop hoặc tên foreign key khác.
                }

                try {
                    DB::statement('ALTER TABLE transactions MODIFY booking_id BIGINT UNSIGNED NULL');
                } catch (Throwable $exception) {
                    // Một số DB local có thể đã nullable sẵn.
                }

                try {
                    Schema::table('transactions', function (Blueprint $table) {
                        $table->foreign('booking_id')
                            ->references('id')
                            ->on('bookings')
                            ->cascadeOnDelete();
                    });
                } catch (Throwable $exception) {
                    // Tránh lỗi nếu foreign key đã tồn tại.
                }
            }

            Schema::table('transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('transactions', 'booking_package_id')) {
                    $table->foreignId('booking_package_id')
                        ->nullable()
                        ->after('booking_id')
                        ->constrained('booking_packages')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'booking_package_id')) {
                    $table->dropConstrainedForeignId('booking_package_id');
                }

                if (Schema::hasColumn('transactions', 'booking_id')) {
                    try {
                        $table->dropForeign(['booking_id']);
                    } catch (Throwable $exception) {
                        //
                    }
                }
            });

            if (Schema::hasColumn('transactions', 'booking_id')) {
                try {
                    DB::statement('ALTER TABLE transactions MODIFY booking_id BIGINT UNSIGNED NOT NULL');
                } catch (Throwable $exception) {
                    //
                }

                try {
                    Schema::table('transactions', function (Blueprint $table) {
                        $table->foreign('booking_id')
                            ->references('id')
                            ->on('bookings')
                            ->cascadeOnDelete();
                    });
                } catch (Throwable $exception) {
                    //
                }
            }   
        }

        Schema::dropIfExists('booking_package_sessions');
    }
};