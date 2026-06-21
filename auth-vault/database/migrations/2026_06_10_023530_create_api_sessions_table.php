<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users','id')->cascadeOnDelete();
            $table->string('jti')->unique();
            $table->string('ip_address')->nullable(false);
            $table->string('device')->nullable(false);
            $table->timestamp('expires_at');
            $table->string('refresh_token')->unique();
            $table->timestamp('refresh_expires_at');
            $table->timestamp('last_use_at')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_sessions');
    }
};
