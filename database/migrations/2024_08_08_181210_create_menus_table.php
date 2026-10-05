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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->bigInteger('price');
            $table->uuid('category_id');
            $table->enum('type', ['Buffet', 'Foodstall', 'Nasi Box'])->default('Nasi Box');
            $table->text('description');
            $table->uuid('image_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('category_id')
                ->references('uuid')
                ->on('menu_categories')
                ->onUpdate('cascade')->onDelete('no action');
            $table->foreign('image_id')
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
        Schema::dropIfExists('menus');
    }
};
