<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('title', 20)->nullable(); // Mr, Mrs, Dr, Prof
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('relationship', 50)->nullable(); // Father, Mother, Uncle, Guardian
            $table->string('phone', 30);
            $table->string('alt_phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('occupation', 150)->nullable();
            $table->string('employer', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_primary_contact')->default(true);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('portal_access')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'phone']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};

