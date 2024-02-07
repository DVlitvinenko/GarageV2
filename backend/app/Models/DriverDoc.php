<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDoc extends Model
{
    use HasFactory;
    protected $fillable = ['driver_id', 'image_licence_front', 'image_licence_back', 'image_pasport_front', 'image_pasport_address', 'image_fase_and_pasport', 'docs_verify'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
