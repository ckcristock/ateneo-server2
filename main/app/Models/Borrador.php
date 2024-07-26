<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrador extends Model
{
    protected $table = 'Borrador';

    protected $primaryKey = 'Id_Borrador';

    protected $fillable = [
        'Codigo',
        'Tipo',
        'Texto',
        'Fecha',
        'Id_Funcionario',
        'Nombre_Destino',
        'Estado',
    ];

    protected $casts = [
        'Fecha' => 'datetime',
    ];
}
