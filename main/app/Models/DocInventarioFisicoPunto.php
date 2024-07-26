<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocInventarioFisicoPunto extends Model
{
    use HasFactory;

    protected $table = 'Doc_Inventario_Fisico_Punto';

    protected $fillable = [
        'Id_Estiba',
        'Fecha_Inicio',
        'Fecha_Fin',
        'Funcionario_Digita',
        'Funcionario_Cuenta',
        'Funcionario_Autorizo',
        'Productos_Correctos',
        'Productos_Diferencia',
        'Observaciones',
        'Estado',
        'Id_Inventario_Fisico_Punto_Nuevo',
        'Lista_Productos',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Observaciones_Anulacion',
    ];
}
