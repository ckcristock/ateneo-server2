<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;
    protected $table = 'Auditoria';
    protected $primaryKey = 'Id_Auditoria';
    public $timestamps = false;

    protected $fillable = [
        'Estado',
        'Funcionario_Preauditoria',
        'Funcionario_Auditoria',
        'Fecha_Preauditoria',
        'Fecha_Auditoria',
        'Id_Turnero',
        'Id_Dispensacion',
        'Id_Paciente',
        'Id_Tipo_Servicio',
        'Nombre_Tipo_Servicio',
        'Punto_Pre_Auditoria',
        'Estado_Turno',
        'Comentario_Auditor',
        'Archivo',
        'Origen',
        'Dispensador_Preauditoria',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Id_Servicio',
        'Id_Dispensacion_Mipres',
        'Estado_Archivo',
    ];
}
