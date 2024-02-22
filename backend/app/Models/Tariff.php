<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;
    protected $fillable = ['class', 'park_id', 'city_id', 'criminal_ids', 'has_caused_accident', 'experience', 'max_fine_count', 'abandoned_car', 'min_scoring', 'is_north_caucasus', 'alcohol'];

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
