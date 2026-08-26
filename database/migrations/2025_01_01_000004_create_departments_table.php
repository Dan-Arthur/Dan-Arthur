<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->noActionOnDelete();
            $table->string('name', 150);
            $table->string('code', 20);
            $table->string('type', 30)->default('academic'); // academic, administrative
            $table->text('description')->nullable();
            $table->foreignId('head_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'campus_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

