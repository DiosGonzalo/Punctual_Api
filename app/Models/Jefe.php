<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jefe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'telefono',
        'id_horario',
        'id_workplace',
        'id_modalidad',
        'is_presente',
    ];

    protected $casts = [
        'is_presente' => 'boolean',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario');
    }

    public function workplace()
    {
        return $this->belongsTo(Workplace::class, 'id_workplace');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'id_modalidad');
    }
}
