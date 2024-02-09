<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = ['division_id', 'tariff_id', 'rent_term_id', 'fuel_type', 'transmission_type', 'brand', 'model', 'year_produced', 'id_car', 'images', 'booking_time', 'user_booked_id', 'show_status'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function rentTerm()
    {
        return $this->belongsTo(RentTerm::class);
    }

    public function schema()
    {
        return $this->hasOneThrough(Schema::class, RentTerm::class);
    }
}
