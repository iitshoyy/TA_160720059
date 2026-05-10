<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ingridient extends Model {
    protected $table    = 'ingridients';
    protected $fillable = ['name','unit','cost_per_unit','min_stock','ingridient_types_id','supplier_id'];
    public function type()        { return $this->belongsTo(IngridientType::class,'ingridient_types_id'); }
    public function inventories() { return $this->hasMany(Inventory::class,'ingridient_id'); }
    public function supplier()    { return $this->belongsTo(Supplier::class); }
    public function components()  { return $this->hasMany(Component::class,'ingridients_id'); }
}
