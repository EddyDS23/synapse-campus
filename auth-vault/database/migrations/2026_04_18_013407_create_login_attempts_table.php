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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email')->nullable(false);
            $table->string('ip_address')->nullable(false);
            $table->string('reason');
            $table->timestamp('failed_at')->nullable(false);
            $table->index(['email','failed_at']);
        });
    }

    /**
     *  Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};


