<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('registration_number', 30)->unique();
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->year('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->smallInteger('capacity')->default(0);
            $table->string('type', 50)->default('bus'); // bus, van, car
            $table->string('status', 30)->default('active'); // active, inactive, maintenance
            $table->date('insurance_expiry')->nullable();
            $table->date('road_worthiness_expiry')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->noActionOnDelete();
            $table->string('licence_number', 50)->nullable();
            $table->string('licence_class', 20)->nullable();
            $table->date('licence_expiry')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->noActionOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->noActionOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->string('direction', 20)->default('both'); // pickup, dropoff, both
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('transport_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->noActionOnDelete();
            $table->string('name', 150);
            $table->text('address')->nullable();
            $table->time('pickup_time')->nullable();
            $table->time('dropoff_time')->nullable();
            $table->smallInteger('sequence')->default(1);
            $table->timestamps();

            $table->index('route_id');
        });

        Schema::create('student_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->noActionOnDelete();
            $table->foreignId('route_id')->constrained('transport_routes');
            $table->foreignId('stop_id')->nullable()->constrained('transport_stops')->noActionOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->string('direction', 20)->default('both');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['student_id', 'route_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
    }
};

