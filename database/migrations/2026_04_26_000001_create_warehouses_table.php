<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('type', 50);
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sub_district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
