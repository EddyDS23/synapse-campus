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
        Schema::create('fines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('loan_id')->constrained('loans','id')->cascadeOnDelete();
            $table->string('borrower_id')->nullable(false);
            $table->float('amount',2);
            $table->timestamp('paid_at')->nullable();
            $table->enum('status',['pending','paid'])->default('pending');
            $table->boolean('debt_notified')->default(false);
            $table->boolean('paid_notified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
