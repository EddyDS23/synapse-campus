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
        Schema::create('loans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('book_id')->constrained('books','id')->cascadeOnDelete();
            $table->string('borrower_id')->nullable(false);
            $table->timestamp('borrowed_at')->nullable(false);
            $table->timestamp('due_at')->nullable(false);
            $table->timestamp('returned_at')->nullable();
            $table->enum('status',['active','returned'])->default('active');
            $table->integer('renew_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
