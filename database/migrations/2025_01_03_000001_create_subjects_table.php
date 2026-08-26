<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->noActionOnDelete();
            $table->string('name', 150);
            $table->string('code', 30);
            $table->string('type', 30)->default('core'); // core, elective, extra_curricular
            $table->string('category', 50)->nullable(); // science, arts, language, vocational
            $table->smallInteger('credit_hours')->default(1);
            $table->boolean('has_practical')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

