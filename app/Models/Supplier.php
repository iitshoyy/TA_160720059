<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    protected $fillable = ['name','phone','email','address'];
    public function ingridients()    { return $this->hasMany(Ingridient::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}
