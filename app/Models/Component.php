<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Component extends Model {
    protected $fillable = ['menus_id','ingridients_id','quantity'];
    public function menu()       { return $this->belongsTo(Menu::class, 'menus_id'); }
    public function ingridient() { return $this->belongsTo(Ingridient::class, 'ingridients_id'); }
}
