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
        Schema::create('sections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('subject_id')->constrained('subjects','id')->cascadeOnDelete();
            $table->foreignUlid('academic_period_id')->constrained('academic_periods','id')->cascadeOnDelete();
            $table->ulid('teacher_id');
            $table->string('group_label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
