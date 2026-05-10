<?php
// =====================================================
// MIGRATION: 2024_01_01_000001_create_roles_table.php
// =====================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('username', 45)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('roles_id')->constrained('roles');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('phone')->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('category_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->text('description')->nullable();
            $table->integer('availability')->default(1);
            $table->string('price', 45);
            $table->foreignId('categoryMenus_id')->constrained('category_menus');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('ingridient_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('ingridients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('unit', 20)->default('pcs');
            $table->string('cost_per_unit', 45)->default(0);
            $table->integer('min_stock')->default(10);
            $table->foreignId('ingridient_types_id')->constrained('ingridient_types');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingridient_id')->constrained('ingridients');
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->string('last_updated', 45);
            $table->timestamps();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->integer('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
        });

        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->dateTime('order_date');
            $table->string('total_amount', 45)->default(0);
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('users_roles_id')->constrained('roles');
            $table->foreignId('customers_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('payment_types_id')->nullable()->constrained('payment_types');
            $table->string('payment_type', 45)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('amount_paid', 45)->nullable();
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->enum('order_type', ['dine-in', 'takeaway', 'pre-order'])->default('dine-in');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->string('customer_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orders_id')->constrained('orders');
            $table->foreignId('menus_id')->constrained('menus');
            $table->string('quantity', 45);
            $table->string('subtotal', 45);
            $table->string('price', 45);
            $table->timestamps();
        });

        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menus_id')->constrained('menus');
            $table->foreignId('ingridients_id')->constrained('ingridients');
            $table->string('quantity', 45);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 255);
            $table->string('phone', 45);
            $table->string('email', 255)->nullable();
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('guests')->default(1);
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'arrived', 'cancelled'])->default('pending');
            $table->enum('source', ['online', 'offline', 'whatsapp', 'phone'])->default('offline');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users');
            $table->string('position', 45);
            $table->string('phone', 45)->nullable();
            $table->date('hire_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->enum('status', ['pending', 'sent', 'received', 'cancelled'])->default('pending');
            $table->string('total_amount', 45)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('ingridient_id')->constrained('ingridients');
            $table->decimal('quantity', 10, 2);
            $table->string('unit_price', 45);
            $table->string('subtotal', 45);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('components');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('ingridients');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('ingridient_types');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('category_menus');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
