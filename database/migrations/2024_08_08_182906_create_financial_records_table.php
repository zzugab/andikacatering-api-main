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
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('order_id');
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->date('date');
            $table->bigInteger('amount');
            $table->text('description')->nullable();
            $table->uuid('record_image_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('order_id')
                ->references('uuid')
                ->on('orders_customers')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('record_image_id')
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
        Schema::dropIfExists('financial_records');
    }
};
