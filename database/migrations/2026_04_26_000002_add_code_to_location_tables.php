<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('id');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('id');
        });

        Schema::table('sub_districts', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('id');
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('sub_districts', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
