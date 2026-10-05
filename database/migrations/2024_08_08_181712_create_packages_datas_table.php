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
        Schema::create('packages_data', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('packages_id');
            $table->uuid('category_id');
            $table->integer('quantity_percent');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('packages_id')
                ->references('uuid')
                ->on('packages')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('category_id')
                ->references('uuid')
                ->on('menu_categories')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages_data');
    }
};
