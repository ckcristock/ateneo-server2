<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroCosto extends Model
{
    protected $table = 'Centro_Costo';
    protected $primaryKey = 'Id_Centro_Costo';

    protected $fillable = [
        'Nombre',
        'Codigo',
        'Id_Centro_Padre',
        'Id_Tipo_Centro',
        'Valor_Tipo_Centro',
        'Estado',
        'Movimiento',
        'Estado',
        'company_id',
    ];

}
