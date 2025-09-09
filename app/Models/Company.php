<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [ 'id' , 'created_at' , 'updated_at' ];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
