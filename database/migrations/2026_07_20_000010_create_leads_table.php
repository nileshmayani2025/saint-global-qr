<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('name');
            $table->string('phone', 20)->index();

            // Geography reuses the global masters, all optional — a lead is
            // often captured with nothing but a name and a number.
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->text('address')->nullable();
            $table->text('remark')->nullable();

            // new | contacted | qualified | converted | lost
            $table->string('status', 20)->default('new')->index();

            $table->timestamps();
            $table->auditColumns();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
