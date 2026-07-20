<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            // Geography is global reference data shared by every company, so
            // there is deliberately no company_id here.
            $table->string('name')->unique();
            $table->string('iso2', 2)->nullable()->unique();
            $table->string('iso3', 3)->nullable()->unique();
            $table->string('phone_code', 8)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
            $table->auditColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
