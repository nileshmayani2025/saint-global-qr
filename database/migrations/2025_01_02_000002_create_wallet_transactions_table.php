<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('direction', 10)->index();       // credit | debit
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reason', 40)->index();           // verification_reward | cashback | redemption | adjustment | ...

            // Polymorphic source (e.g. a VerificationLog or Redemption).
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->string('reference', 64)->unique();
            $table->string('description')->nullable();
            $table->string('status', 20)->default('completed')->index(); // completed | pending | failed | reversed
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->auditColumns();

            $table->index(['wallet_id', 'created_at']);
            $table->index(['user_id', 'direction']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
