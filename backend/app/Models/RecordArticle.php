<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordArticle extends Model
{
    use HasFactory;
    protected $fillable = ['article', 'degree_of_severity'];
}
