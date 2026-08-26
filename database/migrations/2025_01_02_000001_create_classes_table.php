<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->noActionOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->noActionOnDelete();
            $table->string('name', 100); // e.g. "JSS 1", "Form 1", "Year 7"
            $table->string('code', 20);
            $table->smallInteger('level')->default(1); // numeric level for ordering
            $table->string('section', 50)->nullable(); // A, B, C (stream/arm)
            $table->string('programme', 100)->nullable(); // Science, Arts, Commercial
            $table->smallInteger('capacity')->default(40);
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('room', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code', 'section']);
            $table->index(['school_id', 'campus_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};

