<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->string('code', 100);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('quantity')->default(0);      // planned units
            $table->unsignedInteger('qr_generated')->default(0);  // QR codes generated so far
            $table->unsignedInteger('reward_points')->nullable(); // overrides product points when set
            $table->string('status', 20)->default('draft')->index(); // draft|generating|active|closed

            $table->timestamps();
            $table->auditColumns();

            $table->unique(['product_id', 'code']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
