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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('ION CRM');
            $table->string('default_language')->default('ro');
            $table->string('timezone')->default('Europe/Bucharest');
            $table->string('date_format')->default('d/m/Y');
            $table->string('app_logo')->nullable();
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable()->default(587);
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable()->default('tls');
            $table->string('smtp_from_name')->nullable();
            $table->string('smtp_from_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

