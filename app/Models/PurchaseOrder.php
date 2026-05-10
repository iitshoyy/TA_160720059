<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model {
    protected $fillable = ['supplier_id','order_date','expected_date','status','total_amount','notes'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items()    { return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id'); }
}
