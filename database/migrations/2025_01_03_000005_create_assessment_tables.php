<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 100);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('grade_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scale_id')->constrained('grading_scales')->noActionOnDelete();
            $table->string('grade', 10); // A+, A, B, C, etc.
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->string('remark', 100)->nullable(); // Distinction, Merit, Pass, Fail
            $table->smallInteger('gpa_point')->default(0); // 4, 3, 2, 1, 0
            $table->timestamps();

            $table->index('grading_scale_id');
        });

        Schema::create('assessment_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->noActionOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->string('name', 100); // e.g. "Standard Configuration"
            $table->text('components')->nullable(); // JSON: [{name, weight, max_score}, ...]
            $table->decimal('total_score', 5, 2)->default(100.00);
            $table->foreignId('grading_scale_id')->nullable()->constrained('grading_scales')->noActionOnDelete();
            $table->boolean('show_position')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('term_id')->constrained('terms');
            $table->string('title', 200);
            $table->string('type', 50); // assignment, class_test, quiz, ca, mid_term, exam, mock
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->decimal('weight', 5, 2)->nullable(); // percentage weight in final score
            $table->date('assessment_date')->nullable();
            $table->date('submission_deadline')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft'); // draft, published, in_progress, completed, cancelled
            $table->boolean('marks_entered')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'term_id']);
            $table->index(['teacher_id', 'term_id']);
            $table->index('school_id');
        });

        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->noActionOnDelete();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_exempt')->default(false);
            $table->string('remarks', 500)->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_configurations');
        Schema::dropIfExists('grade_bands');
        Schema::dropIfExists('grading_scales');
    }
};

