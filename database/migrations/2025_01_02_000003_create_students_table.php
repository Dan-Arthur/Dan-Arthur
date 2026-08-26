<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->noActionOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('student_number', 30)->unique();
            $table->string('admission_number', 30)->nullable();
            $table->string('title', 20)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('state_of_origin', 100)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('genotype', 10)->nullable();
            $table->text('medical_conditions')->nullable();
            $table->string('allergies', 500)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('photo')->nullable();
            $table->string('house', 100)->nullable(); // school house e.g. Red, Blue
            $table->string('previous_school', 200)->nullable();
            $table->string('previous_class', 100)->nullable();
            $table->date('admission_date')->nullable();
            $table->string('status', 30)->default('active');
            // active, inactive, graduated, transferred, withdrawn, suspended, expelled
            $table->date('status_date')->nullable();
            $table->text('status_reason')->nullable();
            $table->foreignId('current_class_id')->nullable()->constrained('school_classes')->noActionOnDelete();
            $table->foreignId('current_academic_year_id')->nullable()->constrained('academic_years')->noActionOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'current_class_id']);
            $table->index('student_number');
            $table->index('admission_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

