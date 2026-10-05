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
        Schema::create('order_customizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('order_id');
            $table->uuid('menu_id');
            $table->integer('portion');
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('order_id')
                ->references('uuid')
                ->on('orders_customers')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('menu_id')
                ->references('uuid')
                ->on('menus')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_customizations');
    }
};
