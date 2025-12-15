<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellidos',
        'id_rol',
        'email',
        'telefono',
        'id_horario',
        'id_workplace',
        'id_modalidad',
        'id_departamento',
        'is_presente',
    ];

    protected $casts = [
        'is_presente' => 'boolean',
    ];

    public function rol()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }
}
