<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->noActionOnDelete();
            $table->string('name', 50); // e.g. "First Term", "Semester 1"
            $table->smallInteger('sequence')->default(1); // 1, 2, 3
            $table->date('start_date');
            $table->date('end_date');
            $table->date('result_release_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('status', 20)->default('upcoming'); // upcoming, active, completed
            $table->timestamps();

            $table->unique(['academic_year_id', 'sequence']);
            $table->index(['academic_year_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};

