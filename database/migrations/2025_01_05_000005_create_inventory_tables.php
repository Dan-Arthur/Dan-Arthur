<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 200);
            $table->string('contact_person', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('asset_categories')->noActionOnDelete();
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->noActionOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->noActionOnDelete();
            $table->string('name', 200);
            $table->string('asset_tag', 50)->nullable()->unique();
            $table->string('serial_number', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('location', 200)->nullable();
            $table->string('assigned_to_type', 30)->nullable(); // user, class, department
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->string('condition', 30)->default('good'); // good, fair, poor, damaged, disposed
            $table->string('status', 30)->default('active'); // active, disposed, lost, maintenance
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->string('name', 200);
            $table->string('sku', 50)->nullable();
            $table->string('unit', 30)->default('piece'); // piece, box, ream, litre, kg
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('location', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->noActionOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->noActionOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->noActionOnDelete();
            $table->string('movement_type', 30); // purchase, issue, return, adjustment, damage
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('issued_to_type', 30)->nullable(); // user, department, class
            $table->unsignedBigInteger('issued_to_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();

            $table->index(['inventory_item_id', 'movement_date']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('suppliers');
    }
};

