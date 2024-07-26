<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedioMagnetico extends Model
{
    use HasFactory;
    protected $table = 'Medio_Magnetico';
    protected $primaryKey = 'Id_Medio_Magnetico';
    protected $fillable = [
        'Periodo',
        'Codigo_Formato',
        'Nombre_Formato',
        'Tipo_Exportacion',
        'Detalles',
        'Tipos',
        'Tipo_Medio_Magnetico',
        'Tipo_Columna',
        'Columna_Principal',
        'Estado',
        'Id_Empresa',
        'Created_At',
    ];
}
