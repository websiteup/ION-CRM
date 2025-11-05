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
        Schema::create('proposal_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->onDelete('cascade');
            $table->enum('event_type', ['created', 'updated', 'sent', 'accepted', 'rejected', 'expired', 'duplicated'])->default('updated');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // Schimbările făcute (old_value => new_value)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('event_date');
            $table->timestamps();
            
            $table->index('proposal_id');
            $table->index('event_type');
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_history');
    }
};

