<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('created_by');
            $table->string('contract_code')->unique();
            $table->string('title');
            $table->longText('content');
            $table->decimal('commission_rate', 5, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'terminated'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
