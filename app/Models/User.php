<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'roles_id'];
    protected $hidden   = ['password', 'remember_token'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }

    public function getRoleAttribute(): string
    {
        // Use getRelationValue to bypass this mutator and load the BelongsTo,
        // otherwise $this->role would resolve back to this accessor.
        return $this->getRelationValue('role')?->name ?? 'Staff';
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'users_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'users_id');
    }
}
