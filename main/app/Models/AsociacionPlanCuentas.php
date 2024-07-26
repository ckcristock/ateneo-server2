<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsociacionPlanCuentas extends Model
{
    protected $table = 'Asociacion_Plan_Cuentas';
    protected $primaryKey = 'Id_Asociacion_Plan_Cuentas';
    protected $fillable = [
        'Id_Plan_Cuenta',
        'Id_Modulo',
        'Busqueda_Interna',
    ];
}
