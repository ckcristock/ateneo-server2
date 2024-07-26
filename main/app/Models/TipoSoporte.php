<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSoporte extends Model
{
    use HasFactory;

    protected $table = 'Tipo_Soporte';
    protected $primaryKey = 'Id_Tipo_Soporte';

    protected $fillable = [
        'Id_Tipo_Servicio',
        'Tipo_Soporte',
        'Comentario',
        'Pre_Auditoria',
        'Auditoria',
        'Nombre_Rips',
        'Nombre_Radicacion',
    ];
}
