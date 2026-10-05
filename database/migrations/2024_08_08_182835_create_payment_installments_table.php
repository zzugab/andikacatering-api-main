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
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('payment_id');
            $table->integer('instalment');
            $table->uuid('payment_bank_id');
            $table->enum('type', ['payment installments', 'final payment'])->default('payment installments');
            $table->bigInteger('amount');
            $table->date('payment_datelines');
            $table->uuid('payment_image_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('payment_id')
                ->references('uuid')
                ->on('payments')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('payment_bank_id')
                ->references('uuid')
                ->on('banks')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('payment_image_id')
                ->references('uuid')
                ->on('images')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};
