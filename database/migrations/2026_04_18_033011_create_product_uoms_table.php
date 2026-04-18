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
        Schema::create('product_uoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('uom_id')->constrained('uoms');
            $table->foreignId('convert_uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->decimal('conversion_qty', 10, 2)->default(1);
            $table->timestamps();
            $table->unique(['product_id', 'uom_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_uoms');
    }
};
