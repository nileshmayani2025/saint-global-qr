<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            // qr_code_id is null when the scanned code is unknown / forged.
            $table->foreignId('qr_code_id')->nullable()->constrained('qr_codes')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('raw_code', 191)->index();
            $table->string('result', 20)->index(); // valid|duplicate|invalid|blocked|expired

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('device', 30)->nullable();
            $table->string('device_id', 100)->nullable()->index();

            $table->boolean('is_fraud_suspected')->default(false)->index();
            $table->json('fraud_reasons')->nullable();

            $table->timestamps();
            $table->auditColumns();

            $table->index(['qr_code_id', 'result']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
