<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Accounts are created from a mobile number + OTP now, so an email is
        // optional. The unique index stays as-is — MySQL permits many NULLs in
        // a unique column, so email-less accounts don't collide.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Admin approval is gone: every account can scan the moment it exists.
        // Clear the backlog so no legacy row is stranded as "pending".
        DB::table('users')->whereNull('approved_at')->update(['approved_at' => now()]);
    }

    public function down(): void
    {
        // Accounts registered by mobile have no email; give them a placeholder
        // so the NOT NULL constraint can be restored.
        DB::table('users')->whereNull('email')->update([
            'email' => DB::raw("CONCAT('user-', id, '@placeholder.invalid')"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
