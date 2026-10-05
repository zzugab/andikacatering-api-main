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
        Schema::create('orders_customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('order_name');
            $table->uuid('customer_id');
            $table->text('location')->nullable();
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->integer('portion')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['booking', 'confirmed', 'preparing', 'ready_for_delivery', 'delivered', 'event_in_progress', 'event_completed', 'refund', 'case_close'])->default('booking');
            $table->uuid('field_coordinator_id')->nullable();
            $table->text('handover_of_leftovers')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('customer_id')
                ->references('uuid')
                ->on('customers')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('field_coordinator_id')
                ->references('uuid')
                ->on('users')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_customers');
    }
};
