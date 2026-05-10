<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $fillable = [
        'order_date','total_amount','users_id','users_roles_id','customers_id',
        'payment_types_id','payment_date','amount_paid','table_id',
        'order_type','status','customer_name','notes','payment_type',
    ];
    public function orderDetails() { return $this->hasMany(OrderDetail::class,'orders_id'); }
    public function table()        { return $this->belongsTo(Table::class); }
    public function customer()     { return $this->belongsTo(Customer::class,'customers_id'); }
    public function user()         { return $this->belongsTo(User::class,'users_id'); }
}
