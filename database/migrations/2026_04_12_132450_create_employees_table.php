<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('pob', 50);
            $table->date('dob');
            $table->foreignId('departement_id')->constrained('departements');
            $table->foreignId('position_id')->constrained('positions');
            $table->text('address')->nullable();
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('province_id')->constrained('provinces');
            $table->foreignId('district_id')->constrained('districts');
            $table->foreignId('sub_district_id')->constrained('sub_districts');
            $table->foreignId('village_id')->constrained('villages');
            $table->string('postal_code', 10)->nullable();
            $table->string('phone_no', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('identity_no', 50)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->decimal('sallary', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('dependants')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
