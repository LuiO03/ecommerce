<?php

namespace App\Models;

class Client extends User
{
    protected $table = 'users';

    /**
     * Spatie Permission almacena roles por morph type.
     * Como Client hereda de User, si no forzamos el morph class,
     * las consultas por rol buscarán model_type=App\\Models\\Client
     * aunque las asignaciones estén en App\\Models\\User.
     */
    public function getMorphClass()
    {
        return User::class;
    }

    protected static function booted()
    {
        static::addGlobalScope('client_role', function ($query) {
            $query->role('Cliente', 'web');
        });
    }

    public function addresses()
    {
        return $this->hasMany(Addresses::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->sum('total');
    }

    public function getLastOrderAttribute()
    {
        return $this->orders()->latest()->first();
    }
}
