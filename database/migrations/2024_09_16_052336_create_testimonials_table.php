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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('customer_id');
            $table->integer('rating');
            $table->text('testimonial');
            $table->boolean('is_filled')->default(false);;
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('customer_id')
                ->references('uuid')
                ->on('customers')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
