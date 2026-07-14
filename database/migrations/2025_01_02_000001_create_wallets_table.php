<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('type', 20)->index();          // reward | cashback
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('lifetime_credited', 14, 2)->default(0);
            $table->decimal('lifetime_debited', 14, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('active')->index(); // active | frozen

            $table->timestamps();
            $table->auditColumns();

            // One wallet per user per type.
            $table->unique(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
