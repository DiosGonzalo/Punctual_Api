<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Modalidad;
use App\Models\Role;
use App\Models\Workplace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_presente' => 'boolean',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

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

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }
}
