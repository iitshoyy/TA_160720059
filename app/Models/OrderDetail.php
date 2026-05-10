<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {
    protected $table    = 'order_details';
    protected $fillable = ['orders_id','menus_id','quantity','subtotal','price'];
    public function menu()  { return $this->belongsTo(Menu::class,'menus_id'); }
    public function order() { return $this->belongsTo(Order::class,'orders_id'); }
}
