<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model {
    protected $fillable = [
        'customer_name','phone','email','reservation_date','reservation_time',
        'guests','table_id','status','source','notes',
    ];
    public function table() { return $this->belongsTo(Table::class); }
}
