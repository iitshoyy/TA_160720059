<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model {
    protected $fillable = ['purchase_order_id','ingridient_id','quantity','unit_price','subtotal'];
    public function ingridient()    { return $this->belongsTo(Ingridient::class, 'ingridient_id'); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id'); }
}
