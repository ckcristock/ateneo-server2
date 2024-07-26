<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    protected $table = 'Resolucion';
    protected $primaryKey = 'Id_Resolucion';

    protected $fillable = [
        'Codigo',
        'Nombre',
        'Resolucion',
        'Id_Departamento',
        'Fecha_Inicio',
        'Fecha_Fin',
        'Numero_Inicial',
        'Numero_Final',
        'Consecutivo',
        'Descripcion',
        'Modulo',
        'Estado',
        'Tipo_Resolucion',
        'Usuario',
        'Pin',
        'Clave_Tecnica',
        'Contrasena',
        'Id_Software',
        'resolution_id'
    ];
}

