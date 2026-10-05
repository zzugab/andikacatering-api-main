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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('order_id');
            $table->time('akad_start')->nullable();
            $table->time('akad_end')->nullable();
            $table->time('resepsi_start')->nullable();
            $table->time('resepsi_end')->nullable();
            $table->text('nuance')->nullable();
            $table->integer('general_buffet')->nullable();
            $table->integer('vip_buffet')->nullable();
            $table->integer('vip_table')->nullable();
            $table->integer('wedding_food_table')->nullable();
            $table->integer('akad_table')->nullable();
            $table->integer('reception_table')->nullable();
            $table->text('for_naib')->nullable();
            $table->enum('ayam_bekakak_nasi_punar', ['iya', 'tidak'])->default('tidak');
            $table->enum('mica_for_besan', ['iya', 'tidak'])->default('tidak');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('order_id')
                ->references('uuid')
                ->on('orders_customers')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
