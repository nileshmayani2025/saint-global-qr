<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Highest trading-video id this user has already been shown. The
            // welcome popup reappears whenever a newer video is published, so
            // this is a watermark rather than a boolean "seen" flag.
            $table->foreignId('last_seen_trading_video_id')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_trading_video_id');
        });
    }
};
