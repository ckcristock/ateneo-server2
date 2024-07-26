<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'Contrato';

    protected $primaryKey = 'Id_Contrato';

    protected $fillable = [
        'Tipo_Contrato',
        'Nombre_Contrato',
        'Id_Cliente',
        'Fecha_Inicio',
        'Fecha_Fin',
        'Presupuesto',
    ];

}

