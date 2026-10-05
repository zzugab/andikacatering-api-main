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
        Schema::create('event_logistics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('order_id');
            $table->uuid('item_id');
            $table->integer('quantity');
            $table->integer('quantity_return');
            $table->integer('broken_items');
            $table->integer('in_client_hands');
            $table->text('notes');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('order_id')
                ->references('uuid')
                ->on('orders_customers')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('item_id')
                ->references('uuid')
                ->on('inventory_items')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logistics');
    }
};
