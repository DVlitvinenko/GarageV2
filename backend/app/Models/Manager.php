<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;
    protected $fillable = ['park_id', 'user_id'];

    public function park()
    {
        return $this->belongsTo(Park::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
