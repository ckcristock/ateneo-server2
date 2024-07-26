<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioFisicoPuntoNuevo extends Model
{
    use HasFactory;

    protected $table = 'Inventario_Fisico_Punto_Nuevo';
    protected $primaryKey = 'Id_Inventario_Fisico_Punto_Nuevo';
    protected $fillable = [
        'Funcionario_Autoriza',
        'Id_Punto_Dispensacion',
        'Id_Grupo_Estiba',
        'Fecha',
    ];
}
