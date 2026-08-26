<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->noActionOnDelete();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, boolean, integer, json, array
            $table->string('group', 50)->default('general');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'key']);
            $table->index(['school_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

