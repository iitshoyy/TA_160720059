<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Table extends Model {
    protected $fillable = ['name','capacity','status','pos_x','pos_y'];
    public function orders()       { return $this->hasMany(Order::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
}
