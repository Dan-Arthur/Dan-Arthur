<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->noActionOnDelete();
            $table->string('relationship', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_emergency')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->boolean('receives_reports')->default(true);
            $table->boolean('receives_invoices')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'guardian_id']);
            $table->index('student_id');
            $table->index('guardian_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};

