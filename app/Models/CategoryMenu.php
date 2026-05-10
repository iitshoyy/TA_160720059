<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CategoryMenu extends Model {
    protected $table    = 'category_menus';
    protected $fillable = ['name'];
    public function menus() { return $this->hasMany(Menu::class,'categoryMenus_id'); }
}
