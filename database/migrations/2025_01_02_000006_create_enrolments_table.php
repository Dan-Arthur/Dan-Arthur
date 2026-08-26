<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->string('roll_number', 20)->nullable();
            $table->string('status', 30)->default('active'); // active, withdrawn, transferred, graduated
            $table->date('enrolled_date');
            $table->date('exit_date')->nullable();
            $table->text('exit_reason')->nullable();
            $table->boolean('is_promoted')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'class_id', 'academic_year_id']);
            $table->index(['class_id', 'academic_year_id']);
            $table->index(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolments');
    }
};

