<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retencion extends Model
{
    protected $table = 'Retencion';
    protected $primaryKey = 'Id_Retencion';

    protected $fillable = [
        'Nombre',
        'Id_Plan_Cuenta',
        'Porcentaje',
        'Estado',
        'Descripcion',
        'Tipo_Retencion',
        'Modalidad_Retencion',
    ];
}
