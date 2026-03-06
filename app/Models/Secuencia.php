<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secuencia extends Model
{
    use HasFactory;

    protected $table = 'secuencias';
    protected $fillable = ['nombre', 'prefijo', 'valor_actual'];
}
