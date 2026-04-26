<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('zone', 50)->nullable();
            $table->string('aisle', 50)->nullable();
            $table->string('rack', 50)->nullable();
            $table->string('level', 50)->nullable();
            $table->string('bin', 50)->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->decimal('max_weight', 14, 2)->default(0);
            $table->decimal('max_volume', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('type');
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
