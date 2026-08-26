<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('created_by')->constrained('users')->noActionOnDelete();
            $table->string('title', 300);
            $table->text('body');
            $table->string('type', 50)->default('general'); // general, academic, event, emergency, fee
            $table->string('audience', 50)->default('all'); // all, students, parents, staff, teachers, class
            $table->text('audience_filter')->nullable(); // JSON filters (class_ids, etc.)
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, archived
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index('publish_at');
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('created_by')->constrained('users')->noActionOnDelete();
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->string('type', 50)->default('general'); // academic, examination, holiday, sport, meeting, other
            $table->string('audience', 50)->default('all');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location', 300)->nullable();
            $table->string('status', 30)->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->string('color', 20)->default('#3B82F6');
            $table->timestamps();

            $table->index(['school_id', 'start_datetime']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->string('subject', 300)->nullable();
            $table->text('body');
            $table->string('type', 30)->default('internal'); // internal, email, sms
            $table->string('status', 30)->default('sent');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'sender_id']);
        });

        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->noActionOnDelete();
            $table->foreignId('recipient_id')->constrained('users');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['message_id', 'recipient_id']);
            $table->index('recipient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('events');
        Schema::dropIfExists('announcements');
    }
};

