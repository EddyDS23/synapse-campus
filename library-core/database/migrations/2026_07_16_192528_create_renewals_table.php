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
        Schema::create('renewals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('loan_id')->constrained('loans','id')->cascadeOnDelete();
            $table->string('borrower_id')->nullable(false);
            $table->timestamp('previous_due_at')->nullable(false);
            $table->timestamp('new_due_at')->nullable(false);
            $table->timestamp('renewed_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renewals');
    }
};
