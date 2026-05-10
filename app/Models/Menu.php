<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model {
    protected $fillable = ['name','description','availability','price','categoryMenus_id','image'];
    public function category()      { return $this->belongsTo(CategoryMenu::class,'categoryMenus_id'); }
    public function orderDetails()  { return $this->hasMany(OrderDetail::class,'menus_id'); }
    public function components()    { return $this->hasMany(Component::class,'menus_id'); }
}
