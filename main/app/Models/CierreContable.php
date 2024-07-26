<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreContable extends Model
{
    use HasFactory;
    protected $table = 'Cierre_Contable';
    protected $primaryKey = 'Id_Cierre_Contable';
    protected $fillable = [
        'Codigo',
        'Identificacion_Funcionario',
        'Mes',
        'Anio',
        'Observaciones',
        'Tipo_Cierre',
        'Estado',
        'Id_Empresa',
        'Created_At',
        'Updated_At',
    ];
}
