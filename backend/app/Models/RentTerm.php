<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentTerm extends Model
{
    use HasFactory;
    protected $fillable = ['park_id', 'deposit_amount_daily', 'deposit_amount_total', 'minimum_period_days', 'name', 'is_buyout_possible'];

    public function park()
    {
        return $this->belongsTo(Park::class);
    }
    public function cars()
    {
        return $this->hasMany(Car::class, 'rent_term_id ');
    }
    public function schema()
    {
        return $this->hasOne(Schema::class);
    }
}
