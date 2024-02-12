<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;
    protected $fillable = ['class', 'park_id', 'city_id', 'criminal_ids', 'participation_accident', 'experience', 'max_cont_seams', 'abandoned_car', 'min_scoring', 'forbidden_republic_ids', 'alcohol'];

    public function park()
    {
        return $this->belongsTo(Park::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function cars()
    {
        return $this->hasMany(Car::class, 'tariff_id');
    }
}
