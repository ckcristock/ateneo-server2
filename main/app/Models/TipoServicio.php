<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    use HasFactory;

    protected $table = 'Tipo_Servicio';
    protected $primaryKey = 'Id_Tipo_Servicio';

    protected $fillable = [
        'Codigo',
        'Nombre',
        'Nota',
        'Id_Servicio',
        'Codigo_CIE',
        'Tipo_Lista',
        'Usuario_Modificacion',
    ];
}
