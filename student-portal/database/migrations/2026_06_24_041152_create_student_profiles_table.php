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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->ulid('student_id')->primary();
            $table->foreignUlid('career_id')->constrained('careers','id')->cascadeOnDelete();
            $table->string('student_number')->nullable(false)->unique();
            $table->integer('current_semester')->nullable(false);
            $table->enum('status',['active','inactive','graduated']);
            $table->boolean('has_debt')->default(false);
            $table->tinyInteger('debt_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
