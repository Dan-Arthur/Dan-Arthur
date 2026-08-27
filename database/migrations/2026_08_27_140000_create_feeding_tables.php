<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->noActionOnDelete();
            $table->foreignId('student_id')->constrained()->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained()->noActionOnDelete();
            $table->date('enrolled_date');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'academic_year_id']);
        });

        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->noActionOnDelete();
            $table->foreignId('student_id')->constrained()->noActionOnDelete();
            $table->date('record_date');
            $table->boolean('fed')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'record_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_records');
        Schema::dropIfExists('feeding_enrollments');
    }
};
