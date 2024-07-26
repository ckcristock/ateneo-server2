<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depreciacion extends Model
{
    protected $table = 'Depreciacion';
    protected $primaryKey = 'Id_Depreciacion';

    protected $fillable = [
        'Codigo',
        'Fecha_Registro',
        'Mes',
        'Anio',
        'Identificacion_Funcionario',
        'Tipo',
        'Estado',
        'Funcionario_Anula',
        'Fecha_Anulacion',
    ];
}
