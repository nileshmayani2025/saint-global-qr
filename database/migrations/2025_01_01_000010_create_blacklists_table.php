<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_lists', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('list_type', 10)->index();  // blacklist|whitelist
            $table->string('entry_type', 20)->index(); // device|ip|user|code
            $table->string('value', 191);
            $table->string('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
            $table->auditColumns();

            $table->unique(['list_type', 'entry_type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_lists');
    }
};
