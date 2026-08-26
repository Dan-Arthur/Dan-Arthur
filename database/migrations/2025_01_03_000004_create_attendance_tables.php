<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present'); // present, absent, late, excused
            $table->time('arrival_time')->nullable();
            $table->string('reason', 500)->nullable();
            $table->foreignId('taken_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('method', 30)->default('manual'); // manual, biometric, rfid, qr_code
            $table->timestamps();

            $table->unique(['student_id', 'date', 'class_id']);
            $table->index(['class_id', 'date']);
            $table->index(['school_id', 'date']);
            $table->index(['student_id', 'academic_year_id']);
        });

        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('user_id')->constrained('users')->noActionOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present'); // present, absent, late, leave, holiday
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('reason', 500)->nullable();
            $table->string('method', 30)->default('manual');
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['school_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('student_attendance');
    }
};

