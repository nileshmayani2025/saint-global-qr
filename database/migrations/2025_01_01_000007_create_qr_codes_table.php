<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // denormalised for fast verify

            $table->string('code', 40)->unique();          // secure random token, printed on product
            $table->unsignedBigInteger('serial');          // running serial within the batch
            $table->string('payload_hash', 64);            // HMAC signature of the code
            $table->string('image_path')->nullable();
            $table->string('short_url')->nullable();
            $table->unsignedInteger('reward_points')->default(0);

            $table->string('status', 20)->default('generated')->index(); // generated|printed|active|verified|blocked

            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('first_scanned_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();
            $table->auditColumns();

            $table->unique(['batch_id', 'serial']);
            $table->index(['company_id', 'status']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
