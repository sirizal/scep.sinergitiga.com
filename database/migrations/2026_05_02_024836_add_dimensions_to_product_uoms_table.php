<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_uoms', function (Blueprint $table) {
            $table->decimal('width', 10, 2)->default(0);
            $table->decimal('deep', 10, 2)->default(0);
            $table->decimal('height', 10, 2)->default(0);
            $table->decimal('volume', 10, 2)->default(0);
            $table->decimal('weight', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('product_uoms', function (Blueprint $table) {
            $table->dropColumn(['width', 'deep', 'height', 'volume', 'weight']);
        });
    }
};
