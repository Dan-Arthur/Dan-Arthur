<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->noActionOnDelete();
            $table->foreignId('student_id')->constrained()->noActionOnDelete();
            $table->foreignId('scholarship_id')->constrained()->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained()->noActionOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->constrained('users')->noActionOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'scholarship_id', 'academic_year_id'], 'unique_student_scholarship_year');
            $table->index(['school_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scholarships');
    }
};
