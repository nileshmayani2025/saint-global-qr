<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('log_name')->default('default')->index();
            $table->string('event', 50)->index();               // created|updated|deleted|restored|login|logout|scan|verify|...
            $table->string('description')->nullable();

            // Polymorphic subject (the model the action was performed on).
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Who did it.
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();

            // Change payload: {"old": {...}, "attributes": {...}}.
            $table->json('properties')->nullable();

            // Request context.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('device', 30)->nullable();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
