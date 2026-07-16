<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Table extends Model {
    protected $fillable = ['name','capacity','status','floor_id','pos_x','pos_y'];
    public function floor()        { return $this->belongsTo(Floor::class); }
    public function orders()       { return $this->hasMany(Order::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
}
