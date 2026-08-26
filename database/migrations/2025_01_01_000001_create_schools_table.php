<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('type', 50)->default('secondary'); // primary, secondary, international, college
            $table->string('motto')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('Nigeria');
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('stamp')->nullable();
            $table->string('signature')->nullable();
            $table->string('academic_structure', 20)->default('term'); // term, semester
            $table->smallInteger('terms_per_year')->default(3);
            $table->string('currency_code', 10)->default('NGN');
            $table->string('currency_symbol', 5)->default('₦');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};

