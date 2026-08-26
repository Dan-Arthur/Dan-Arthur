<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->noActionOnDelete();
            $table->string('title', 150);
            $table->string('code', 30)->nullable();
            $table->string('type', 30)->default('teaching'); // teaching, non_teaching, management
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->noActionOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->noActionOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->noActionOnDelete();
            $table->string('employee_number', 30)->unique();
            $table->string('title', 20)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('alt_phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('qualification', 200)->nullable();
            $table->string('specialisation', 200)->nullable();
            $table->integer('years_experience')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('employment_type', 30)->default('full_time'); // full_time, part_time, contract
            $table->string('status', 30)->default('active'); // active, inactive, resigned, terminated, retired
            $table->date('exit_date')->nullable();
            $table->text('exit_reason')->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_sort_code', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index('employee_number');
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 100);
            $table->smallInteger('days_allowed')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->noActionOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->foreignId('approved_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, cancelled
            $table->timestamp('actioned_at')->nullable();
            $table->text('action_note')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
    }
};

