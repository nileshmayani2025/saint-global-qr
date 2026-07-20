<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('state_id')->constrained('states')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
            $table->auditColumns();

            $table->unique(['state_id', 'name']);
            $table->index(['state_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
