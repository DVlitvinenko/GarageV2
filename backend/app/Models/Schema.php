<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schema extends Model
{
    use HasFactory;
    protected $fillable = ['rent_term_id', 'daily_amount', 'non_working_days', 'working_days'];

    public function rentTerm()
    {
        return $this->belongsTo(RentTerm::class);
    }
}
