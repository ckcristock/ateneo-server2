<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadesDispensacion extends Model
{
    use HasFactory;

    protected $table = 'Actividades_Dispensacion';

    protected $primaryKey = 'Id_Actividades_Dispensacion';

    protected $fillable = [
        'Id_Dispensacion',
        'Identificacion_Funcionario',
        'Fecha',
        'Detalle',
        'Estado',
    ];
}
