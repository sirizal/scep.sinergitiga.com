<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('company_type_id')->constrained('company_types');
            $table->text('address')->nullable();
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('province_id')->constrained('provinces');
            $table->foreignId('district_id')->constrained('districts');
            $table->foreignId('sub_district_id')->constrained('sub_districts');
            $table->foreignId('village_id')->constrained('villages');
            $table->string('postal_code', 10)->nullable();
            $table->string('phone_no', 20)->nullable();
            $table->string('fax_no', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('contact_name', 50)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('bussiness_license_id', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
