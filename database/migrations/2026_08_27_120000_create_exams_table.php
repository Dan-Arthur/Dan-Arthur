<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->noActionOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->noActionOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->noActionOnDelete();
            $table->string('title', 200);
            $table->date('exam_date');
            $table->time('start_time')->nullable();
            $table->smallInteger('duration_minutes')->nullable();
            $table->string('venue', 200)->nullable();
            $table->string('invigilator', 200)->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'exam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
