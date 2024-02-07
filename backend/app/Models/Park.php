<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Park extends Model
{
    use HasFactory;
    protected $fillable = ['API_key', 'url', 'comission', 'park_name', 'about', 'working_hours'];

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }
}
