<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('reported_by')->constrained('users')->noActionOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->noActionOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->noActionOnDelete();
            $table->string('category', 100)->nullable(); // bullying, theft, misconduct, etc.
            $table->string('severity', 20)->default('minor'); // minor, moderate, major
            $table->date('incident_date');
            $table->string('location', 200)->nullable();
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->string('status', 30)->default('open'); // open, resolved, pending_review
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'incident_date']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};

