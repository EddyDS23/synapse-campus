<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('owner_id')->index();
            $table->string('original_name');
            $table->string('stored_name')->unique();
            $table->string('extension');
            $table->string('mime_type');
            $table->integer('size');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
