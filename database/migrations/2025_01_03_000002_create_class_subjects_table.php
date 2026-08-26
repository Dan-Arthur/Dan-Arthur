<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->noActionOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->noActionOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->boolean('is_compulsory')->default(true);
            $table->smallInteger('periods_per_week')->default(5);
            $table->timestamps();

            $table->unique(['class_id', 'subject_id', 'academic_year_id']);
            $table->index(['class_id', 'academic_year_id']);
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subjects');
    }
};

