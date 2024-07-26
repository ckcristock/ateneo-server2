<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'Servicio';

    protected $fillable = [
        "Nombre",
        "Estado",
        "Identificacion_Funcionario",
        "Fecha",
        "Funcionario_Inactiva",
        "Fecha_Inactivacion",
        "Cantidad_Formulada",
        "Autorizacion",
    ];
}
