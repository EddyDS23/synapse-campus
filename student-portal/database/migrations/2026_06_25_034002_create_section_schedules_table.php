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
        Schema::create('section_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('section_id')->constrained('sections','id')->cascadeOnDelete();
            $table->enum('day_of_week',['monday','tuesday','wednesday','thursday','friday','saturday','sunday'])->nullable(false);
            $table->time('start_time')->nullable(false);
            $table->time('end_time')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_schedules');
    }
};
