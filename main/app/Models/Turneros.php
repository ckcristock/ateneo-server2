<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turneros extends Model
{
    use HasFactory;

    protected $table = 'Turneros';

    protected $primaryKey = 'Id_Turneros';

    protected $fillable = [
        'Nombre',
        'Direccion',
        'Capita',
        'No_Pos',
        'Autorizacion_Servicios',
        'Maximo_Turnos',
        'Identificacion_Persona',
        'Persona',
        'Fecha',
        'Hora_Turno',
        'Hora_Inicio_Atencion',
        'Hora_Fin_Atencion',
        'Estado',
        'Orden',
        'Caja',
        'Tipo',
        'Id_Auditoria',
        'Tag',
        'Prioridad',
        'Id_Prioridad_Turnero',
        'Id_Dispensacion_Mipres',
        'Tipo_Turno'
    ];

    protected $dates = [
        'Fecha',
        'Hora_Turno',
        'Hora_Inicio_Atencion',
        'Hora_Fin_Atencion'
    ];

    public $timestamps = true;
}
