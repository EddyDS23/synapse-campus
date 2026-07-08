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
        Schema::create('career_subjects', function (Blueprint $table) {
            $table->foreignUlid('career_id')->constrained('careers','id')->cascadeOnDelete();
            $table->foreignUlid('subject_id')->constrained('subjects','id')->cascadeOnDelete();
            $table->integer('semester')->nullable(false);
            $table->timestamps();
            $table->primary(['career_id','subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_subjects');
    }
};
