<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('paid_to')->nullable();
            $table->string('payment_method')->default('cash');
            $table->string('reference_number')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'date']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
