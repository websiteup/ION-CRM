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
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->string('to')->index();
            $table->string('from')->nullable();
            $table->string('from_name')->nullable();
            $table->string('subject');
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status')->default('sent'); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('related_type')->nullable(); // Proposal, Invoice, etc.
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['related_type', 'related_id']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};

