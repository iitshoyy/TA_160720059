<?php
// =========================
// app/Models/User.php
// =========================
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;
    protected $fillable = ['name', 'username', 'email', 'password', 'roles_id'];
    protected $hidden = ['password', 'remember_token'];
    public function role() { return $this->belongsTo(Role::class, 'roles_id'); }
    public function getRoleAttribute() { return $this->role?->name ?? 'Staff'; }
}

// =========================
// app/Models/Role.php
// =========================
class Role extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['name'];
    public function users() { return $this->hasMany(User::class, 'roles_id'); }
}

// =========================
// app/Models/Menu.php
// =========================
class Menu extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['name', 'description', 'availability', 'price', 'categoryMenus_id', 'image'];
    public function category() { return $this->belongsTo(CategoryMenu::class, 'categoryMenus_id'); }
    public function orderDetails() { return $this->hasMany(OrderDetail::class, 'menus_id'); }
    public function components() { return $this->hasMany(Component::class, 'menus_id'); }
}

// =========================
// app/Models/CategoryMenu.php
// =========================
class CategoryMenu extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'category_menus';
    protected $fillable = ['name'];
    public function menus() { return $this->hasMany(Menu::class, 'categoryMenus_id'); }
}

// =========================
// app/Models/Order.php
// =========================
class Order extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'order_date', 'total_amount', 'users_id', 'users_roles_id', 'customers_id',
        'payment_types_id', 'payment_date', 'amount_paid', 'table_id',
        'order_type', 'status', 'customer_name', 'notes'
    ];
    public function orderDetails() { return $this->hasMany(OrderDetail::class, 'orders_id'); }
    public function table() { return $this->belongsTo(Table::class); }
    public function customer() { return $this->belongsTo(Customer::class, 'customers_id'); }
    public function paymentType() { return $this->belongsTo(PaymentType::class, 'payment_types_id'); }
    public function user() { return $this->belongsTo(User::class, 'users_id'); }
}

// =========================
// app/Models/OrderDetail.php
// =========================
class OrderDetail extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'order_details';
    protected $fillable = ['orders_id', 'menus_id', 'quantity', 'subtotal', 'price'];
    public function menu() { return $this->belongsTo(Menu::class, 'menus_id'); }
    public function order() { return $this->belongsTo(Order::class, 'orders_id'); }
}

// =========================
// app/Models/Table.php
// =========================
class Table extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['name', 'capacity', 'status'];
    public function orders() { return $this->hasMany(Order::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
}

// =========================
// app/Models/Ingridient.php
// =========================
class Ingridient extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'ingridients';
    protected $fillable = ['name', 'unit', 'cost_per_unit', 'min_stock', 'ingridient_types_id', 'supplier_id'];
    public function type() { return $this->belongsTo(IngridientType::class, 'ingridient_types_id'); }
    public function inventories() { return $this->hasMany(Inventory::class, 'ingridient_id'); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function components() { return $this->hasMany(Component::class, 'ingridients_id'); }
}

// =========================
// app/Models/IngridientType.php
// =========================
class IngridientType extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'ingridient_types';
    protected $fillable = ['name'];
    public function ingridients() { return $this->hasMany(Ingridient::class, 'ingridient_types_id'); }
}

// =========================
// app/Models/Inventory.php
// =========================
class Inventory extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['ingridient_id', 'quantity_on_hand', 'last_updated'];
    public function ingridient() { return $this->belongsTo(Ingridient::class, 'ingridient_id'); }
}

// =========================
// app/Models/Supplier.php
// =========================
class Supplier extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['name', 'phone', 'email', 'address'];
    public function ingridients() { return $this->hasMany(Ingridient::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}

// =========================
// app/Models/Reservation.php
// =========================
class Reservation extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'customer_name', 'phone', 'email', 'reservation_date', 'reservation_time',
        'guests', 'table_id', 'status', 'source', 'notes'
    ];
    public function table() { return $this->belongsTo(Table::class); }
}

// =========================
// app/Models/Employee.php
// =========================
class Employee extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['users_id', 'position', 'phone', 'hire_date', 'status'];
    public function user() { return $this->belongsTo(User::class, 'users_id'); }
}

// =========================
// app/Models/PurchaseOrder.php
// =========================
class PurchaseOrder extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['supplier_id', 'order_date', 'expected_date', 'status', 'total_amount', 'notes'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id'); }
}

// =========================
// app/Models/PurchaseOrderItem.php
// =========================
class PurchaseOrderItem extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['purchase_order_id', 'ingridient_id', 'quantity', 'unit_price', 'subtotal'];
    public function ingridient() { return $this->belongsTo(Ingridient::class, 'ingridient_id'); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id'); }
}

// =========================
// app/Models/Component.php
// =========================
class Component extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['menus_id', 'ingridients_id', 'quantity'];
    public function menu() { return $this->belongsTo(Menu::class, 'menus_id'); }
    public function ingridient() { return $this->belongsTo(Ingridient::class, 'ingridients_id'); }
}

// =========================
// app/Models/Customer.php
// =========================
class Customer extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['name', 'phone', 'email'];
    public function orders() { return $this->hasMany(Order::class, 'customers_id'); }
}

// =========================
// app/Models/PaymentType.php
// =========================
class PaymentType extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'payment_types';
    protected $fillable = ['name'];
}
