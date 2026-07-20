<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->publicId();

            $table->string('title');
            $table->text('body');
            $table->string('image_path')->nullable();
            // Where tapping the notification takes the user. Stored as entered
            // (relative or absolute) and resolved at send time.
            $table->string('action_url')->nullable();

            // all | role | users | location
            $table->string('audience', 20)->default('all')->index();
            $table->json('audience_filters')->nullable();

            // draft | queued | sending | sent | failed
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();
            $table->auditColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
