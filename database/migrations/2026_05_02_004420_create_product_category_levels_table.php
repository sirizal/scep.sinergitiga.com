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
        Schema::create('product_category_levels', function (Blueprint $table) {
            $table->id();
            $table->text('category_0')->nullable();
            $table->text('category_1')->nullable();
            $table->text('category_2')->nullable();
            $table->text('category_3')->nullable();
            $table->text('category_4')->nullable();
            $table->text('category_5')->nullable();
            $table->text('category_6')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_levels');
    }
};
