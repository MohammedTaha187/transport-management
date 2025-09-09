<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $guarded = [ 'id' , 'created_at' , 'updated_at' ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
