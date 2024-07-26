<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActivoFijo extends Model
{
    protected $table = 'Tipo_Activo_Fijo';
    protected $primaryKey = 'Id_Tipo_Activo_Fijo';

    protected $fillable = [
        'Nombre_Tipo_Activo',
        'Categoria',
        'Vida_Util',
        'Porcentaje_Depreciacion_Anual',
        'Vida_Util_PCGA',
        'Porcentaje_Depreciacion_Anual_PCGA',
        'Id_Plan_Cuenta_Depreciacion_NIIF',
        'Id_Plan_Cuenta_Depreciacion_PCGA',
        'Id_Plan_Cuenta_NIIF',
        'Id_Plan_Cuenta_PCGA',
        'Estado',
        'Id_Plan_Cuenta_Credito_Depreciacion_PCGA',
        'Id_Plan_Cuenta_Credito_Depreciacion_NIIF',
        'Consecutivo',
        'Mantis',
    ];
}
