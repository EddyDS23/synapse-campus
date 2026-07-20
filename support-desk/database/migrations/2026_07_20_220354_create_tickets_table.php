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
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('requester_id')->nullable(false);
            $table->string('assignee_id')->nullable();
            $table->foreignUlid('category_id')->constrained('categories','id')->restrictOnDelete();
            $table->enum('priority',['low','medium','high','urgent'])->default('medium');
            $table->enum('status',['open','in_progress','on_hold','resolved','closed'])->default('open');
            $table->string('title')->nullable(false);
            $table->string('description')->nullable(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
