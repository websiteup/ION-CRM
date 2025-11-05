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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('phone');
            $table->boolean('notification_email_enabled')->default(true)->after('telegram_chat_id');
            $table->boolean('notification_telegram_enabled')->default(false)->after('notification_email_enabled');
            $table->boolean('notification_task_created')->default(true)->after('notification_telegram_enabled');
            $table->boolean('notification_task_assigned')->default(true)->after('notification_task_created');
            $table->boolean('notification_task_updated')->default(true)->after('notification_task_assigned');
            $table->boolean('notification_task_deadline')->default(true)->after('notification_task_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_chat_id',
                'notification_email_enabled',
                'notification_telegram_enabled',
                'notification_task_created',
                'notification_task_assigned',
                'notification_task_updated',
                'notification_task_deadline',
            ]);
        });
    }
};

