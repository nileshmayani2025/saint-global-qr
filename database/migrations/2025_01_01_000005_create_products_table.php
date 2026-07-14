<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->string('sku', 100);
            $table->string('hsn_code', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 30)->default('piece'); // piece|box|kg|litre|...
            $table->decimal('mrp', 12, 2)->default(0);
            $table->unsignedInteger('reward_points')->default(0);
            $table->string('image_path')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->auditColumns();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'status']);
            $table->index('brand_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
