<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'Alerta';
    protected $primaryKey = 'Id_Alerta';
    public $timestamps = false;

    protected $fillable = [
        'Identificacion_Funcionario',
        'Tipo',
        'Fecha',
        'Detalles',
        'Respuesta',
        'Id',
        'Modulo',
    ];
}
