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
}
