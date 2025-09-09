<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $guarded = [ 'id' , 'created_at' , 'updated_at' ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
