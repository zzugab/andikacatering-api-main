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
        Schema::create('event_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('event_transfer_id');
            $table->uuid('item_id');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('event_transfer_id')
                ->references('uuid')
                ->on('event_transfers')
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
        Schema::dropIfExists('event_transfer_items');
    }
};
