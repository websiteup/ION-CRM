<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('google_calendar_client_id')->nullable()->after('telegram_bot_token');
            $table->string('google_calendar_client_secret')->nullable()->after('google_calendar_client_id');
            $table->string('google_calendar_redirect_uri')->nullable()->after('google_calendar_client_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['google_calendar_client_id', 'google_calendar_client_secret', 'google_calendar_redirect_uri']);
        });
    }
};
