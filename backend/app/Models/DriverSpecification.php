<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverSpecification extends Model
{
    use HasFactory;
    protected $fillable = ['driver_id', 'rent_story', 'criminal_ids', 'has_caused_accident', 'experience', 'count_seams', 'abandoned_car', 'republick_id', 'scoring', 'alcohol'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function republick()
    {
        return $this->belongsTo(Republick::class);
    }
}
