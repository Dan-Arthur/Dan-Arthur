<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('run_by')->constrained('users')->noActionOnDelete();
            $table->string('title', 200);
            $table->unsignedTinyInteger('month');   // 1-12
            $table->unsignedSmallInteger('year');
            $table->string('status', 20)->default('draft'); // draft | approved | paid
            $table->decimal('total_gross', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('total_net', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->noActionOnDelete();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->text('allowances')->nullable();    // JSON: {transport:x, housing:y, …}
            $table->text('deductions')->nullable();    // JSON: {ssnit:x, income_tax:y, …}
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft | paid
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
    }
};
