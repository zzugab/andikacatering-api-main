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
        Schema::create('event_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->enum('status', ['from warehouse', 'event to event', 'to warehouse']);
            $table->uuid('order_id_from')->nullable();
            $table->uuid('order_id_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('order_id_from')
                ->references('uuid')
                ->on('orders_customers')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('order_id_to')
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
        Schema::dropIfExists('event_transfers');
    }
};
