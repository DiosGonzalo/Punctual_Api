<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'id_jefe'];

    public function jefe()
    {
        return $this->belongsTo(Jefe::class, 'id_jefe');
    }
}
