<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 150);
            $table->string('code', 20)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('book_categories')->noActionOnDelete();
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 200);
            $table->string('bio', 500)->nullable();
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 200);
            $table->string('contact', 200)->nullable();
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('book_categories')->noActionOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->noActionOnDelete();
            $table->string('title', 300);
            $table->string('isbn', 30)->nullable();
            $table->string('edition', 50)->nullable();
            $table->year('publish_year')->nullable();
            $table->string('language', 50)->default('English');
            $table->integer('total_copies')->default(0);
            $table->integer('available_copies')->default(0);
            $table->string('location', 100)->nullable(); // shelf reference
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'title']);
            $table->index('isbn');
        });

        Schema::create('book_authors', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained('books')->noActionOnDelete();
            $table->foreignId('author_id')->constrained('authors')->noActionOnDelete();
            $table->primary(['book_id', 'author_id']);
        });

        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->noActionOnDelete();
            $table->string('barcode', 100)->nullable();
            $table->string('accession_number', 50)->nullable();
            $table->string('condition', 30)->default('good'); // good, fair, poor, damaged, lost
            $table->string('status', 30)->default('available'); // available, loaned, reserved, lost, damaged
            $table->timestamps();

            $table->index('book_id');
            $table->index('barcode');
        });

        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('book_copy_id')->constrained('book_copies')->noActionOnDelete();
            $table->string('member_type', 30); // student, employee
            $table->unsignedBigInteger('member_id');
            $table->date('loan_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->string('status', 30)->default('active'); // active, returned, overdue, lost
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->decimal('fine_paid', 8, 2)->default(0);
            $table->foreignId('issued_by')->constrained('users')->noActionOnDelete();
            $table->foreignId('returned_to')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('return_condition', 30)->nullable();
            $table->timestamps();

            $table->index(['member_type', 'member_id']);
            $table->index(['school_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('book_authors');
        Schema::dropIfExists('books');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('book_categories');
    }
};

