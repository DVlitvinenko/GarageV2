<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'city_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function driverDocs()
    {
        return $this->hasOne(DriverDoc::class);
    }

    public function driverSpecification()
    {
        return $this->hasOne(DriverSpecification::class);
    }
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }
}
