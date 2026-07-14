<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemption_requests', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('reference', 32)->unique();           // human-friendly request number
            $table->decimal('amount', 14, 2);                    // points/amount requested
            $table->string('method', 20)->default('upi');        // upi | bank | gift | cash
            $table->json('payout_details')->nullable();          // upi_id / account / ifsc / name
            $table->string('note')->nullable();                  // requester note

            $table->string('status', 20)->default('pending')->index(); // pending | approved | rejected

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('review_note')->nullable();           // admin remark on approval
            $table->string('attachment_path')->nullable();       // proof uploaded by admin on approval
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();

            $table->timestamps();
            $table->auditColumns();

            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemption_requests');
    }
};
