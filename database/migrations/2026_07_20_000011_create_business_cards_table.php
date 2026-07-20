<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_cards', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            // One card per registered person.
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // The public link segment. Long and random rather than derived from
            // the name: the card exposes a mobile number, so the URL must not
            // be guessable or enumerable. Regenerating it revokes old shares.
            $table->string('slug', 32)->unique();

            $table->string('business_name')->nullable();
            $table->string('tagline', 255)->nullable();
            // Separate from the login number — many people give out a different
            // number for WhatsApp.
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();

            // Lets someone take their card offline without deleting it.
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
            $table->auditColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_cards');
    }
};
