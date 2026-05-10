<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class IngridientType extends Model {
    protected $table    = 'ingridient_types';
    protected $fillable = ['name'];
    public function ingridients() { return $this->hasMany(Ingridient::class,'ingridient_types_id'); }
}
