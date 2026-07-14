<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('qr_code_id')->constrained('qr_codes')->cascadeOnDelete();
            $table->foreignId('scan_id')->nullable()->constrained('scans')->nullOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('reward_points')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 20)->default('verified')->index(); // verified|rewarded
            $table->timestamp('verified_at');

            $table->timestamps();
            $table->auditColumns();

            // A QR code can only produce one successful verification.
            $table->unique('qr_code_id');
            $table->index(['company_id', 'product_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
