<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->noActionOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->noActionOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->noActionOnDelete();
            $table->string('name', 200);
            $table->boolean('applies_to_all_classes')->default(false);
            $table->string('student_category', 50)->nullable(); // all, boarder, day, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
        });

        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->noActionOnDelete();
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->noActionOnDelete();
            $table->string('name', 200);
            $table->decimal('amount', 12, 2);
            $table->boolean('is_mandatory')->default(true);
            $table->text('notes')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('fee_structure_id');
        });

        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 200);
            $table->string('type', 30)->default('percentage'); // percentage, fixed
            $table->decimal('value', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->noActionOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('scholarship_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status', 30)->default('unpaid'); // unpaid, partial, paid, overdue, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'academic_year_id']);
            $table->index(['school_id', 'status']);
            $table->index('invoice_number');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->noActionOnDelete();
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->noActionOnDelete();
            $table->string('description', 500);
            $table->decimal('unit_price', 12, 2);
            $table->smallInteger('quantity')->default(1);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->index('invoice_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->noActionOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->string('payment_number', 50)->unique();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50); // cash, bank_transfer, cheque, online, pos
            $table->string('reference_number', 100)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('status', 30)->default('confirmed'); // pending, confirmed, failed, reversed
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();

            $table->index(['school_id', 'payment_date']);
            $table->index(['invoice_id']);
            $table->index('payment_number');
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->noActionOnDelete();
            $table->string('receipt_number', 50)->unique();
            $table->timestamp('issued_at')->useCurrent();
            $table->foreignId('issued_by')->constrained('users');
            $table->string('file_path', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('fee_structure_items');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_categories');
    }
};

