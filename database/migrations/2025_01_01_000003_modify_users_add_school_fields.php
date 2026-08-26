<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->foreignId('school_id')->nullable()->after('uuid')
                ->constrained('schools')->noActionOnDelete();
            $table->foreignId('campus_id')->nullable()->after('school_id')
                ->constrained('campuses')->noActionOnDelete();
            $table->string('first_name', 100)->after('name')->nullable();
            $table->string('last_name', 100)->after('first_name')->nullable();
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('status', 20)->default('active')->after('avatar'); // active, inactive, suspended
            $table->boolean('is_super_admin')->default(false)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('is_super_admin');
            $table->string('last_login_ip', 50)->nullable()->after('last_login_at');
            $table->string('timezone', 50)->default('Africa/Lagos')->after('last_login_ip');
            $table->softDeletes();

            $table->index('school_id');
            $table->index('campus_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['campus_id']);
            $table->dropColumn([
                'uuid', 'school_id', 'campus_id', 'first_name', 'last_name',
                'phone', 'avatar', 'status', 'is_super_admin',
                'last_login_at', 'last_login_ip', 'timezone', 'deleted_at',
            ]);
        });
    }
};

