<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('sender_id')->constrained('users')->noActionOnDelete();
            $table->text('body');
            $table->string('recipient_group', 50); // all_parents, all_staff, all_students, class_parents, class_students
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->noActionOnDelete();
            $table->integer('recipients_count')->default(0);
            $table->text('phone_numbers')->nullable(); // JSON array
            $table->string('status', 20)->default('sent'); // sent, failed, pending
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_alerts');
    }
};
