<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->noActionOnDelete();
            $table->string('documentable_type', 150)->nullable();
            $table->unsignedBigInteger('documentable_id')->nullable();
            $table->string('title', 255);
            $table->string('category', 50)->nullable(); // admission, id_card, certificate, report, invoice, etc.
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable(); // bytes
            $table->string('status', 20)->default('active'); // active, archived
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['documentable_type', 'documentable_id']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

