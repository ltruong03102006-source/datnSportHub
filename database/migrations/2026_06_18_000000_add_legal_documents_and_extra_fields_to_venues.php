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
        // Alter venues table: change status to string and add extra fields
        Schema::table('venues', function (Blueprint $table) {
            if (!Schema::hasColumn('venues', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('venues', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('venues', 'open_hours')) {
                $table->string('open_hours')->nullable();
            }
            if (!Schema::hasColumn('venues', 'close_hours')) {
                $table->string('close_hours')->nullable();
            }
            if (!Schema::hasColumn('venues', 'google_maps_address')) {
                $table->text('google_maps_address')->nullable();
            }
        });

        // Safe way to modify enum to string in MySQL - only if status is still ENUM
        try {
            DB::statement("ALTER TABLE venues MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Status column might already be VARCHAR, skip
        }

        // Create venue_legal_documents table
        Schema::create('venue_legal_documents', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('venue_id')
                ->constrained('venues')
                ->cascadeOnDelete();

            $table->string('owner_name');
            $table->string('citizen_id');
            $table->string('business_license_number');
            $table->string('address');
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_holder');

            $table->string('citizen_front_image');
            $table->string('citizen_back_image');
            $table->string('business_license_file');
            $table->string('rental_contract_file')->nullable();
            $table->string('land_certificate_file')->nullable();

            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('reject_reason')->nullable();
            
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
                
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_legal_documents');

        if (Schema::hasColumn('venues', 'phone') || Schema::hasColumn('venues', 'email') || Schema::hasColumn('venues', 'open_hours') || Schema::hasColumn('venues', 'close_hours') || Schema::hasColumn('venues', 'google_maps_address')) {
            Schema::table('venues', function (Blueprint $table) {
                if (Schema::hasColumn('venues', 'phone')) {
                    $table->dropColumn('phone');
                }
                if (Schema::hasColumn('venues', 'email')) {
                    $table->dropColumn('email');
                }
                if (Schema::hasColumn('venues', 'open_hours')) {
                    $table->dropColumn('open_hours');
                }
                if (Schema::hasColumn('venues', 'close_hours')) {
                    $table->dropColumn('close_hours');
                }
                if (Schema::hasColumn('venues', 'google_maps_address')) {
                    $table->dropColumn('google_maps_address');
                }
            });
        }

        if (Schema::hasColumn('venues', 'status')) {
            try {
                DB::statement("ALTER TABLE venues MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'pending'");
            } catch (\Exception $e) {
                // Ignore if status is already a string or cannot be altered.
            }
        }
    }
};
