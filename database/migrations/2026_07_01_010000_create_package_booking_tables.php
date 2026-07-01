<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            if (! Schema::hasColumn('venues', 'allow_package_booking')) {
                $table->boolean('allow_package_booking')
                    ->default(false)
                    ->after('status');
            }
        });

        if (! Schema::hasTable('venue_packages')) {
            Schema::create('venue_packages', function (Blueprint $table) {
                $table->id();

                $table->foreignId('venue_id')
                    ->constrained('venues')
                    ->cascadeOnDelete();

                $table->string('name');

                // week = gói theo tuần, month = gói theo tháng
                $table->enum('type', ['week', 'month']);

                // Nếu type = week: duration là số tuần
                // Nếu type = month: duration là số tháng
                $table->unsignedSmallInteger('duration');

                // Khách được chọn tối đa bao nhiêu buổi/tuần, từ 1 đến 7
                // Nếu = 7 nghĩa là có thể chơi mỗi ngày
                $table->unsignedTinyInteger('max_sessions_per_week')
                    ->default(7);

                $table->decimal('discount_percent', 5, 2)
                    ->default(0);

                // Số khách tối đa được đăng ký gói này
                // NULL = không giới hạn
                $table->unsignedInteger('max_subscribers')
                    ->nullable();

                $table->enum('status', ['active', 'inactive'])
                    ->default('active');

                $table->timestamps();

                $table->index(['venue_id', 'status']);
                $table->index(['venue_id', 'type']);
            });
        }

        if (! Schema::hasTable('booking_packages')) {
            Schema::create('booking_packages', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId('venue_id')
                    ->constrained('venues')
                    ->cascadeOnDelete();

                $table->foreignId('package_id')
                    ->constrained('venue_packages')
                    ->cascadeOnDelete();

                $table->date('start_date');
                $table->date('end_date');

                // Số buổi/tuần khách đã chọn khi mua gói
                $table->unsignedTinyInteger('weekly_sessions')
                    ->default(1);

                // Tổng số buổi được sinh ra từ gói
                // Ví dụ gói tháng chọn 7 buổi/tuần:
                // tháng 30 ngày = 30 buổi, tháng 31 ngày = 31 buổi
                $table->unsignedSmallInteger('total_sessions')
                    ->default(0);

                // Số buổi đã chơi
                $table->unsignedSmallInteger('used_sessions')
                    ->default(0);

                // Giá gốc trước giảm
                $table->decimal('total_amount', 12, 2)
                    ->default(0);

                // Số tiền giảm
                $table->decimal('discount_amount', 12, 2)
                    ->default(0);

                // Số tiền cuối cùng khách cần thanh toán
                $table->decimal('final_amount', 12, 2)
                    ->default(0);

                // pending_payment: vừa đăng ký, chờ thanh toán
                // active: đã thanh toán, đã sinh booking
                // paused: tạm dừng gói
                // completed: đã dùng hết gói
                // cancelled: đã hủy
                // expired: hết hạn
                $table->enum('status', [
                    'pending_payment',
                    'active',
                    'paused',
                    'completed',
                    'cancelled',
                    'expired',
                ])->default('pending_payment');

                $table->timestamp('paid_at')->nullable();
                $table->timestamp('paused_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['venue_id', 'status']);
                $table->index(['package_id', 'status']);
                $table->index(['start_date', 'end_date']);
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'booking_package_id')) {
                $table->foreignId('booking_package_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('booking_packages')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'booking_package_id')) {
                $table->dropConstrainedForeignId('booking_package_id');
            }
        });

        Schema::dropIfExists('booking_packages');
        Schema::dropIfExists('venue_packages');

        Schema::table('venues', function (Blueprint $table) {
            if (Schema::hasColumn('venues', 'allow_package_booking')) {
                $table->dropColumn('allow_package_booking');
            }
        });
    }
};