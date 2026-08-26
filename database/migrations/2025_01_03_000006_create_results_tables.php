<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('term_id')->constrained('terms');
            $table->decimal('total_score', 6, 2)->nullable();
            $table->decimal('average_score', 5, 2)->nullable();
            $table->integer('position')->nullable();
            $table->integer('class_size')->nullable();
            $table->integer('subjects_offered')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->string('overall_grade', 10)->nullable();
            $table->string('overall_remark', 100)->nullable();
            $table->text('class_teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            $table->string('status', 30)->default('draft'); // draft, pending_approval, approved, published, locked
            $table->foreignId('approved_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
            $table->index(['class_id', 'term_id']);
            $table->index(['school_id', 'term_id']);
        });

        Schema::create('result_subject_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('results')->noActionOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('student_id')->constrained('students');
            $table->decimal('ca_score', 5, 2)->nullable(); // continuous assessment total
            $table->decimal('exam_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->string('remark', 100)->nullable();
            $table->integer('position')->nullable();
            $table->decimal('class_average', 5, 2)->nullable();
            $table->decimal('highest_score', 5, 2)->nullable();
            $table->decimal('lowest_score', 5, 2)->nullable();
            $table->text('teacher_comment')->nullable();
            $table->timestamps();

            $table->unique(['result_id', 'subject_id']);
            $table->index(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_subject_scores');
        Schema::dropIfExists('results');
    }
};

