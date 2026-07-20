<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_trading_videos', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // Optional — falls back to the product name when left blank.
            $table->string('title')->nullable();
            $table->string('url', 1000);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
            $table->auditColumns();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_trading_videos');
    }
};
