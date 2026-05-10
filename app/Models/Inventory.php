<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model {
    protected $fillable = ['ingridient_id','quantity_on_hand','last_updated'];
    public function ingridient() { return $this->belongsTo(Ingridient::class,'ingridient_id'); }
}
