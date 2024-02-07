<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;
    protected $fillable = ['park_id', 'city_id', 'coords', 'address'];

    public function park()
    {
        return $this->belongsTo(Park::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
