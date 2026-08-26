<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->noActionOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->noActionOnDelete();
            $table->string('application_number', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('religion', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('previous_school', 200)->nullable();
            $table->string('applying_for_class', 100)->nullable();
            $table->foreignId('applied_class_id')->nullable()->constrained('school_classes')->noActionOnDelete();
            $table->string('status', 30)->default('draft');
            // draft, submitted, under_review, interview, entrance_exam, accepted, rejected, waitlisted, enrolled
            $table->date('application_date')->nullable();
            $table->date('interview_date')->nullable();
            $table->date('exam_date')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('guardian_info')->nullable(); // JSON
            $table->text('documents')->nullable(); // JSON list
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'academic_year_id']);
            $table->index('application_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};

